<?php
require_once __DIR__ . '/config.php';
date_default_timezone_set('UTC');
mb_internal_encoding('UTF-8');
error_reporting(APP_DEBUG ? E_ALL : 0);
ini_set('display_errors', APP_DEBUG ? '1' : '0');

define('ROOT_PATH', dirname(__DIR__));
$docRoot = rtrim(str_replace('\\', '/', (string)($_SERVER['DOCUMENT_ROOT'] ?? '')), '/');
$rootFs  = str_replace('\\', '/', ROOT_PATH);
define('BASE_URL', ($docRoot !== '' && strpos($rootFs, $docRoot) === 0) ? rtrim(substr($rootFs, strlen($docRoot)), '/') : '');

session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax',
    'secure' => !empty($_SERVER['HTTPS'])]);
session_name('SOUNDSESS');
session_start();
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');

function db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;
    try {
        $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4, time_zone = '+00:00'",
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        exit('<h2 style="font-family:sans-serif">Database connection failed.</h2><p style="font-family:sans-serif">Check <code>includes/config.php</code> and make sure you imported <code>database/sound_db.sql</code>.</p>');
    }
    return $pdo;
}
function q(string $sql, array $p = []): PDOStatement { $s = db()->prepare($sql); $s->execute($p); return $s; }
function one(string $sql, array $p = []) { return q($sql, $p)->fetch() ?: null; }
function all(string $sql, array $p = []): array { return q($sql, $p)->fetchAll(); }
function val(string $sql, array $p = []) { $r = q($sql, $p)->fetchColumn(); return $r === false ? null : $r; }

function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function url(string $path = ''): string { return BASE_URL . '/' . ltrim($path, '/'); }
function redirect(string $path): never { header('Location: ' . (preg_match('#^https?://#', $path) ? url('') : url($path))); exit; }

// ---- Settings ----
function setting(string $key, string $default = ''): string {
    static $cache = null;
    if ($cache === null) { $cache = []; foreach (all('SELECT setting_key, setting_value FROM settings') as $r) $cache[$r['setting_key']] = $r['setting_value']; }
    return $cache[$key] ?? $default;
}

// ---- CSRF ----
function csrf_token(): string { if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32)); return $_SESSION['csrf']; }
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">'; }
function csrf_ok(): bool { return isset($_POST['csrf'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], (string)$_POST['csrf']); }
function back_path(string $fallback): string {
    $r = $_SERVER['HTTP_REFERER'] ?? '';
    $host = parse_url($r, PHP_URL_HOST);
    if ($r && $host === ($_SERVER['HTTP_HOST'] ? explode(':', $_SERVER['HTTP_HOST'])[0] : '')) {
        $path = parse_url($r, PHP_URL_PATH) . (($qs = parse_url($r, PHP_URL_QUERY)) ? '?' . $qs : '');
        if (BASE_URL === '' || strpos($path, BASE_URL) === 0) return substr($path, strlen(BASE_URL));
    }
    return $fallback;
}
function require_post_csrf(string $back = 'index.php'): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Method not allowed'); }
    if (!csrf_ok()) { flash('danger', 'Security token invalid or expired (a very large upload can also cause this). Please try again.'); redirect(back_path($back)); }
}

// ---- Flash messages ----
function flash(string $type, string $msg): void { $_SESSION['flash'][] = [$type, $msg]; }
function flash_render(): string {
    $out = '';
    foreach ($_SESSION['flash'] ?? [] as [$t, $m]) $out .= '<div class="alert alert-' . e($t) . ' alert-dismissible fade show" role="alert">' . e($m) . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    unset($_SESSION['flash']);
    return $out;
}

// ---- Auth ----
function current_user(): ?array {
    static $u = false;
    if ($u !== false) return $u;
    $u = null;
    if (!empty($_SESSION['uid'])) {
        $u = one('SELECT * FROM users WHERE id = ?', [$_SESSION['uid']]);
        if (!$u || $u['status'] !== 'active') { $u = null; unset($_SESSION['uid']); }
    }
    return $u;
}
function is_admin(): bool { $u = current_user(); return $u && $u['role'] === 'admin'; }
function require_login(): array {
    $u = current_user();
    if (!$u) { $_SESSION['after_login'] = $_SERVER['REQUEST_URI'] ?? ''; flash('warning', 'Please log in to continue.'); redirect('login.php'); }
    return $u;
}
function require_admin(): array {
    $u = require_login();
    if ($u['role'] !== 'admin') { http_response_code(403); exit('<h2 style="font-family:sans-serif">403 – Administrators only.</h2>'); }
    return $u;
}
function login_user(array $u): void { session_regenerate_id(true); $_SESSION['uid'] = (int)$u['id']; unset($_SESSION['csrf']); }

