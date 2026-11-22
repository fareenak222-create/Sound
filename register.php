<?php
$title = 'Sign up';
require_once __DIR__ . '/includes/bootstrap.php';
if (current_user())
  redirect('profile.php');
$errors = [];
$v = ['full_name' => '', 'user_id' => '', 'address' => '', 'phone' => '', 'email' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!csrf_ok())
    $errors[] = 'Security token invalid. Please try again.';
  foreach ($v as $k => $_)
    $v[$k] = trim((string) ($_POST[$k] ?? ''));
  $pw = (string) ($_POST['password'] ?? '');
  $pw2 = (string) ($_POST['password_confirm'] ?? '');
  if (mb_strlen($v['full_name']) < 3 || mb_strlen($v['full_name']) > 100)
    $errors[] = 'Full name must be 3–100 characters.';
  if (!preg_match('/^[A-Za-z0-9_.]{4,30}$/', $v['user_id']))
    $errors[] = 'User ID must be 4–30 characters: letters, numbers, underscore or dot.';
  if (mb_strlen($v['address']) < 5 || mb_strlen($v['address']) > 255)
    $errors[] = 'Please enter your address (5–255 characters).';
  if (!preg_match('/^\+?[0-9 \-]{7,20}$/', $v['phone']))
    $errors[] = 'Enter a valid phone number (7–20 digits, optional +).';
  if (!filter_var($v['email'], FILTER_VALIDATE_EMAIL) || strlen($v['email']) > 120)
    $errors[] = 'Enter a valid email address.';
  if (strlen($pw) < 8 || !preg_match('/[A-Za-z]/', $pw) || !preg_match('/\d/', $pw))
    $errors[] = 'Password must be at least 8 characters with a letter and a number.';
  if ($pw !== $pw2)
    $errors[] = 'Passwords do not match.';
  if (!$errors) {
    if (val('SELECT 1 FROM users WHERE user_id = ?', [$v['user_id']]))
      $errors[] = 'That User ID is already taken.';
    if (val('SELECT 1 FROM users WHERE email = ?', [$v['email']]))
      $errors[] = 'That email is already registered.';
  }
  if (!$errors) {
    try {
      q('INSERT INTO users (user_id, full_name, address, phone, email, password_hash) VALUES (?,?,?,?,?,?)', [$v['user_id'], $v['full_name'], $v['address'], $v['phone'], $v['email'], password_hash($pw, PASSWORD_DEFAULT)]);
      flash('success', 'Account created! You can log in now.');
      redirect('login.php');
    } catch (PDOException $ex) {
      $errors[] = 'User ID or email already exists.';
    }
  }
}
require __DIR__ . '/includes/header.php';
?>
<div class="auth-card mx-auto">
  <h1 class="h3 mb-1">Create your account</h1>
  <p class="text-muted">Join <?= e(setting('site_name')) ?> to rate and review.</p>
  <?php foreach ($errors as $er): ?>
    <div class="alert alert-danger py-2"><?= e($er) ?></div><?php endforeach; ?>
  <form method="post" novalidate class="row g-3"><?= csrf_field() ?>
    <div class="col-md-6"><label class="form-label">Full name</label><input name="full_name" class="form-control"
        value="<?= e($v['full_name']) ?>" required maxlength="100"></div>
    <div class="col-md-6"><label class="form-label">User ID</label><input name="user_id" class="form-control"
        value="<?= e($v['user_id']) ?>" required maxlength="30" autocomplete="username"></div>
    <div class="col-12"><label class="form-label">Address</label><input name="address" class="form-control"
        value="<?= e($v['address']) ?>" required maxlength="255"></div>
    <div class="col-md-6"><label class="form-label">Phone number</label><input name="phone" type="tel"
        class="form-control" value="<?= e($v['phone']) ?>" required maxlength="20"></div>
    <div class="col-md-6"><label class="form-label">Email</label><input name="email" type="email" class="form-control"
        value="<?= e($v['email']) ?>" required maxlength="120"></div>
    <div class="col-md-6"><label class="form-label">Password</label><input name="password" type="password"
        class="form-control" required autocomplete="new-password">
      <div class="form-text">Min 8 chars, with a letter and a number.</div>
    </div>
    <div class="col-md-6"><label class="form-label">Confirm password</label><input name="password_confirm"
        type="password" class="form-control" required autocomplete="new-password"></div>
    <div class="col-12"><button class="btn btn-sound w-100">Sign up</button></div>
  </form>
  <p class="text-center text-muted mt-3 mb-0">Already have an account? <a href="<?= e(url('login.php')) ?>">Log in</a>
  </p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>