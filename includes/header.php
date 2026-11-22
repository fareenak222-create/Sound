<?php
require_once __DIR__ . '/bootstrap.php';
$me = current_user();
$pageTitle = ($title ?? '') ? $title . ' · ' . setting('site_name', 'SOUND') : setting('site_name', 'SOUND') . ' – ' . setting('tagline');
$nav = ['index.php' => 'Home', 'music.php' => 'Music', 'videos.php' => 'Videos', 'browse.php' => 'Browse', 'about.php' => 'About', 'contact.php' => 'Contact'];
$_cur = basename($_SERVER['SCRIPT_NAME']);
?><!doctype html>
<html lang="en" data-bs-theme="dark">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="<?= e(url('assets/css/style.css')) ?>" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg sound-nav sticky-top">
  <div class="container">
    <a class="navbar-brand brand" href="<?= e(url('index.php')) ?>"><i class="bi bi-soundwave"></i> <?= e(setting('site_name', 'SOUND')) ?></a>
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-label="Menu"><i class="bi bi-list fs-2"></i></button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <?php foreach ($nav as $_href => $_label): ?><li class="nav-item"><a class="nav-link<?= $_cur === $_href ? ' active' : '' ?>" href="<?= e(url($_href)) ?>"><?= e($_label) ?></a></li><?php endforeach; ?>
      </ul>
      <form class="d-flex me-lg-3 my-2 my-lg-0 nav-search" action="<?= e(url('search.php')) ?>" method="get" role="search">
        <input class="form-control form-control-sm" type="search" name="q" placeholder="Search music & videos" aria-label="Search" maxlength="100">
        <button class="btn btn-sm btn-sound ms-1" aria-label="Search"><i class="bi bi-search"></i></button>
      </form>
      <div class="d-flex align-items-center gap-2">
      <?php if ($me): ?>
        <div class="dropdown">
          <button class="btn btn-sm btn-outline-light dropdown-toggle" data-bs-toggle="dropdown"><i class="bi bi-person-circle"></i> <?= e($me['user_id']) ?></button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="<?= e(url('profile.php')) ?>">My profile</a></li>
            <?php if ($me['role'] === 'admin'): ?><li><a class="dropdown-item" href="<?= e(url('admin/index.php')) ?>">Admin dashboard</a></li><?php endif; ?>
            <li><hr class="dropdown-divider"></li>
            <li><form method="post" action="<?= e(url('logout.php')) ?>"><?= csrf_field() ?><button class="dropdown-item">Log out</button></form></li>
          </ul>
        </div>
      <?php else: ?>
        <a class="btn btn-sm btn-outline-light" href="<?= e(url('login.php')) ?>">Log in</a>
        <a class="btn btn-sm btn-sound" href="<?= e(url('register.php')) ?>">Sign up</a>
      <?php endif; ?>
      </div>
    </div>
  </div>
</nav>
<main class="container py-4 main-wrap">
<?= flash_render() ?>
