<?php
$title = 'Search';
require __DIR__ . '/includes/header.php';
$f = read_filters();
$t = in_array($f['type'], ['music', 'video'], true) ? $f['type'] : 'all';
$f['type'] = $t;
$per = $t === 'all' ? 8 : max(4, min(48, (int)setting('items_per_page', '12')));
$page = max(1, (int)($_GET['page'] ?? 1));
$res = [];
foreach (['music', 'video'] as $ty) if ($t === 'all' || $t === $ty) $res[$ty] = items_query($ty, $f, $t === 'all' ? 1 : $page, $per);
$hasFilter = $f['q'] !== '' || $f['artist'] || $f['album'] || $f['genre'] || $f['language'] || $f['year'];
$ctx = [];
if ($f['artist']) $ctx[] = 'artist: ' . val('SELECT name FROM artists WHERE id=?', [$f['artist']]);
if ($f['album']) $ctx[] = 'album: ' . val('SELECT title FROM albums WHERE id=?', [$f['album']]);
?>
<div class="page-head"><h1>Search</h1><?php if ($ctx): ?><p class="text-muted mb-0">Filtering by <?= e(implode(', ', array_filter($ctx))) ?></p><?php endif; ?></div>
<?= filter_form(url('search.php'), $f, 'all') ?>
<?php if (!$hasFilter): ?>
  <div class="empty"><i class="bi bi-search"></i><p>Type a keyword or choose filters to start searching.</p></div>
<?php else: $found = 0; foreach ($res as $ty => [$rows, $total, $pg, $pages]): $found += $total; if (!$total) continue; ?>
  <section class="mb-4">
    <div class="sec-head"><h2><?= $ty === 'music' ? 'Music' : 'Videos' ?> <small class="text-muted fs-6">(<?= (int)$total ?>)</small></h2>
      <?php if ($t === 'all' && $total > $per): ?><a href="?<?= e(http_build_query(array_filter(array_merge($f, ['type' => $ty])))) ?>">See all <i class="bi bi-arrow-right"></i></a><?php endif; ?></div>
    <div class="row g-3"><?php foreach ($rows as $r) echo card($r, $ty); ?></div>
    <?php if ($t !== 'all') echo '<div class="mt-4">' . pagination($pg, $pages, $f) . '</div>'; ?>
  </section>
<?php endforeach; if (!$found): ?><div class="empty"><i class="bi bi-emoji-frown"></i><p>No results found. Try different keywords or fewer filters.</p></div><?php endif; endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
