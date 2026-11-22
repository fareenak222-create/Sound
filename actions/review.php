<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$me = require_login();
$type = ($_POST['type'] ?? '') === 'video' ? 'video' : 'music';
$id = (int)($_POST['id'] ?? 0);
$back = 'item.php?type=' . $type . '&id=' . $id;
require_post_csrf($back);
$t = tbl($type); $k = fk($type);
if (!val("SELECT 1 FROM $t WHERE id=?", [$id])) { flash('danger', 'Item not found.'); redirect('index.php'); }
$action = $_POST['action'] ?? '';
if ($action === 'delete') {
    q("DELETE FROM ratings WHERE user_id=? AND $k=?", [$me['id'], $id]);
    q("DELETE FROM reviews WHERE user_id=? AND $k=?", [$me['id'], $id]);
    flash('success', 'Your rating and review were deleted.');
} elseif ($action === 'save') {
    $rating = filter_var($_POST['rating'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]]);
    $comment = trim((string)($_POST['comment'] ?? ''));
    if ($rating === false || $rating === null) { flash('danger', 'Please choose a rating from 1 to 5 stars.'); redirect($back); }
    if (mb_strlen($comment) > 1000) { flash('danger', 'Review is too long (max 1000 characters).'); redirect($back); }
    q("INSERT INTO ratings (user_id, $k, rating) VALUES (?,?,?) ON DUPLICATE KEY UPDATE rating=VALUES(rating)", [$me['id'], $id, $rating]);
    if ($comment === '') q("DELETE FROM reviews WHERE user_id=? AND $k=?", [$me['id'], $id]);
    else q("INSERT INTO reviews (user_id, $k, comment) VALUES (?,?,?) ON DUPLICATE KEY UPDATE comment=VALUES(comment)", [$me['id'], $id, $comment]);
    flash('success', 'Thanks! Your rating' . ($comment !== '' ? ' and review were' : ' was') . ' saved.');
}
redirect($back);
