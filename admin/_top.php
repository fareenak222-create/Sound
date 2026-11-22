<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$admin = require_admin();
$title = $title ?? 'Admin';
require __DIR__ . '/../includes/header.php';
$cur = basename($_SERVER['SCRIPT_NAME']) . (isset($_GET['t']) ? '?t=' . $_GET['t'] : '');
$unread = (int)val('SELECT COUNT(*) FROM contact_messages WHERE is_read=0');
$links = [
 ['', 'Overview'], ['index.php', 'Dashboard', 'bi-speedometer2'],
 ['', 'Content'], ['music.php', 'Music', 'bi-music-note-beamed'], ['videos.php', 'Videos', 'bi-camera-video'],
 ['', 'Catalogue'], ['taxonomy.php?t=artists', 'Artists', 'bi-person-lines-fill'], ['taxonomy.php?t=albums', 'Albums', 'bi-disc'], ['taxonomy.php?t=genres', 'Genres', 'bi-tags'], ['taxonomy.php?t=years', 'Years', 'bi-calendar3'], ['taxonomy.php?t=languages', 'Languages', 'bi-translate'],
 ['', 'Community'], ['users.php', 'Users', 'bi-people'], ['reviews.php', 'Reviews', 'bi-chat-square-text'], ['messages.php', 'Messages' . ($unread ? " ($unread)" : ''), 'bi-envelope'],
 ['', 'Site'], ['settings.php', 'Settings', 'bi-gear'],
];
?>
<div class="admin-wrap">
<aside class="admin-side">
<?php foreach ($links as $l): if ($l[0] === ''): ?><div class="grp"><?= e($l[1]) ?></div><?php else: ?>
  <a href="<?= e(url('admin/' . $l[0])) ?>" class="<?= $cur === $l[0] ? 'active' : '' ?>"><i class="bi <?= e($l[2]) ?>"></i> <?= e($l[1]) ?></a>
<?php endif; endforeach; ?>
</aside>
<section style="min-width:0">
