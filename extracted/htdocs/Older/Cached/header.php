<?php $u=current_user(); $title=$title ?? SITE_NAME; ?>
<!doctype html><html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=e($title)?> · <?=SITE_NAME?></title>
<link rel="icon" href="<?=url('assets/img/favicon.svg')?>">
<link rel="stylesheet" href="<?=url('assets/css/app.css')?>">
<script defer src="<?=url('assets/js/app.js')?>"></script>
</head><body>
<div class="site-noise"></div>
<header class="nav"><a class="brand" href="<?=url('index.php')?>"><img src="<?=url('assets/img/wildvista-mark.png')?>" alt="Wildvista"></a>
<nav class="navlinks"><a href="<?=url('index.php#explore')?>">Explore</a><a href="<?=url('tours.php')?>">Tours</a><a href="<?=url('accommodation.php')?>">Stay</a><a href="<?=url('transport.php')?>">Transport</a><a href="<?=url('activities.php')?>">Activities</a><a href="<?=url('index.php#destinations')?>">Destinations</a></nav>
<div class="nav-actions"><a class="iconbtn" href="<?=url('search.php')?>">⌕</a><?php if($u): ?><a class="pill" href="<?=url($u['role']==='admin'?'admin/index.php':($u['role']==='operator'?'operator/index.php':'customer/index.php'))?>">Dashboard</a><?php else: ?><a class="pill" href="<?=url('login.php')?>">Login / Sign up</a><?php endif; ?></div>
<button class="hamb" type="button" aria-label="Menu">☰</button></header>
<?php foreach(flashes() as $f): ?><div class="toast <?=e($f[0])?>"><?=e($f[1])?></div><?php endforeach; ?>
<main>
