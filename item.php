<?php
require_once __DIR__ . '/includes/bootstrap.php';
$type = ($_GET['type'] ?? '') === 'video' ? 'video' : 'music';
$id = (int)($_GET['id'] ?? 0);
[$rows] = items_query($type, ['id' => $id], 1, 1);
$item = $rows[0] ?? null;
if (!$item) { http_response_code(404); $title = 'Not found'; require __DIR__ . '/includes/header.php'; echo '<div class="empty"><i class="bi bi-emoji-frown"></i><p>That item could not be found.</p><a class="btn btn-sound btn-sm" href="' . e(url($type === 'music' ? 'music.php' : 'videos.php')) . '">Back to list</a></div>'; require __DIR__ . '/includes/footer.php'; exit; }
$k = fk($type);
$title = $item['title'];
$me = current_user();
$file = $type === 'music' ? $item['audio_file'] : $item['video_file'];
$hasMedia = $file && is_file(ROOT_PATH . '/uploads/' . $file);
$myRating = $me ? (int)val("SELECT rating FROM ratings WHERE user_id=? AND $k=?", [$me['id'], $id]) : 0;
$myReview = $me ? one("SELECT * FROM reviews WHERE user_id=? AND $k=?", [$me['id'], $id]) : null;
$reviews = all("SELECT rv.*, u.user_id uname, (SELECT rating FROM ratings r WHERE r.user_id=rv.user_id AND r.$k=rv.$k) rating FROM reviews rv JOIN users u ON u.id=rv.user_id WHERE rv.$k=? AND rv.status='visible' ORDER BY rv.updated_at DESC", [$id]);
$dist = array_fill(1, 5, 0); foreach (all("SELECT rating, COUNT(*) n FROM ratings WHERE $k=? GROUP BY rating", [$id]) as $d) $dist[(int)$d['rating']] = (int)$d['n'];
// related: same artist or genre, either type
$related = [];
foreach (['music', 'video'] as $ty) {
    $t = tbl($ty);
    $params = [(int)$item['artist_id'], (int)($item['genre_id'] ?? 0)]; $excl = '';
    if ($ty === $type) { $excl = ' AND t.id <> ?'; $params[] = $id; }
    foreach (all("SELECT t.*, a.name artist_name, g.name genre_name" . ($ty === 'music' ? ", al.title album_title" : "") . ", (SELECT ROUND(AVG(r.rating),1) FROM ratings r WHERE r.".fk($ty)."=t.id) avg_rating, (SELECT COUNT(*) FROM ratings r WHERE r.".fk($ty)."=t.id) rating_count FROM $t t LEFT JOIN artists a ON a.id=t.artist_id LEFT JOIN genres g ON g.id=t.genre_id" . ($ty === 'music' ? " LEFT JOIN albums al ON al.id=t.album_id" : "") . " WHERE (t.artist_id=? OR t.genre_id=?)$excl ORDER BY (t.artist_id=?) DESC, t.created_at DESC LIMIT 4", array_merge([$params[0], $params[1]], array_slice($params, 2), $ty === $type ? [$item['artist_id']] : [$item['artist_id']])) as $r) $related[] = [$r, $ty];
}
$related = array_slice($related, 0, 5);
require __DIR__ . '/includes/header.php';
?>
<div class="row g-4">
  <div class="col-lg-5">
    <div class="detail-cover <?= $type === 'video' ? 'wide' : '' ?>"><?= cover_html($item, $type) ?><?php if (is_new($item['created_at'])): ?><span class="badge-new">NEW</span><?php endif; ?></div>
  </div>
  <div class="col-lg-7">
    <span class="eyebrow"><?= $type === 'music' ? 'Song' : 'Video' ?></span>
    <h1 class="mb-1"><?= e($item['title']) ?></h1>
    <p class="fs-5 mb-2"><?php if ($item['artist_id']): ?><a href="<?= e(url('search.php?artist=' . $item['artist_id'])) ?>"><?= e($item['artist_name']) ?></a><?php else: ?>Various<?php endif; ?></p>
    <div class="mb-3"><?= stars($item['avg_rating'] !== null ? (float)$item['avg_rating'] : null, (int)$item['rating_count']) ?></div>
    <div class="meta-chips mb-3">
      <?php if ($type === 'music' && $item['album_id']): ?><a href="<?= e(url('search.php?album=' . $item['album_id'])) ?>"><i class="bi bi-disc"></i> <?= e($item['album_title']) ?></a><?php endif; ?>
      <?php if ($item['genre_id']): ?><a href="<?= e(url('search.php?genre=' . $item['genre_id'])) ?>"><i class="bi bi-tag"></i> <?= e($item['genre_name']) ?></a><?php endif; ?>
      <?php if ($item['language_id']): ?><a href="<?= e(url('search.php?language=' . $item['language_id'])) ?>"><i class="bi bi-translate"></i> <?= e($item['language_name']) ?></a><?php endif; ?>
      <?php if ($item['year']): ?><a href="<?= e(url('search.php?year=' . $item['year'])) ?>"><i class="bi bi-calendar3"></i> <?= e($item['year']) ?></a><?php endif; ?>
    </div>
    <?php if ($item['description']): ?><p class="text-muted"><?= nl2br(e($item['description'])) ?></p><?php endif; ?>
    <div class="player-box">
      <?php if ($hasMedia && $type === 'music'): ?><audio controls preload="metadata" src="<?= e(url('uploads/' . $file)) ?>">Your browser does not support audio.</audio>
      <?php elseif ($hasMedia): ?><video controls preload="metadata" playsinline <?php if (!empty($item['thumbnail']) && is_file(ROOT_PATH . '/uploads/' . $item['thumbnail'])) echo 'poster="' . e(url('uploads/' . $item['thumbnail'])) . '"'; ?> src="<?= e(url('uploads/' . $file)) ?>">Your browser does not support video.</video>
      <?php else: ?><div class="text-muted"><i class="bi bi-slash-circle"></i> No <?= $type === 'music' ? 'audio' : 'video' ?> file is available for this title yet.</div><?php endif; ?>
    </div>
  </div>
