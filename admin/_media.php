<?php
// Expects $type = 'music' | 'video'
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin();
$music = $type === 'music';
$t = tbl($type);
$imgCol = $music ? 'cover' : 'thumbnail';
$mediaCol = $music ? 'audio_file' : 'video_file';
$kind = $music ? 'audio' : 'video';
$self = $music ? 'music.php' : 'videos.php';
$sel = fn($table, $id) => $id !== '' && $id !== null && val("SELECT 1 FROM $table WHERE id=?", [(int)$id]) ? (int)$id : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  require_post_csrf('admin/' . $self);
  $do = $_POST['do'] ?? '';
  $id = (int)($_POST['id'] ?? 0);
  if ($do === 'delete') {
    $row = one("SELECT * FROM $t WHERE id=?", [$id]);
    if ($row) {
      q("DELETE FROM $t WHERE id=?", [$id]);
      delete_upload($row[$imgCol]);
      delete_upload($row[$mediaCol]);
      flash('success', 'Deleted "' . $row['title'] . '" (its ratings and reviews were removed too).');
    } else flash('danger', 'Item not found.');
    redirect('admin/' . $self);
  }
  if ($do === 'save') {
    $old = $id ? one("SELECT * FROM $t WHERE id=?", [$id]) : null;
    if ($id && !$old) {
      flash('danger', 'Item not found.');
      redirect('admin/' . $self);
    }
    $d = [
      'title' => trim((string)($_POST['title'] ?? '')),
      'description' => trim((string)($_POST['description'] ?? '')),
      'artist_id' => $sel('artists', $_POST['artist_id'] ?? ''),
      'genre_id' => $sel('genres', $_POST['genre_id'] ?? ''),
      'language_id' => $sel('languages', $_POST['language_id'] ?? '')
    ];
    $yr = $_POST['year'] ?? '';
    $d['year'] = $yr !== '' && ctype_digit((string)$yr) && valid_year((int)$yr) ? (int)$yr : null;
    if ($music) $d['album_id'] = $sel('albums', $_POST['album_id'] ?? '');
    $err = [];
    if ($d['title'] === '' || mb_strlen($d['title']) > 150) $err[] = 'Title is required (max 150 characters).';
    if ($music && !$d['artist_id']) $err[] = 'Please choose an artist.';
    if (mb_strlen($d['description']) > 5000) $err[] = 'Description is too long.';
    $newImg = upload_file($imgCol, 'covers', 'image', $e1);
    if ($e1) $err[] = $e1;
    $newMedia = upload_file($mediaCol, $music ? 'audio' : 'video', $kind, $e2);
    if ($e2) $err[] = $e2;
    if ($err) {
      delete_upload($newImg);
      delete_upload($newMedia);
      foreach ($err as $x) flash('danger', $x);
      $_SESSION['form_old'] = $_POST;
      redirect('admin/' . $self . '?action=' . ($id ? 'edit&id=' . $id : 'new'));
    }
    $d[$imgCol] = $old[$imgCol] ?? null;
    $d[$mediaCol] = $old[$mediaCol] ?? null;
    if ($newImg) {
      delete_upload($old[$imgCol] ?? null);
      $d[$imgCol] = $newImg;
    } elseif (!empty($_POST['remove_img'])) {
      delete_upload($old[$imgCol] ?? null);
      $d[$imgCol] = null;
    }
    if ($newMedia) {
      delete_upload($old[$mediaCol] ?? null);
      $d[$mediaCol] = $newMedia;
    } elseif (!empty($_POST['remove_media'])) {
      delete_upload($old[$mediaCol] ?? null);
      $d[$mediaCol] = null;
    }
    $cols = array_keys($d);
    if ($id) q("UPDATE $t SET " . implode(',', array_map(fn($c) => "$c=?", $cols)) . " WHERE id=?", array_merge(array_values($d), [$id]));
    else q("INSERT INTO $t (" . implode(',', $cols) . ") VALUES (" . implode(',', array_fill(0, count($cols), '?')) . ")", array_values($d));
    flash('success', ($id ? 'Updated' : 'Added') . ' "' . $d['title'] . '".');
    redirect('admin/' . $self);
  }
}