// ---- Content helpers ----
function tbl(string $type): string { return $type === 'video' ? 'videos' : 'music'; }
function fk(string $type): string { return $type === 'video' ? 'video_id' : 'music_id'; }
function is_new(string $createdAt): bool { return strtotime($createdAt . ' UTC') >= time() - (int)setting('new_badge_days', '14') * 86400; }
function hue(int $id): int { return ($id * 47) % 360; }

function cover_html(array $r, string $type, string $class = ''): string {
    $file = $r['cover'] ?? $r['thumbnail'] ?? $r['image'] ?? null;
    if ($file && is_file(ROOT_PATH . '/uploads/' . $file)) return '<img class="cover ' . e($class) . '" src="' . e(url('uploads/' . $file)) . '" alt="' . e($r['title'] ?? $r['name'] ?? '') . '" loading="lazy">';
    $h = hue((int)$r['id'] + ($type === 'video' ? 5 : ($type === 'artist' ? 11 : 0)));
    $icon = $type === 'video' ? 'bi-play-circle-fill' : ($type === 'artist' ? 'bi-person-fill' : 'bi-music-note-beamed');
    return '<div class="cover cover-ph ' . e($class) . '" style="--h:' . $h . '"><i class="bi ' . $icon . '"></i></div>';
}
function stars(?float $avg, ?int $count = null): string {
    if (!$avg) return '<span class="stars text-muted small">No ratings yet</span>';
    $o = '<span class="stars" title="' . e($avg) . ' / 5">';
    for ($i = 1; $i <= 5; $i++) $o .= '<i class="bi ' . ($avg >= $i - 0.25 ? 'bi-star-fill' : ($avg >= $i - 0.75 ? 'bi-star-half' : 'bi-star')) . '"></i>';
    return $o . ' <small class="text-muted">' . e(number_format($avg, 1)) . ($count !== null ? " ($count)" : '') . '</small></span>';
}
function item_url(string $type, int $id): string { return url('item.php?type=' . $type . '&id=' . $id); }

function card(array $r, string $type): string {
    $new = is_new($r['created_at']) ? '<span class="badge-new">NEW</span>' : '';
    $sub = $type === 'music'
        ? e($r['artist_name'] ?? 'Unknown') . (!empty($r['album_title']) ? ' · ' . e($r['album_title']) : '')
        : e($r['genre_name'] ?? 'Video') . ($r['year'] ? ' · ' . e($r['year']) : '');
    return '<div class="col-6 col-md-4 col-lg-3 col-xl-2-4 reveal"><a class="item-card" href="' . e(item_url($type, (int)$r['id'])) . '">'
        . '<div class="thumb ' . ($type === 'video' ? 'thumb-wide' : '') . '">' . cover_html($r, $type) . $new
        . '<span class="play-ov"><i class="bi bi-play-fill"></i></span></div>'
        . '<div class="item-body"><div class="item-title text-truncate">' . e($r['title']) . '</div><div class="item-sub text-truncate">' . $sub . '</div>'
        . '<div class="mt-1">' . stars($r['avg_rating'] !== null ? (float)$r['avg_rating'] : null, (int)$r['rating_count']) . '</div></div></a></div>';
}

