<?php
$title = '';
require __DIR__ . '/includes/header.php';
[$songs] = items_query('music', [], 1, 5);
[$vids] = items_query('video', [], 1, 5);
$artists = all('SELECT a.*, (SELECT COUNT(*) FROM music m WHERE m.artist_id=a.id) + (SELECT COUNT(*) FROM videos v WHERE v.artist_id=a.id) works FROM artists a ORDER BY works DESC, a.name LIMIT 6');
$genres = all('SELECT g.*, (SELECT COUNT(*) FROM music m WHERE m.genre_id=g.id) + (SELECT COUNT(*) FROM videos v WHERE v.genre_id=g.id) n FROM genres g ORDER BY n DESC, g.name');
?>
<section class="hero mb-5">
  <div class="hero-bg"></div>
  <div class="hero-inner">
    <span class="eyebrow">Music &amp; Video</span>
    <h1>Feel every <span class="grad">beat</span>.<br>Watch every <span class="grad">moment</span>.</h1>
    <p class="lead text-muted"><?= e(setting('tagline')) ?> Discover new releases, explore artists and share your ratings.</p>
    <form action="<?= e(url('search.php')) ?>" method="get" class="hero-search">
      <i class="bi bi-search"></i><input name="q" maxlength="100" placeholder="Search by title, artist, album, year, genre or language" aria-label="Search">
      <button class="btn btn-sound">Search</button>
    </form>
    <div class="eq" aria-hidden="true"><?php for ($i = 0; $i < 18; $i++) echo '<span style="--d:' . (0.6 + ($i % 5) * 0.15) . 's;--l:' . ($i * 0.07) . 's"></span>'; ?></div>
  </div>
</section>

<section class="mb-5">
  <div class="sec-head"><h2>Latest songs</h2><a href="<?= e(url('music.php')) ?>">View all <i class="bi bi-arrow-right"></i></a></div>
  <div class="row g-3 row-5"><?php foreach ($songs as $r) echo card($r, 'music'); ?><?php if (!$songs) echo '<p class="text-muted">No songs yet.</p>'; ?></div>
</section>
<section class="mb-5">
  <div class="sec-head"><h2>Latest videos</h2><a href="<?= e(url('videos.php')) ?>">View all <i class="bi bi-arrow-right"></i></a></div>
  <div class="row g-3 row-5"><?php foreach ($vids as $r) echo card($r, 'video'); ?><?php if (!$vids) echo '<p class="text-muted">No videos yet.</p>'; ?></div>
</section>
<section class="mb-5">
  <div class="sec-head"><h2>Featured artists</h2><a href="<?= e(url('browse.php?by=artists')) ?>">All artists <i class="bi bi-arrow-right"></i></a></div>
  <div class="row g-3">
    <?php foreach ($artists as $a): ?>
    <div class="col-6 col-md-4 col-lg-2 reveal"><a class="artist-card" href="<?= e(url('search.php?artist=' . $a['id'])) ?>">
      <?= cover_html($a, 'artist', 'round') ?><div class="fw-semibold mt-2 text-truncate"><?= e($a['name']) ?></div><div class="small text-muted"><?= (int)$a['works'] ?> titles</div></a></div>
    <?php endforeach; ?>
  </div>
</section>
<section class="mb-2">
  <div class="sec-head"><h2>Browse by genre</h2><a href="<?= e(url('browse.php')) ?>">All categories <i class="bi bi-arrow-right"></i></a></div>
  <div class="row g-3">
    <?php foreach ($genres as $g): ?>
    <div class="col-6 col-md-4 col-lg-2 reveal"><a class="genre-tile" style="--h:<?= hue((int)$g['id'] * 3) ?>" href="<?= e(url('search.php?genre=' . $g['id'])) ?>"><span><?= e($g['name']) ?></span><small><?= (int)$g['n'] ?> titles</small></a></div>
    <?php endforeach; ?>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
