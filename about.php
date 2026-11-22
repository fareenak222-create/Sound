<?php
$title = 'About';
require __DIR__ . '/includes/header.php';
$stats = [['Songs', val('SELECT COUNT(*) FROM music')], ['Videos', val('SELECT COUNT(*) FROM videos')], ['Artists', val('SELECT COUNT(*) FROM artists')], ['Members', val('SELECT COUNT(*) FROM users')]];
?>
<div class="page-head"><h1>About <?= e(setting('site_name')) ?></h1></div>
<div class="row g-4"><div class="col-lg-8"><div class="panel fs-6"><?= nl2br(e(setting('about_text'))) ?></div></div>
<div class="col-lg-4"><div class="row g-3"><?php foreach ($stats as [$l, $n]): ?><div class="col-6"><div class="stat-tile"><div class="num"><?= (int)$n ?></div><div class="text-muted small"><?= e($l) ?></div></div></div><?php endforeach; ?></div></div></div>
<?php require __DIR__ . '/includes/footer.php'; ?>