/** Shared list/search query for music + videos. $f keys: q, artist, album, genre, language, year, sort */
function items_query(string $type, array $f, int $page, int $per): array {
    $t = tbl($type); $k = fk($type); $music = $type === 'music';
    $join = 'LEFT JOIN artists a ON a.id=t.artist_id LEFT JOIN genres g ON g.id=t.genre_id LEFT JOIN languages l ON l.id=t.language_id' . ($music ? ' LEFT JOIN albums al ON al.id=t.album_id' : '');
    $w = []; $p = [];
    if (($f['q'] ?? '') !== '') {
        $like = '%' . addcslashes($f['q'], '%_\\') . '%';
        $cols = ['t.title', 'a.name', 'g.name', 'l.name', 'CAST(t.year AS CHAR)'];
        if ($music) $cols[] = 'al.title';
        $w[] = '(' . implode(' OR ', array_map(fn($c) => "$c LIKE ?", $cols)) . ')';
        foreach ($cols as $_) $p[] = $like;
    }
    foreach (['artist' => 't.artist_id', 'genre' => 't.genre_id', 'language' => 't.language_id', 'year' => 't.year'] as $key => $col)
        if (!empty($f[$key])) { $w[] = "$col = ?"; $p[] = (int)$f[$key]; }
    if (!empty($f['id'])) { $w[] = 't.id = ?'; $p[] = (int)$f['id']; }
    if ($music && !empty($f['album'])) { $w[] = 't.album_id = ?'; $p[] = (int)$f['album']; }
    $where = $w ? 'WHERE ' . implode(' AND ', $w) : '';
    $order = match ($f['sort'] ?? 'new') {
        'old' => 't.created_at ASC', 'title' => 't.title ASC', 'rating' => 'avg_rating IS NULL, avg_rating DESC, rating_count DESC',
        'year' => 't.year DESC, t.title ASC', default => 't.created_at DESC, t.id DESC' };
    $total = (int)val("SELECT COUNT(*) FROM $t t $join $where", $p);
    $pages = max(1, (int)ceil($total / $per)); $page = min(max(1, $page), $pages);
    $off = ($page - 1) * $per;
    $sel = "t.*, a.name artist_name, g.name genre_name, l.name language_name" . ($music ? ', al.title album_title' : '')
        . ", (SELECT ROUND(AVG(r.rating),1) FROM ratings r WHERE r.$k=t.id) avg_rating, (SELECT COUNT(*) FROM ratings r WHERE r.$k=t.id) rating_count";
    $rows = all("SELECT $sel FROM $t t $join $where ORDER BY $order LIMIT $per OFFSET $off", $p);
    return [$rows, $total, $page, $pages];
}

function pagination(int $page, int $pages, array $params = []): string {
    if ($pages <= 1) return '';
    $params = array_filter($params, fn($v) => $v !== '' && $v !== null);
    $link = fn($n, $label, $active = false, $dis = false) => '<li class="page-item' . ($active ? ' active' : '') . ($dis ? ' disabled' : '') . '"><a class="page-link" href="?' . e(http_build_query($params + ['page' => $n])) . '">' . $label . '</a></li>';
    $o = '<nav aria-label="Pagination"><ul class="pagination justify-content-center flex-wrap">' . $link($page - 1, '&laquo;', false, $page <= 1);
    for ($i = 1; $i <= $pages; $i++) { if ($i == 1 || $i == $pages || abs($i - $page) <= 2) $o .= $link($i, $i, $i == $page); elseif (abs($i - $page) == 3) $o .= '<li class="page-item disabled"><span class="page-link">…</span></li>'; }
    return $o . $link($page + 1, '&raquo;', false, $page >= $pages) . '</ul></nav>';
}

