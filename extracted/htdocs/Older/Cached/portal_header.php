<?php
$u=current_user();
$title=$title ?? 'Dashboard';
$role=$u['role'] ?? 'customer';
$tab=$tab ?? '';
$nav = $role==='admin' ? [
 ['overview','Dashboard','admin/index.php?tab=overview'],['bookings','Bookings','admin/index.php?tab=bookings'],['payments','Payments','admin/index.php?tab=payments'],['listings','Listings','admin/index.php?tab=listings'],['users','Users','admin/index.php?tab=users'],['content','Content','admin/index.php?tab=content'],['settings','Settings','admin/index.php?tab=settings']
] : ($role==='operator' ? [
 ['overview','Dashboard','operator/index.php?tab=overview'],['tours','My Tours','operator/index.php?tab=tours'],['services','Accommodation & Transport','operator/index.php?tab=services'],['bookings','Bookings','operator/index.php?tab=bookings'],['profile','Profile','operator/index.php?tab=profile']
] : [
 ['overview','Dashboard','customer/index.php'],['bookings','My Bookings','customer/index.php'],['profile','Profile','customer/index.php?tab=profile']
]);
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=e($title)?> · <?=SITE_NAME?></title><link rel="icon" href="<?=url('assets/img/favicon.svg')?>"><link rel="stylesheet" href="<?=url('assets/css/app.css')?>"><script defer src="<?=url('assets/js/app.js')?>"></script></head><body class="portal-body">
<div class="portal-shell">
<aside class="portal-side">
 <a class="portal-brand" href="<?=url('index.php')?>"><img src="<?=url('assets/img/wildvista-mark.png')?>" alt="Wildvista"></a>
 <div class="portal-role"><span class="role-dot"></span><?=e(ucfirst($role))?> Portal</div>
 <nav class="portal-nav"><?php foreach($nav as $n): ?><a class="<?=($tab===$n[0] || ($role==='customer' && $n[0]==='overview' && !$tab))?'active':''?>" href="<?=url($n[2])?>"><span class="nav-icon">•</span><?=e($n[1])?></a><?php endforeach; ?></nav>
 <div class="portal-side-bottom"><a href="<?=url('index.php')?>">↗ View website</a><a href="<?=url('logout.php')?>">↪ Logout</a></div>
</aside>
<main class="portal-main">
 <header class="portal-top"><div class="portal-top-title"><span class="mobile-menu">☰</span><div><span class="eyebrow">Wildvista / <?=e(ucfirst($role))?></span><h1><?=e($title)?></h1></div></div><div class="portal-user"><div class="avatar"><?=e(strtoupper(substr($u['name']??'W',0,1)))?></div><div><strong><?=e($u['name']??'User')?></strong><small><?=e($u['email']??'')?></small></div></div></header>
<?php foreach(flashes() as $f): ?><div class="toast <?=e($f[0])?>"><?=e($f[1])?></div><?php endforeach; ?>
<div class="portal-content">