</div>

<div class="row g-4 mt-2">
  <div class="col-lg-5">
    <div class="panel">
      <h3 class="h5">Ratings</h3>
      <?php $cnt = max(1, array_sum($dist)); for ($s = 5; $s >= 1; $s--): ?>
        <div class="d-flex align-items-center gap-2 small mb-1"><span style="width:2.5ch"><?= $s ?> <i class="bi bi-star-fill text-warning"></i></span>
          <div class="progress flex-grow-1" style="height:6px"><div class="progress-bar bg-sound" style="width:<?= round($dist[$s] / $cnt * 100) ?>%"></div></div><span class="text-muted" style="width:3ch"><?= $dist[$s] ?></span></div>
      <?php endfor; ?>
      <hr>
      <?php if ($me): ?>
      <form method="post" action="<?= e(url('actions/review.php')) ?>">
        <?= csrf_field() ?><input type="hidden" name="type" value="<?= e($type) ?>"><input type="hidden" name="id" value="<?= $id ?>">
        <label class="form-label"><?= $myRating ? 'Update your rating' : 'Rate this ' . ($type === 'music' ? 'song' : 'video') ?></label>
        <div class="star-input mb-2"><?php for ($s = 5; $s >= 1; $s--): ?><input type="radio" id="st<?= $s ?>" name="rating" value="<?= $s ?>" <?= $myRating === $s ? 'checked' : '' ?> required><label for="st<?= $s ?>" title="<?= $s ?> star<?= $s > 1 ? 's' : '' ?>"><i class="bi bi-star-fill"></i></label><?php endfor; ?></div>
        <label class="form-label" for="comment">Your review <span class="text-muted small">(optional)</span></label>
        <textarea id="comment" name="comment" class="form-control mb-2" rows="3" maxlength="1000" placeholder="Share what you think…"><?= e($myReview['comment'] ?? '') ?></textarea>
        <?php if ($myReview && $myReview['status'] === 'hidden'): ?><div class="small text-warning mb-2">Your review is currently hidden by a moderator.</div><?php endif; ?>
        <div class="d-flex gap-2"><button class="btn btn-sound btn-sm" name="action" value="save">Save</button>
        <?php if ($myRating || $myReview): ?><button class="btn btn-outline-danger btn-sm" name="action" value="delete" data-confirm="Delete your rating and review?">Delete mine</button><?php endif; ?></div>
      </form>
      <?php else: ?><p class="mb-0 text-muted"><a href="<?= e(url('login.php')) ?>">Log in</a> or <a href="<?= e(url('register.php')) ?>">sign up</a> to rate and review.</p><?php endif; ?>
    </div>
  </div>
  <div class="col-lg-7">
    <h3 class="h5 mb-3">Reviews (<?= count($reviews) ?>)</h3>
    <?php foreach ($reviews as $rv): ?>
      <div class="review"><div class="d-flex justify-content-between"><strong><i class="bi bi-person-circle"></i> <?= e($rv['uname']) ?></strong><small class="text-muted"><?= e(date('M j, Y', strtotime($rv['updated_at'] . ' UTC'))) ?></small></div>
      <?= $rv['rating'] ? stars((float)$rv['rating']) : '' ?><p class="mb-0 mt-1"><?= nl2br(e($rv['comment'])) ?></p></div>
    <?php endforeach; if (!$reviews) echo '<p class="text-muted">No reviews yet. Be the first!</p>'; ?>
  </div>
</div>
<?php if ($related): ?>
<section class="mt-5"><div class="sec-head"><h2>Related</h2></div><div class="row g-3 row-5"><?php foreach ($related as [$r, $ty]) echo card($r, $ty); ?></div></section>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