function filter_form(string $action, array $f, string $type = 'all'): string {
    $opt = function (string $name, array $rows, $key, $label, $sel) {
        $o = '<select name="' . $name . '" class="form-select form-select-sm"><option value="">' . ucfirst($name) . ': any</option>';
        foreach ($rows as $r) $o .= '<option value="' . e($r[$key]) . '"' . ((string)$sel === (string)$r[$key] ? ' selected' : '') . '>' . e($r[$label]) . '</option>';
        return $o . '</select>';
    };
    $o = '<form method="get" action="' . e($action) . '" class="filter-bar row g-2 align-items-center mb-4"><div class="col-12 col-lg-3"><input name="q" value="' . e($f['q'] ?? '') . '" class="form-control form-control-sm" placeholder="Search title, artist, album, year…"></div>';
    if ($type === 'all') $o .= '<div class="col-6 col-lg-1"><select name="type" class="form-select form-select-sm">' . implode('', array_map(fn($k, $v) => '<option value="' . $k . '"' . (($f['type'] ?? 'all') === $k ? ' selected' : '') . '>' . $v . '</option>', ['all', 'music', 'video'], ['All', 'Music', 'Videos'])) . '</select></div>';
    $o .= '<div class="col-6 col-lg-2">' . $opt('genre', all('SELECT id,name FROM genres ORDER BY name'), 'id', 'name', $f['genre'] ?? '') . '</div>'
        . '<div class="col-6 col-lg-2">' . $opt('language', all('SELECT id,name FROM languages ORDER BY name'), 'id', 'name', $f['language'] ?? '') . '</div>'
        . '<div class="col-6 col-lg-1">' . $opt('year', all('SELECT year FROM years ORDER BY year DESC'), 'year', 'year', $f['year'] ?? '') . '</div>'
        . '<div class="col-6 col-lg-2"><select name="sort" class="form-select form-select-sm">' . implode('', array_map(fn($k, $v) => '<option value="' . $k . '"' . (($f['sort'] ?? 'new') === $k ? ' selected' : '') . '>Sort: ' . $v . '</option>', ['new', 'old', 'title', 'rating', 'year'], ['Newest', 'Oldest', 'Title A–Z', 'Top rated', 'Year'])) . '</select></div>'
        . '<div class="col-6 col-lg-1 d-flex gap-1"><button class="btn btn-sm btn-sound w-100">Go</button></div>';
    foreach (['artist', 'album'] as $h) if (!empty($f[$h])) $o .= '<input type="hidden" name="' . $h . '" value="' . e($f[$h]) . '">';
    return $o . '</form>';
}
function read_filters(): array {
    $f = [];
    foreach (['q', 'type', 'sort'] as $k) $f[$k] = trim((string)($_GET[$k] ?? ''));
    foreach (['artist', 'album', 'genre', 'language', 'year'] as $k) $f[$k] = isset($_GET[$k]) && ctype_digit((string)$_GET[$k]) ? (int)$_GET[$k] : '';
    $f['q'] = mb_substr($f['q'], 0, 100);
    return $f;
}

// ---- Uploads ----
function upload_file(string $field, string $dir, string $kind, ?string &$error): ?string {
    $error = null;
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) return null;
    $fl = $_FILES[$field];
    if ($fl['error'] !== UPLOAD_ERR_OK) { $error = ucfirst($field) . ': upload failed' . ($fl['error'] === UPLOAD_ERR_INI_SIZE || $fl['error'] === UPLOAD_ERR_FORM_SIZE ? ' (file exceeds the PHP server limit — raise upload_max_filesize / post_max_size in php.ini).' : '.'); return null; }
    $map = [
        'image' => ['max' => MAX_IMAGE_SIZE, 'types' => ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp']],
        'audio' => ['max' => MAX_AUDIO_SIZE, 'types' => ['audio/mpeg' => 'mp3', 'audio/wav' => 'wav', 'audio/x-wav' => 'wav', 'audio/wave' => 'wav', 'audio/ogg' => 'ogg', 'application/ogg' => 'ogg']],
        'video' => ['max' => MAX_VIDEO_SIZE, 'types' => ['video/mp4' => 'mp4', 'video/webm' => 'webm']],
    ][$kind];
    if ($fl['size'] > $map['max']) { $error = ucfirst($field) . ' is too large (max ' . round($map['max'] / 1048576) . ' MB).'; return null; }
    if (!is_uploaded_file($fl['tmp_name'])) { $error = 'Invalid upload.'; return null; }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($fl['tmp_name']);
    if (!isset($map['types'][$mime])) { $error = ucfirst($field) . ': unsupported file type (allowed: ' . implode(', ', array_unique($map['types'])) . ').'; return null; }
    if ($kind === 'image' && !@getimagesize($fl['tmp_name'])) { $error = 'Not a valid image.'; return null; }
    $name = $dir . '/' . bin2hex(random_bytes(12)) . '.' . $map['types'][$mime];
    if (!move_uploaded_file($fl['tmp_name'], ROOT_PATH . '/uploads/' . $name)) { $error = 'Could not save the uploaded file (check folder permissions).'; return null; }
    return $name;
}
function delete_upload(?string $file): void { if ($file && preg_match('#^(covers|audio|video|artists)/[a-f0-9]+\.\w+$#', $file) && is_file(ROOT_PATH . '/uploads/' . $file)) @unlink(ROOT_PATH . '/uploads/' . $file); }
function valid_year(int $y): bool { return (bool)val('SELECT 1 FROM years WHERE year=?', [$y]); }
