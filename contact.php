<?php
$title = 'Contact';
require_once __DIR__ . '/includes/bootstrap.php';
$me = current_user(); $errors = [];
$v = ['name' => $me['full_name'] ?? '', 'email' => $me['email'] ?? '', 'subject' => '', 'message' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) $errors[] = 'Security token invalid. Please try again.';
    foreach ($v as $k => $_) $v[$k] = trim((string)($_POST[$k] ?? ''));
    if (!empty($_POST['website'])) { redirect('contact.php'); } // honeypot
    if (mb_strlen($v['name']) < 2 || mb_strlen($v['name']) > 100) $errors[] = 'Please enter your name.';
    if (!filter_var($v['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if (mb_strlen($v['subject']) < 3 || mb_strlen($v['subject']) > 150) $errors[] = 'Subject must be 3–150 characters.';
    if (mb_strlen($v['message']) < 10 || mb_strlen($v['message']) > 3000) $errors[] = 'Message must be 10–3000 characters.';
    if (!$errors && ($_SESSION['last_contact'] ?? 0) > time() - 20) $errors[] = 'Please wait a few seconds before sending another message.';
    if (!$errors) {
        q('INSERT INTO contact_messages (name,email,subject,message) VALUES (?,?,?,?)', [$v['name'], $v['email'], $v['subject'], $v['message']]);
        $_SESSION['last_contact'] = time();
        flash('success', 'Thanks! Your message has been sent. We will get back to you soon.'); redirect('contact.php');
    }
}
require __DIR__ . '/includes/header.php';
?>
<div class="page-head"><h1>Contact us</h1></div>
<div class="row g-4">
  <div class="col-lg-7"><div class="panel">
    <?php foreach ($errors as $er): ?><div class="alert alert-danger py-2"><?= e($er) ?></div><?php endforeach; ?>
    <form method="post" class="row g-3"><?= csrf_field() ?>
      <div style="position:absolute;left:-9999px" aria-hidden="true"><input name="website" tabindex="-1" autocomplete="off"></div>
      <div class="col-md-6"><label class="form-label">Name</label><input name="name" class="form-control" value="<?= e($v['name']) ?>" required maxlength="100"></div>
      <div class="col-md-6"><label class="form-label">Email</label><input name="email" type="email" class="form-control" value="<?= e($v['email']) ?>" required maxlength="120"></div>
      <div class="col-12"><label class="form-label">Subject</label><input name="subject" class="form-control" value="<?= e($v['subject']) ?>" required maxlength="150"></div>
      <div class="col-12"><label class="form-label">Message</label><textarea name="message" rows="6" class="form-control" required maxlength="3000"><?= e($v['message']) ?></textarea></div>
      <div class="col-12"><button class="btn btn-sound">Send message</button></div>
    </form></div></div>
  <div class="col-lg-5"><div class="panel vstack gap-3">
    <div><i class="bi bi-envelope text-sound"></i> <?= e(setting('contact_email')) ?></div>
    <div><i class="bi bi-telephone text-sound"></i> <?= e(setting('contact_phone')) ?></div>
    <div><i class="bi bi-geo-alt text-sound"></i> <?= e(setting('contact_address')) ?></div></div></div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
