<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin();
$u = fn($col, $tbl = 'music') => "(SELECT COUNT(*) FROM $tbl x WHERE x.$col=%s)";
$cfg = [
  'artists' => [
    'Artist',
    'artists',
    'id',
    [['name', 'Name', 'text', 1], ['bio', 'Biography', 'textarea', 0], ['image', 'Photo', 'file', 0]],
    'SELECT a.id, a.name label, LEFT(a.bio,90) sub, a.image img, (SELECT COUNT(*) FROM music m WHERE m.artist_id=a.id)+(SELECT COUNT(*) FROM videos v WHERE v.artist_id=a.id)+(SELECT COUNT(*) FROM albums b WHERE b.artist_id=a.id) uses FROM artists a ORDER BY a.name'
  ],
  'albums' => [
    'Album',
    'albums',
    'id',
    [['title', 'Title', 'text', 1], ['artist_id', 'Artist', 'select:artists:id:name', 1], ['year', 'Year', 'select:years:year:year', 0], ['cover', 'Cover image', 'file', 0]],
    "SELECT al.id, al.title label, CONCAT(ar.name, IFNULL(CONCAT(' · ',al.year),'')) sub, al.cover img, (SELECT COUNT(*) FROM music m WHERE m.album_id=al.id) uses FROM albums al JOIN artists ar ON ar.id=al.artist_id ORDER BY al.title"
  ],
  'genres' => ['Genre', 'genres', 'id', [['name', 'Name', 'text', 1]], 'SELECT g.id, g.name label, NULL sub, NULL img, (SELECT COUNT(*) FROM music m WHERE m.genre_id=g.id)+(SELECT COUNT(*) FROM videos v WHERE v.genre_id=g.id) uses FROM genres g ORDER BY g.name'],
  'languages' => ['Language', 'languages', 'id', [['name', 'Name', 'text', 1]], 'SELECT l.id, l.name label, NULL sub, NULL img, (SELECT COUNT(*) FROM music m WHERE m.language_id=l.id)+(SELECT COUNT(*) FROM videos v WHERE v.language_id=l.id) uses FROM languages l ORDER BY l.name'],
  'years' => ['Year', 'years', 'year', [['year', 'Year (1900–2100)', 'number', 1]], 'SELECT y.year id, y.year label, NULL sub, NULL img, (SELECT COUNT(*) FROM music m WHERE m.year=y.year)+(SELECT COUNT(*) FROM videos v WHERE v.year=y.year)+(SELECT COUNT(*) FROM albums b WHERE b.year=y.year) uses FROM years y ORDER BY y.year DESC'],
];
$tk = $_GET['t'] ?? $_POST['t'] ?? 'artists';
if (!isset($cfg[$tk]))
  $tk = 'artists';
[$label, $table, $pk, $fields, $listSql] = $cfg[$tk];
$self = 'admin/taxonomy.php?t=' . $tk;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  require_post_csrf($self);
  $do = $_POST['do'] ?? '';
  $id = (int) ($_POST['id'] ?? 0);
  if ($do === 'delete') {
    $old = one("SELECT * FROM $table WHERE $pk=?", [$id]);
    try {
      if (!$old)
        throw new RuntimeException('Not found.');
      q("DELETE FROM $table WHERE $pk=?", [$id]);
      foreach ($fields as $f)
        if ($f[2] === 'file')
          delete_upload($old[$f[0]]);
      flash('success', "$label deleted.");
    } catch (PDOException $ex) {
      flash('danger', "Cannot delete: this $label is still used by other records. Reassign or remove them first.");
    } catch (RuntimeException $ex) {
      flash('danger', $ex->getMessage());
    }
    redirect($self);
  }
  if ($do === 'save') {
    $old = $id ? one("SELECT * FROM $table WHERE $pk=?", [$id]) : null;
    $d = [];
    $err = [];
    $newFiles = [];
    foreach ($fields as [$col, $lab, $ty, $req]) {
      if ($ty === 'file') {
        $f = upload_file($col, $tk === 'albums' ? 'covers' : 'artists', 'image', $fe);
        if ($fe)
          $err[] = $fe;
        if ($f) {
          $newFiles[] = $f;
          $d[$col] = $f;
        } else
          $d[$col] = $old[$col] ?? null;
        continue;
      }
      $val = trim((string) ($_POST[$col] ?? ''));
      if (str_starts_with($ty, 'select:')) {
        [, $rt, $rk] = explode(':', $ty);
        $val = $val !== '' && val("SELECT 1 FROM $rt WHERE $rk=?", [$val]) ? (int) $val : '';
        if ($val === '' && $req)
          $err[] = "$lab is required.";
        $val = $val === '' ? null : $val;
      } elseif ($ty === 'number') {
        if (!ctype_digit($val) || (int) $val < 1900 || (int) $val > 2100)
          $err[] = "$lab must be a number between 1900 and 2100.";
      } else {
        if ($req && $val === '')
          $err[] = "$lab is required.";
        if (mb_strlen($val) > ($ty === 'textarea' ? 3000 : 150))
          $err[] = "$lab is too long.";
        if ($val === '')
          $val = null;
      }
      $d[$col] = $val;
    }
    if (!$err) {
      try {
        if ($old && $tk !== 'years') {
          q("UPDATE $table SET " . implode(',', array_map(fn($c) => "$c=?", array_keys($d))) . " WHERE $pk=?", array_merge(array_values($d), [$id]));
          foreach ($fields as $f)
            if ($f[2] === 'file' && $d[$f[0]] !== $old[$f[0]])
              delete_upload($old[$f[0]]);
        } elseif (!$old)
          q("INSERT INTO $table (" . implode(',', array_keys($d)) . ") VALUES (" . implode(',', array_fill(0, count($d), '?')) . ")", array_values($d));
        flash('success', "$label saved.");
        redirect($self);
      } catch (PDOException $ex) {
        foreach ($newFiles as $nf)
          delete_upload($nf);
        $err[] = ($ex->errorInfo[1] ?? 0) == 1062 ? "That $label already exists." : "Could not save $label.";
      }
    } else
      foreach ($newFiles as $nf)
        delete_upload($nf);
    foreach ($err as $x)
      flash('danger', $x);
    $_SESSION['form_old'] = $_POST;
    redirect($self . ($id ? '&edit=' . $id : ''));
  }
}
$title = 'Manage ' . strtolower($label) . 's';
require __DIR__ . '/_top.php';
$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
$row = $editId ? one("SELECT * FROM $table WHERE $pk=?", [$editId]) : [];
if (!empty($_SESSION['form_old'])) {
  $row = array_merge($row ?: [], $_SESSION['form_old']);
  unset($_SESSION['form_old']);
}
$list = all($listSql);
?>
<div class="page-head">
  <h1 class="h3"><?= e($label) ?>s <small class="text-muted fs-6">(<?= count($list) ?>)</small></h1>
