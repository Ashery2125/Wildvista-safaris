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

    <!-- ORIGINAL WILDVISTA LOGO -->
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


    <!-- ONE NAVIGATION ONLY -->
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


    <!-- RIGHT ACTIONS -->
    <div class="nav-actions">

        <?php if ($u): ?>

            <a
                class="pill"
                href="<?=
                    url(
                        $u['role'] === 'admin'
                            ? 'admin/index.php'
                            : (
                                $u['role'] === 'operator'
                                    ? 'operator/index.php'
                                    : 'customer/index.php'
                            )
                    )
                ?>"
            >
                Dashboard
            </a>

        <?php else: ?>

            <a href="<?= url('login.php') ?>">
                Login
            </a>

            <a
                class="pill"
                href="<?= url('book.php') ?>"
            >
                Plan Your Trip
            </a>

        <?php endif; ?>

    </div>


    <!-- MOBILE BUTTON -->
    <button
        class="hamb"
        type="button"
        aria-label="Open menu"
        aria-expanded="false"
    >
        ☰
    </button>

</header>


<?php foreach (flashes() as $f): ?>

    <div class="toast <?= e($f[0]) ?>">
        <?= e($f[1]) ?>
    </div>

<?php endforeach; ?>


<main>