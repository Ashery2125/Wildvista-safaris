<?php

$u = current_user();

$title = $title ?? SITE_NAME;

?>
<!doctype html>

<html lang="en">

<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        <?= e($title) ?> · <?= SITE_NAME ?>
    </title>


    <link
        rel="icon"
        href="<?= url('assets/img/favicon.svg') ?>"
    >


    <link
        rel="stylesheet"
        href="<?= url('assets/css/app.css') ?>"
    >


    <script
        defer
        src="<?= url('assets/js/app.js') ?>"
    ></script>

</head>


<body>

<div class="site-noise"></div>


<header class="nav">


    <!-- =====================================================
         WILDVISTA ORIGINAL LOGO
         ===================================================== -->

    <a
        class="brand"
        href="<?= url('index.php') ?>"
        aria-label="Wildvista Safaris"
    >

        <img
            src="<?= url('assets/img/wildvista-mark.png') ?>"
            alt="Wildvista"
        >

    </a>



    <!-- =====================================================
         PUBLIC NAVIGATION
         ===================================================== -->

    <nav
        class="navlinks"
        aria-label="Main navigation"
    >

        <a href="<?= url('index.php') ?>">
            Home
        </a>


        <a href="<?= url('index.php#explore') ?>">
            Explore
        </a>


        <a href="<?= url('tours.php') ?>">
            Safaris
        </a>


        <a href="<?= url('accommodation.php') ?>">
            Stays
        </a>


        <a href="<?= url('transport.php') ?>">
            Transport
        </a>


        <a href="<?= url('activities.php') ?>">
            Activities
        </a>

    </nav>



    <!-- =====================================================
         PUBLIC ACTIONS
         
         IMPORTANT:
         Dashboard is NOT displayed here.
         Dashboard belongs inside the user's portal.
         ===================================================== -->

    <div class="nav-actions">

        <?php if (!$u): ?>

            <a href="<?= url('login.php') ?>">
                Login
            </a>

        <?php endif; ?>


        <a
            class="pill"
            href="<?= url('book.php') ?>"
        >
            Plan Your Trip
        </a>

    </div>



    <!-- =====================================================
         MOBILE MENU
         ===================================================== -->

    <button
        class="hamb"
        type="button"
        aria-label="Open menu"
        aria-expanded="false"
    >
        ☰
    </button>

</header>



<!-- =========================================================
     FLASH MESSAGES
     ========================================================= -->

<?php foreach (flashes() as $f): ?>

    <div class="toast <?= e($f[0]) ?>">

        <?= e($f[1]) ?>

    </div>

<?php endforeach; ?>



<main>