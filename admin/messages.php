<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_post_csrf('admin/messages.php');
    $id = (int) ($_POST['id'] ?? 0);
    $do = $_POST['do'] ?? '';
    if ($do === 'read') {
        q('UPDATE contact_messages SET is_read=1-is_read WHERE id=?', [$id]);
    } elseif ($do === 'delete') {
        q('DELETE FROM contact_messages WHERE id=?', [$id]);
        flash('success', 'Message deleted.');
    }
    redirect('admin/messages.php');
}
$title = 'Contact messages';
require __DIR__ . '/_top.php';
$rows = all('SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 200');
?>
<div class="page-head">
    <h1 class="h3">Contact messages</h1>
</div>
<?php foreach ($rows as $m): ?>
    <div class="review" <?= !$m['is_read'] ? 'style="border-color:var(--p)"' : '' ?>>
        <div class="d-flex justify-content-between flex-wrap"><strong><?= e($m['subject']) ?>
                <?= !$m['is_read'] ? '<span class="badge text-bg-primary">new</span>' : '' ?></strong><small
                class="text-muted"><?= e(date('M j, Y H:i', strtotime($m['created_at'] . ' UTC'))) ?> UTC</small></div>
        <div class="small text-muted mb-2"><?= e($m['name']) ?> · <a
                href="mailto:<?= e($m['email']) ?>"><?= e($m['email']) ?></a></div>
        <p><?= nl2br(e($m['message'])) ?></p>
        <form method="post" class="d-flex gap-2"><?= csrf_field() ?><input type="hidden" name="id"
                value="<?= (int) $m['id'] ?>"><button name="do" value="read" class="btn btn-sm btn-outline-light">Mark
                <?= $m['is_read'] ? 'unread' : 'read' ?></button><button name="do" value="delete"
                class="btn btn-sm btn-outline-danger" data-confirm="Delete this message?">Delete</button></form>
    </div>
<?php endforeach;
if (!$rows)
    echo '<p class="text-muted">No messages yet.</p>'; ?>
<?php require __DIR__ . '/_bottom.php'; ?>