$title = 'Manage ' . ($music ? 'music' : 'videos');
require __DIR__ . '/_top.php';
$action = $_GET['action'] ?? 'list';
if ($action === 'new' || $action === 'edit') {
  $row = $action === 'edit' ? one("SELECT * FROM $t WHERE id=?", [(int)($_GET['id'] ?? 0)]) : [];
  if ($action === 'edit' && !$row) {
    echo '<div class="alert alert-danger">Item not found.</div>';
    require __DIR__ . '/_bottom.php';
    exit;
  }
  if (!empty($_SESSION['form_old'])) {
    $row = array_merge($row ?: [], $_SESSION['form_old']);
    unset($_SESSION['form_old']);
  }
  $g = fn($k) => $row[$k] ?? '';
  $opts = function ($name, $rows, $k, $l, $cur, $req = false) {
    $o = '<select name="' . $name . '" class="form-select"' . ($req ? ' required' : '') . '><option value="">' . ($req ? '— choose —' : '— none —') . '</option>';
    foreach ($rows as $r) $o .= '<option value="' . e($r[$k]) . '"' . ((string)$cur === (string)$r[$k] ? ' selected' : '') . '>' . e($r[$l]) . '</option>';
    return $o . '</select>';
  };
  $curImg = $row[$imgCol] ?? null;
  $curMedia = $row[$mediaCol] ?? null;
?>
  <div class="page-head d-flex justify-content-between align-items-center">
    <h1 class="h3"><?= $row && isset($row['id']) ? 'Edit' : 'Add' ?> <?= $music ? 'song' : 'video' ?></h1><a href="<?= e(url('admin/' . $self)) ?>" class="btn btn-outline-light btn-sm">&larr; Back</a>
  </div>
  <form method="post" enctype="multipart/form-data" class="panel row g-3"><?= csrf_field() ?><input type="hidden" name="do" value="save"><input type="hidden" name="id" value="<?= (int)($row['id'] ?? 0) ?>">
    <div class="col-12"><label class="form-label">Title *</label><input name="title" class="form-control" value="<?= e($g('title')) ?>" required maxlength="150"></div>
    <div class="col-md-6"><label class="form-label">Artist<?= $music ? ' *' : '' ?></label><?= $opts('artist_id', all('SELECT id,name FROM artists ORDER BY name'), 'id', 'name', $g('artist_id'), $music) ?></div>
    <?php if ($music): ?><div class="col-md-6"><label class="form-label">Album</label><?= $opts('album_id', all('SELECT al.id, CONCAT(al.title," — ",ar.name) n FROM albums al JOIN artists ar ON ar.id=al.artist_id ORDER BY al.title'), 'id', 'n', $g('album_id')) ?></div><?php endif; ?>
    <div class="col-md-4"><label class="form-label"><?= $music ? 'Genre' : 'Category (genre)' ?></label><?= $opts('genre_id', all('SELECT id,name FROM genres ORDER BY name'), 'id', 'name', $g('genre_id')) ?></div>
    <div class="col-md-4"><label class="form-label">Language</label><?= $opts('language_id', all('SELECT id,name FROM languages ORDER BY name'), 'id', 'name', $g('language_id')) ?></div>
    <div class="col-md-4"><label class="form-label">Year</label><?= $opts('year', all('SELECT year FROM years ORDER BY year DESC'), 'year', 'year', $g('year')) ?></div>
    <div class="col-12"><label class="form-label">Description</label><textarea name="description" rows="4" class="form-control" maxlength="5000"><?= e($g('description')) ?></textarea></div>
    <div class="col-md-6"><label class="form-label"><?= $music ? 'Cover image' : 'Thumbnail' ?> <span class="text-muted small">(JPG/PNG/WebP, max <?= round(MAX_IMAGE_SIZE / 1048576) ?> MB)</span></label><input type="file" name="<?= $imgCol ?>" accept="image/jpeg,image/png,image/webp" class="form-control">
      <?php if ($curImg): ?><div class="form-check mt-2"><input class="form-check-input" type="checkbox" name="remove_img" value="1" id="ri"><label class="form-check-label" for="ri">Remove current image (<?= e($curImg) ?>)</label></div><?php endif; ?></div>
    <div class="col-md-6"><label class="form-label"><?= $music ? 'Audio file' : 'Video file' ?> <span class="text-muted small">(<?= $music ? 'MP3/WAV/OGG' : 'MP4/WebM' ?>, max <?= round(($music ? MAX_AUDIO_SIZE : MAX_VIDEO_SIZE) / 1048576) ?> MB)</span></label><input type="file" name="<?= $mediaCol ?>" accept="<?= $music ? 'audio/mpeg,audio/wav,audio/ogg' : 'video/mp4,video/webm' ?>" class="form-control">
      <?php if ($curMedia): ?><div class="form-check mt-2"><input class="form-check-input" type="checkbox" name="remove_media" value="1" id="rm"><label class="form-check-label" for="rm">Remove current file (<?= e($curMedia) ?>)</label></div><?php endif; ?></div>
    <div class="col-12"><button class="btn btn-sound">Save</button> <a href="<?= e(url('admin/' . $self)) ?>" class="btn btn-outline-light">Cancel</a></div>
  </form>
<?php
} else {
  $qs = trim((string)($_GET['q'] ?? ''));
  $page = max(1, (int)($_GET['page'] ?? 1));
  $per = 15;
  $like = '%' . addcslashes($qs, '%_\\') . '%';
  $total = (int)val("SELECT COUNT(*) FROM $t t LEFT JOIN artists a ON a.id=t.artist_id WHERE t.title LIKE ? OR a.name LIKE ?", [$like, $like]);
  $pages = max(1, (int)ceil($total / $per));
  $page = min($page, $pages);
  $off = ($page - 1) * $per;
  $rows = all("SELECT t.*, a.name artist_name, g.name genre_name FROM $t t LEFT JOIN artists a ON a.id=t.artist_id LEFT JOIN genres g ON g.id=t.genre_id WHERE t.title LIKE ? OR a.name LIKE ? ORDER BY t.created_at DESC, t.id DESC LIMIT $per OFFSET $off", [$like, $like]);
?>
  <div class="page-head d-flex flex-wrap gap-2 justify-content-between align-items-center">
    <h1 class="h3 mb-0"><?= $music ? 'Music' : 'Videos' ?> <small class="text-muted fs-6">(<?= $total ?>)</small></h1>
    <div class="d-flex gap-2">
      <form class="d-flex gap-1"><input name="q" value="<?= e($qs) ?>" class="form-control form-control-sm" placeholder="Search…"><button class="btn btn-sm btn-outline-light">Search</button></form><a class="btn btn-sound btn-sm" href="?action=new"><i class="bi bi-plus-lg"></i> Add <?= $music ? 'song' : 'video' ?></a>
    </div>
  </div>
  <div class="table-wrap">
    <table class="table align-middle mb-0">
      <thead>
        <tr>
          <th></th>
          <th>Title</th>
          <th>Artist</th>
          <th class="d-none d-md-table-cell">Genre</th>
          <th class="d-none d-md-table-cell">Media</th>
          <th class="d-none d-lg-table-cell">Added</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): $has = $r[$mediaCol] && is_file(ROOT_PATH . '/uploads/' . $r[$mediaCol]); ?>
          <tr>
            <td>
              <div class="thumb-sm"><?= cover_html($r, $type) ?></div>
            </td>
            <td><a href="<?= e(item_url($type, (int)$r['id'])) ?>" target="_blank"><?= e($r['title']) ?></a><?= is_new($r['created_at']) ? ' <span class="badge text-bg-primary">NEW</span>' : '' ?></td>
            <td><?= e($r['artist_name'] ?? '—') ?></td>
            <td class="d-none d-md-table-cell"><?= e($r['genre_name'] ?? '—') ?></td>
            <td class="d-none d-md-table-cell"><?= $has ? '<span class="text-success"><i class="bi bi-check-circle"></i> file</span>' : '<span class="text-muted">none</span>' ?></td>
            <td class="d-none d-lg-table-cell text-muted small"><?= e(date('M j, Y', strtotime($r['created_at'] . ' UTC'))) ?></td>
            <td class="text-end text-nowrap"><a class="btn btn-sm btn-outline-light" href="?action=edit&id=<?= (int)$r['id'] ?>"><i class="bi bi-pencil"></i></a>
              <form method="post" class="d-inline"><?= csrf_field() ?><input type="hidden" name="do" value="delete"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><button class="btn btn-sm btn-outline-danger" data-confirm="Delete &quot;<?= e($r['title']) ?>&quot;? Its ratings, reviews and uploaded files will be removed.<?= '' ?>"><i class="bi bi-trash"></i></button></form>
            </td>
          </tr>
        <?php endforeach;
        if (!$rows) echo '<tr><td colspan="7" class="text-center text-muted py-4">Nothing found.</td></tr>'; ?>
      </tbody>
    </table>
  </div>
  <div class="mt-3"><?= pagination($page, $pages, ['q' => $qs]) ?></div>
<?php
}
require __DIR__ . '/_bottom.php';
