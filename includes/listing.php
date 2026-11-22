<?php
// Expects $type ('music'|'video') to be set by the including page.
require_once __DIR__ . '/bootstrap.php';
$f = read_filters();
$per = max(4, min(48, (int)setting('items_per_page', '12')));
[$rows, $total, $page, $pages] = items_query($type, $f, max(1, (int)($_GET['page'] ?? 1)), $per);
$title = $type === 'music' ? 'Music' : 'Videos';
require __DIR__ . '/header.php';
$self = url($type === 'music' ? 'music.php' : 'videos.php');
?>
<div class="page-head"><h1><?= e($title) ?></h1><p class="text-muted mb-0"><?= (int)$total ?> <?= $type === 'music' ? 'songs' : 'videos' ?> found</p></div>
<?= filter_form($self, $f, $type) ?>
<div class="row g-3"><?php foreach ($rows as $r) echo card($r, $type); ?></div>
<?php if (!$rows): ?><div class="empty"><i class="bi bi-emoji-frown"></i><p>Nothing matches your filters.</p><a class="btn btn-sound btn-sm" href="<?= e($self) ?>">Clear filters</a></div><?php endif; ?>
<div class="mt-4"><?= pagination($page, $pages, array_diff_key($f, ['type' => 1])) ?></div>
<?php require __DIR__ . '/footer.php';
