<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  require_post_csrf('admin/reviews.php');
  $id = (int) ($_POST['id'] ?? 0);
  $do = $_POST['do'] ?? '';
  if (!val('SELECT 1 FROM reviews WHERE id=?', [$id]))
    flash('danger', 'Review not found.');
  elseif ($do === 'hide' || $do === 'show') {
    q('UPDATE reviews SET status=? WHERE id=?', [$do === 'hide' ? 'hidden' : 'visible', $id]);
    flash('success', 'Review ' . ($do === 'hide' ? 'hidden' : 'made visible') . '.');
  } elseif ($do === 'delete') {
    q('DELETE FROM reviews WHERE id=?', [$id]);
    flash('success', 'Review deleted.');
  }
  redirect('admin/reviews.php' . (!empty($_GET['status']) ? '?status=' . urlencode($_GET['status']) : ''));
}
$title = 'Moderate reviews';
require __DIR__ . '/_top.php';
$st = in_array($_GET['status'] ?? '', ['visible', 'hidden'], true) ? $_GET['status'] : '';
$rows = all("SELECT rv.*, u.user_id uname, m.title mt, v.title vt, (SELECT rating FROM ratings r WHERE r.user_id=rv.user_id AND (r.music_id<=>rv.music_id) AND (r.video_id<=>rv.video_id)) rating
  FROM reviews rv JOIN users u ON u.id=rv.user_id LEFT JOIN music m ON m.id=rv.music_id LEFT JOIN videos v ON v.id=rv.video_id " . ($st ? 'WHERE rv.status=?' : '') . " ORDER BY rv.created_at DESC LIMIT 200", $st ? [$st] : []);
?>
<div class="page-head d-flex justify-content-between flex-wrap gap-2">
  <h1 class="h3 mb-0">Reviews <small class="text-muted fs-6">(<?= count($rows) ?>)</small></h1>
  <div class="btn-group btn-group-sm"><a class="btn btn-outline-light<?= $st === '' ? ' active' : '' ?>"
      href="?">All</a><a class="btn btn-outline-light<?= $st === 'visible' ? ' active' : '' ?>"
      href="?status=visible">Visible</a><a class="btn btn-outline-light<?= $st === 'hidden' ? ' active' : '' ?>"
      href="?status=hidden">Hidden</a></div>
</div>
<?php foreach ($rows as $r):
  $isM = $r['music_id']; ?>
  <div class="review">
    <div class="d-flex justify-content-between flex-wrap gap-2">
      <div><strong>@<?= e($r['uname']) ?></strong> on <a
          href="<?= e(item_url($isM ? 'music' : 'video', (int) ($isM ?: $r['video_id']))) ?>"><?= e($isM ? $r['mt'] : $r['vt']) ?></a>
        <span class="badge text-bg-secondary"><?= $isM ? 'song' : 'video' ?></span> <span
          class="badge text-bg-<?= $r['status'] === 'visible' ? 'success' : 'warning' ?>"><?= e($r['status']) ?></span>
      </div>
      <div><?= $r['rating'] ? stars((float) $r['rating']) : '' ?></div>
    </div>
    <p class="my-2"><?= nl2br(e($r['comment'])) ?></p>
    <form method="post" class="d-flex gap-2"><?= csrf_field() ?><input type="hidden" name="id"
        value="<?= (int) $r['id'] ?>">
      <button name="do" value="<?= $r['status'] === 'visible' ? 'hide' : 'show' ?>"
        class="btn btn-sm btn-outline-warning"><?= $r['status'] === 'visible' ? 'Hide' : 'Show' ?></button>
      <button name="do" value="delete" class="btn btn-sm btn-outline-danger"
        data-confirm="Permanently delete this review?">Delete</button>
    </form>
  </div>
<?php endforeach;
if (!$rows)
  echo '<p class="text-muted">No reviews.</p>'; ?>
<?php require __DIR__ . '/_bottom.php'; ?>