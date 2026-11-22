<?php
$title = 'Admin dashboard';
require __DIR__ . '/_top.php';
$s = [
 ['Songs', val('SELECT COUNT(*) FROM music'), 'bi-music-note-beamed', 'music.php'], ['Videos', val('SELECT COUNT(*) FROM videos'), 'bi-camera-video', 'videos.php'],
 ['Users', val('SELECT COUNT(*) FROM users'), 'bi-people', 'users.php'], ['Reviews', val('SELECT COUNT(*) FROM reviews'), 'bi-chat-square-text', 'reviews.php'],
 ['Ratings', val('SELECT COUNT(*) FROM ratings'), 'bi-star', 'reviews.php'], ['Avg rating', val('SELECT ROUND(AVG(rating),2) FROM ratings') ?? '–', 'bi-graph-up', 'reviews.php'],
 ['Artists', val('SELECT COUNT(*) FROM artists'), 'bi-person-lines-fill', 'taxonomy.php?t=artists'], ['Unread messages', $unread, 'bi-envelope', 'messages.php'],
];
$dist = array_fill(1, 5, 0); foreach (all('SELECT rating, COUNT(*) n FROM ratings GROUP BY rating') as $d) $dist[(int)$d['rating']] = (int)$d['n']; $mx = max(1, max($dist));
$top = all("SELECT * FROM (SELECT 'music' t, m.id, m.title, ROUND(AVG(r.rating),2) avg, COUNT(*) n FROM ratings r JOIN music m ON m.id=r.music_id GROUP BY m.id
  UNION ALL SELECT 'video', v.id, v.title, ROUND(AVG(r.rating),2), COUNT(*) FROM ratings r JOIN videos v ON v.id=r.video_id GROUP BY v.id) x ORDER BY avg DESC, n DESC LIMIT 5");
$newUsers = all('SELECT user_id, full_name, created_at FROM users ORDER BY created_at DESC LIMIT 5');
?>
<div class="page-head"><h1 class="h2">Dashboard</h1><p class="text-muted mb-0">Welcome, <?= e($admin['full_name']) ?>.</p></div>
<div class="row g-3 mb-4"><?php foreach ($s as [$l, $n, $i, $href]): ?><div class="col-6 col-md-3"><a href="<?= e(url('admin/' . $href)) ?>" class="stat-tile d-block text-decoration-none"><i class="bi <?= $i ?> text-sound"></i><div class="num"><?= e($n) ?></div><div class="text-muted small"><?= e($l) ?></div></a></div><?php endforeach; ?></div>
<div class="row g-4">
  <div class="col-lg-6"><div class="panel"><h2 class="h5">Rating distribution</h2>
    <?php for ($i = 5; $i >= 1; $i--): ?><div class="d-flex align-items-center gap-2 mb-2"><span style="width:2.5ch"><?= $i ?>★</span><div class="flex-grow-1"><div class="bar" style="width:<?= max(2, round($dist[$i] / $mx * 100)) ?>%"></div></div><span class="text-muted small"><?= $dist[$i] ?></span></div><?php endfor; ?></div></div>
  <div class="col-lg-6"><div class="panel"><h2 class="h5">Top rated</h2>
    <?php foreach ($top as $t): ?><div class="d-flex justify-content-between border-bottom py-2"><a href="<?= e(item_url($t['t'], (int)$t['id'])) ?>"><?= e($t['title']) ?></a><span><?= stars((float)$t['avg'], (int)$t['n']) ?></span></div><?php endforeach; if (!$top) echo '<p class="text-muted mb-0">No ratings yet.</p>'; ?></div></div>
  <div class="col-12"><div class="panel"><h2 class="h5">Newest members</h2>
    <?php foreach ($newUsers as $u): ?><div class="d-flex justify-content-between border-bottom py-2"><span><?= e($u['full_name']) ?> <span class="text-muted">@<?= e($u['user_id']) ?></span></span><small class="text-muted"><?= e(date('M j, Y', strtotime($u['created_at'] . ' UTC'))) ?></small></div><?php endforeach; ?></div></div>
</div>
<?php require __DIR__ . '/_bottom.php'; ?>
