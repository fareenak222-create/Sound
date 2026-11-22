<?php
$title = 'Browse';
require __DIR__ . '/includes/header.php';
$tabs = [
  'artists'   => ['Artists', 'SELECT a.id, a.name label, (SELECT COUNT(*) FROM music m WHERE m.artist_id=a.id)+(SELECT COUNT(*) FROM videos v WHERE v.artist_id=a.id) n FROM artists a ORDER BY a.name', 'artist'],
  'albums'    => ['Albums', 'SELECT al.id, CONCAT(al.title, " — ", ar.name) label, (SELECT COUNT(*) FROM music m WHERE m.album_id=al.id) n FROM albums al JOIN artists ar ON ar.id=al.artist_id ORDER BY al.title', 'album'],
  'genres'    => ['Genres', 'SELECT g.id, g.name label, (SELECT COUNT(*) FROM music m WHERE m.genre_id=g.id)+(SELECT COUNT(*) FROM videos v WHERE v.genre_id=g.id) n FROM genres g ORDER BY g.name', 'genre'],
  'years'     => ['Years', 'SELECT y.year id, y.year label, (SELECT COUNT(*) FROM music m WHERE m.year=y.year)+(SELECT COUNT(*) FROM videos v WHERE v.year=y.year) n FROM years y ORDER BY y.year DESC', 'year'],
  'languages' => ['Languages', 'SELECT l.id, l.name label, (SELECT COUNT(*) FROM music m WHERE m.language_id=l.id)+(SELECT COUNT(*) FROM videos v WHERE v.language_id=l.id) n FROM languages l ORDER BY l.name', 'language'],
];
$by = isset($tabs[$_GET['by'] ?? '']) ? $_GET['by'] : 'genres';
$rows = all($tabs[$by][1]);
?>
<div class="page-head"><h1>Browse</h1></div>
<ul class="nav nav-pills mb-4 gap-1 flex-wrap">
<?php foreach ($tabs as $k => $t): ?><li class="nav-item"><a class="nav-link pill<?= $k === $by ? ' active' : '' ?>" href="?by=<?= e($k) ?>"><?= e($t[0]) ?></a></li><?php endforeach; ?>
</ul>
<div class="row g-3">
<?php foreach ($rows as $r): ?>
  <div class="col-6 col-md-4 col-lg-3 reveal"><a class="genre-tile" style="--h:<?= hue((int)$r['id'] * 3 + 40) ?>" href="<?= e(url('search.php?' . $tabs[$by][2] . '=' . $r['id'])) ?>"><span class="text-truncate"><?= e($r['label']) ?></span><small><?= (int)$r['n'] ?> titles</small></a></div>
<?php endforeach; if (!$rows) echo '<p class="text-muted">Nothing here yet.</p>'; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>