</div>
<div class="row g-4">
  <div class="col-xl-4">
    <form method="post" enctype="multipart/form-data" class="panel vstack gap-3"><?= csrf_field() ?><input type="hidden"
        name="do" value="save"><input type="hidden" name="t" value="<?= e($tk) ?>"><input type="hidden" name="id"
        value="<?= $editId ?>">
      <h2 class="h5 mb-0"><?= $editId ? 'Edit' : 'Add' ?> <?= e(strtolower($label)) ?></h2>
      <?php foreach ($fields as [$col, $lab, $ty, $req]): ?>
        <div><label class="form-label"><?= e($lab) ?><?= $req ? ' *' : '' ?></label>
          <?php if ($ty === 'textarea'): ?><textarea name="<?= $col ?>" rows="4" class="form-control"
              maxlength="3000"><?= e($row[$col] ?? '') ?></textarea>
          <?php elseif ($ty === 'file'): ?><input type="file" name="<?= $col ?>" accept="image/jpeg,image/png,image/webp"
              class="form-control"><?php if (!empty($row[$col])): ?>
              <div class="form-text">Current image kept unless you upload a new one.</div><?php endif; ?>
          <?php elseif (str_starts_with($ty, 'select:')):
            [, $rt, $rk, $rl] = explode(':', $ty); ?><select
              name="<?= $col ?>" class="form-select" <?= $req ? 'required' : '' ?>>
              <option value="">— <?= $req ? 'choose' : 'none' ?> —</option>
              <?php foreach (all("SELECT $rk k, $rl l FROM $rt ORDER BY " . ($rt === 'years' ? 'year DESC' : $rl)) as $o): ?>
                <option value="<?= e($o['k']) ?>" <?= (string) ($row[$col] ?? '') === (string) $o['k'] ? 'selected' : '' ?>>
                  <?= e($o['l']) ?></option><?php endforeach; ?>
            </select>
          <?php else: ?><input name="<?= $col ?>" type="<?= $ty === 'number' ? 'number' : 'text' ?>" class="form-control"
              value="<?= e($row[$col] ?? '') ?>" <?= $ty === 'number' ? 'min="1900" max="2100"' : 'maxlength="150"' ?>     <?= $req ? 'required' : '' ?>     <?= $tk === 'years' && $editId ? 'readonly' : '' ?>><?php endif; ?>
        </div><?php endforeach; ?>
      <div class="d-flex gap-2"><button class="btn btn-sound">Save</button><?php if ($editId): ?><a
            href="<?= e(url($self)) ?>" class="btn btn-outline-light">Cancel</a><?php endif; ?></div>
    </form>
  </div>
  <div class="col-xl-8">
    <div class="table-wrap">
      <table class="table align-middle mb-0">
        <thead>
          <tr>
            <th><?= e($label) ?></th>
            <th>Used by</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($list as $r): ?>
            <tr>
              <td>
                <div class="d-flex align-items-center gap-2"><?php if ($tk === 'artists' || $tk === 'albums'): ?>
                    <div class="thumb-sm">
                      <?= cover_html(['id' => $r['id'], 'title' => $r['label'], $tk === 'artists' ? 'image' : 'cover' => $r['img']], $tk === 'artists' ? 'artist' : 'music') ?>
                    </div><?php endif; ?>
                  <div>
                    <div class="fw-semibold"><?= e($r['label']) ?></div><?php if ($r['sub']): ?>
                      <div class="small text-muted"><?= e($r['sub']) ?></div><?php endif; ?>
                  </div>
                </div>
              </td>
              <td><?= (int) $r['uses'] ?></td>
              <td class="text-end text-nowrap"><?php if ($tk !== 'years'): ?><a class="btn btn-sm btn-outline-light"
                    href="?t=<?= e($tk) ?>&edit=<?= (int) $r['id'] ?>"><i class="bi bi-pencil"></i></a> <?php endif; ?>
                <form method="post" class="d-inline"><?= csrf_field() ?><input type="hidden" name="do"
                    value="delete"><input type="hidden" name="t" value="<?= e($tk) ?>"><input type="hidden" name="id"
                    value="<?= (int) $r['id'] ?>"><button class="btn btn-sm btn-outline-danger"
                    data-confirm="Delete this <?= e(strtolower($label)) ?>?"><i class="bi bi-trash"></i></button></form>
              </td>
            </tr>
          <?php endforeach;
          if (!$list)
            echo '<tr><td colspan="3" class="text-center text-muted py-4">Nothing here yet.</td></tr>'; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php require __DIR__ . '/_bottom.php'; ?>