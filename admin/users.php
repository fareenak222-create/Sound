<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$admin = require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  require_post_csrf('admin/users.php');
  $id = (int) ($_POST['id'] ?? 0);
  $do = $_POST['do'] ?? '';
  $u = one('SELECT * FROM users WHERE id=?', [$id]);
  if (!$u)
    flash('danger', 'User not found.');
  elseif ($u['id'] == $admin['id'] && $do !== 'noop')
    flash('danger', 'You cannot change or delete your own account here.');
  else {
    $admins = (int) val("SELECT COUNT(*) FROM users WHERE role='admin' AND status='active'");
    $lastAdmin = $u['role'] === 'admin' && $u['status'] === 'active' && $admins <= 1;
    if ($do === 'toggle') {
      if ($lastAdmin)
        flash('danger', 'Cannot suspend the last active administrator.');
      else {
        q('UPDATE users SET status=? WHERE id=?', [$u['status'] === 'active' ? 'suspended' : 'active', $id]);
        flash('success', 'Account status updated.');
      }
    } elseif ($do === 'role') {
      if ($lastAdmin)
        flash('danger', 'Cannot demote the last active administrator.');
      else {
        q('UPDATE users SET role=? WHERE id=?', [$u['role'] === 'admin' ? 'user' : 'admin', $id]);
        flash('success', 'Role updated.');
      }
    } elseif ($do === 'delete') {
      if ($lastAdmin)
        flash('danger', 'Cannot delete the last active administrator.');
      else {
        q('DELETE FROM users WHERE id=?', [$id]);
        flash('success', 'User and their reviews/ratings deleted.');
      }
    }
  }
  redirect('admin/users.php' . (isset($_GET['q']) ? '?q=' . urlencode($_GET['q']) : ''));
}
$title = 'Manage users';
require __DIR__ . '/_top.php';
$qs = trim((string) ($_GET['q'] ?? ''));
$like = '%' . addcslashes($qs, '%_\\') . '%';
$users = all('SELECT u.*, (SELECT COUNT(*) FROM reviews r WHERE r.user_id=u.id) revs FROM users u WHERE u.user_id LIKE ? OR u.full_name LIKE ? OR u.email LIKE ? ORDER BY u.created_at DESC LIMIT 200', [$like, $like, $like]);
$btn = fn($id, $do, $txt, $cls, $conf = '') => '<form method="post" class="d-inline">' . csrf_field() . '<input type="hidden" name="id" value="' . $id . '"><input type="hidden" name="do" value="' . $do . '"><button class="btn btn-sm ' . $cls . '"' . ($conf ? ' data-confirm="' . e($conf) . '"' : '') . '>' . $txt . '</button></form> ';
?>
<div class="page-head d-flex justify-content-between flex-wrap gap-2">
  <h1 class="h3 mb-0">Users <small class="text-muted fs-6">(<?= count($users) ?>)</small></h1>
  <form class="d-flex gap-1"><input name="q" value="<?= e($qs) ?>" class="form-control form-control-sm"
      placeholder="Search users…"><button class="btn btn-sm btn-outline-light">Search</button></form>
</div>
<div class="table-wrap">
  <table class="table align-middle mb-0">
    <thead>
      <tr>
        <th>User</th>
        <th class="d-none d-md-table-cell">Contact</th>
        <th>Role</th>
        <th>Status</th>
        <th class="text-end">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($users as $u):
        $self = $u['id'] == $admin['id']; ?>
        <tr>
          <td>
            <div class="fw-semibold"><?= e($u['full_name']) ?></div>
            <div class="small text-muted">@<?= e($u['user_id']) ?> · <?= (int) $u['revs'] ?> reviews</div>
          </td>
          <td class="d-none d-md-table-cell small"><?= e($u['email']) ?><br><?= e($u['phone']) ?></td>
          <td><span
              class="badge text-bg-<?= $u['role'] === 'admin' ? 'primary' : 'secondary' ?>"><?= e($u['role']) ?></span>
          </td>
          <td><span
              class="badge text-bg-<?= $u['status'] === 'active' ? 'success' : 'danger' ?>"><?= e($u['status']) ?></span>
          </td>
          <td class="text-end text-nowrap"><?php if ($self): ?><span
                class="text-muted small">you</span><?php else:
            echo $btn($u['id'], 'toggle', $u['status'] === 'active' ? 'Suspend' : 'Activate', 'btn-outline-warning');
            echo $btn($u['id'], 'role', $u['role'] === 'admin' ? 'Make user' : 'Make admin', 'btn-outline-light', 'Change this account\'s role?');
            echo $btn($u['id'], 'delete', '<i class="bi bi-trash"></i>', 'btn-outline-danger', 'Delete this user and all their reviews and ratings?');
          endif; ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php require __DIR__ . '/_bottom.php'; ?>