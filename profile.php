<?php
$title = 'My profile';
require_once __DIR__ . '/includes/bootstrap.php';
$me = require_login();
$errors = []; $v = $me;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) { flash('danger', 'Security token invalid. Please try again.'); redirect('profile.php'); }
    $do = $_POST['do'] ?? '';
    if ($do === 'profile') {
        foreach (['full_name', 'address', 'phone', 'email'] as $k) $v[$k] = trim((string)($_POST[$k] ?? ''));
        if (mb_strlen($v['full_name']) < 3 || mb_strlen($v['full_name']) > 100) $errors[] = 'Full name must be 3–100 characters.';
        if (mb_strlen($v['address']) < 5 || mb_strlen($v['address']) > 255) $errors[] = 'Please enter your address (5–255 characters).';
        if (!preg_match('/^\+?[0-9 \-]{7,20}$/', $v['phone'])) $errors[] = 'Enter a valid phone number.';
        if (!filter_var($v['email'], FILTER_VALIDATE_EMAIL) || strlen($v['email']) > 120) $errors[] = 'Enter a valid email address.';
        elseif (val('SELECT 1 FROM users WHERE email=? AND id<>?', [$v['email'], $me['id']])) $errors[] = 'That email is used by another account.';
        if (!$errors) { q('UPDATE users SET full_name=?, address=?, phone=?, email=? WHERE id=?', [$v['full_name'], $v['address'], $v['phone'], $v['email'], $me['id']]); flash('success', 'Profile updated.'); redirect('profile.php'); }
    } elseif ($do === 'password') {
        $cur = (string)($_POST['current'] ?? ''); $new = (string)($_POST['password'] ?? ''); $new2 = (string)($_POST['password_confirm'] ?? '');
        if (!password_verify($cur, $me['password_hash'])) $errors[] = 'Current password is incorrect.';
        if (strlen($new) < 8 || !preg_match('/[A-Za-z]/', $new) || !preg_match('/\d/', $new)) $errors[] = 'New password must be at least 8 characters with a letter and a number.';
        if ($new !== $new2) $errors[] = 'New passwords do not match.';
        if (!$errors) { q('UPDATE users SET password_hash=? WHERE id=?', [password_hash($new, PASSWORD_DEFAULT), $me['id']]); session_regenerate_id(true); flash('success', 'Password changed.'); redirect('profile.php'); }
    }
}
$mine = all("SELECT 'music' t, m.id, m.title, r.rating, rv.comment, rv.status FROM ratings r JOIN music m ON m.id=r.music_id LEFT JOIN reviews rv ON rv.user_id=r.user_id AND rv.music_id=r.music_id WHERE r.user_id=?
  UNION ALL SELECT 'video', v.id, v.title, r.rating, rv.comment, rv.status FROM ratings r JOIN videos v ON v.id=r.video_id LEFT JOIN reviews rv ON rv.user_id=r.user_id AND rv.video_id=r.video_id WHERE r.user_id=?", [$me['id'], $me['id']]);
require __DIR__ . '/includes/header.php';
?>
<div class="page-head"><h1>My profile</h1><p class="text-muted mb-0">User ID: <strong><?= e($me['user_id']) ?></strong> · Member since <?= e(date('M Y', strtotime($me['created_at'] . ' UTC'))) ?></p></div>
<?php foreach ($errors as $er): ?><div class="alert alert-danger py-2"><?= e($er) ?></div><?php endforeach; ?>
<div class="row g-4">
  <div class="col-lg-6"><div class="panel"><h2 class="h5">Edit details</h2>
    <form method="post" class="vstack gap-3"><?= csrf_field() ?><input type="hidden" name="do" value="profile">
      <div><label class="form-label">Full name</label><input name="full_name" class="form-control" value="<?= e($v['full_name']) ?>" required maxlength="100"></div>
      <div><label class="form-label">Email</label><input name="email" type="email" class="form-control" value="<?= e($v['email']) ?>" required maxlength="120"></div>
      <div><label class="form-label">Phone</label><input name="phone" class="form-control" value="<?= e($v['phone']) ?>" required maxlength="20"></div>
      <div><label class="form-label">Address</label><input name="address" class="form-control" value="<?= e($v['address']) ?>" required maxlength="255"></div>
      <button class="btn btn-sound">Save changes</button></form></div></div>
  <div class="col-lg-6"><div class="panel"><h2 class="h5">Change password</h2>
    <form method="post" class="vstack gap-3"><?= csrf_field() ?><input type="hidden" name="do" value="password">
      <div><label class="form-label">Current password</label><input name="current" type="password" class="form-control" required autocomplete="current-password"></div>
      <div><label class="form-label">New password</label><input name="password" type="password" class="form-control" required autocomplete="new-password"></div>
      <div><label class="form-label">Confirm new password</label><input name="password_confirm" type="password" class="form-control" required autocomplete="new-password"></div>
      <button class="btn btn-outline-light">Update password</button></form></div></div>
</div>
<h2 class="h5 mt-5 mb-3">My ratings &amp; reviews (<?= count($mine) ?>)</h2>
<?php foreach ($mine as $m): ?>
  <div class="review"><div class="d-flex justify-content-between flex-wrap gap-2"><a href="<?= e(item_url($m['t'], (int)$m['id'])) ?>" class="fw-semibold"><i class="bi <?= $m['t'] === 'music' ? 'bi-music-note' : 'bi-camera-video' ?>"></i> <?= e($m['title']) ?></a><?= stars((float)$m['rating']) ?></div>
  <?php if ($m['comment']): ?><p class="mb-0 mt-1"><?= e($m['comment']) ?><?= $m['status'] === 'hidden' ? ' <span class="badge text-bg-warning">hidden by moderator</span>' : '' ?></p><?php endif; ?></div>
<?php endforeach; if (!$mine) echo '<p class="text-muted">You have not rated anything yet.</p>'; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
