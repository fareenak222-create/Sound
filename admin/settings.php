<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin();
$fields = ['site_name' => ['Site name', 'text', 40], 'tagline' => ['Tagline', 'text', 150], 'about_text' => ['About text', 'textarea', 5000], 'contact_email' => ['Contact email', 'email', 120], 'contact_phone' => ['Contact phone', 'text', 40], 'contact_address' => ['Contact address', 'text', 200], 'footer_text' => ['Footer text', 'text', 200], 'new_badge_days' => ['NEW badge period (days)', 'number', 3], 'items_per_page' => ['Items per page (4–48)', 'number', 2]];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_post_csrf('admin/settings.php');
    $err = [];
    $vals = [];
    foreach ($fields as $k => [$lab, $ty, $max]) {
        $v = trim((string) ($_POST[$k] ?? ''));
        if ($ty === 'number' && (!ctype_digit($v) || (int) $v < 1 || ($k === 'items_per_page' && ((int) $v < 4 || (int) $v > 48)) || ($k === 'new_badge_days' && (int) $v > 365)))
            $err[] = "$lab is invalid.";
        if ($ty === 'email' && !filter_var($v, FILTER_VALIDATE_EMAIL))
            $err[] = "$lab must be a valid email.";
        if ($k === 'site_name' && $v === '')
            $err[] = 'Site name is required.';
        if (mb_strlen($v) > $max && $ty !== 'number')
            $err[] = "$lab is too long.";
        $vals[$k] = $v;
    }
    if ($err)
        foreach ($err as $x)
            flash('danger', $x);
    else {
        foreach ($vals as $k => $v)
            q('INSERT INTO settings (setting_key, setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)', [$k, $v]);
        flash('success', 'Settings saved.');
    }
    redirect('admin/settings.php');
}
$title = 'Site settings';
require __DIR__ . '/_top.php';
?>
<div class="page-head">
    <h1 class="h3">Website settings</h1>
</div>
<form method="post" class="panel vstack gap-3" style="max-width:760px"><?= csrf_field() ?>
    <?php foreach ($fields as $k => [$lab, $ty, $max]): ?>
        <div><label class="form-label"><?= e($lab) ?></label>
            <?php if ($ty === 'textarea'): ?><textarea name="<?= $k ?>" rows="6" class="form-control"
                    maxlength="<?= $max ?>"><?= e(setting($k)) ?></textarea>
            <?php else: ?><input name="<?= $k ?>" type="<?= $ty ?>" class="form-control" value="<?= e(setting($k)) ?>"
                    <?= $ty === 'number' ? 'min="1"' : 'maxlength="' . $max . '"' ?> required><?php endif; ?></div>
    <?php endforeach; ?>
    <div><button class="btn btn-sound">Save settings</button></div>
</form>
<?php require __DIR__ . '/_bottom.php'; ?>