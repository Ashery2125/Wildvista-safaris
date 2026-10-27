<?php
$base = function_exists('base_url') ? base_url() : '';
?>

<footer class="footer">

    <!-- BRAND -->
    <div>

        <a
            href="<?= $base ?>/"
            class="brand footer-brand"
        >
            <img
                src="<?= $base ?>/assets/img/wildvista-mark.png"
                alt="Wildvista Safaris"
            >
        </a>

        <p>
            Discover Arusha your way — from unforgettable safaris
            and mountain experiences to stays, transport and
            carefully selected activities.
        </p>

        <p>
            <strong>Arusha, Tanzania</strong>
        </p>

    </div>


    <!-- EXPLORE -->
    <div>

        <h3>Explore</h3>

        <a href="<?= $base ?>/">
            Home
        </a>

        <a href="<?= $base ?>/search.php">
            Explore
        </a>

        <a href="<?= $base ?>/tours.php">
            Safaris
        </a>

        <a href="<?= $base ?>/activities.php">
            Activities
        </a>

    </div>


    <!-- TRAVEL -->
    <div>

        <h3>Travel</h3>

        <a href="<?= $base ?>/accommodations.php">
            Accommodation
        </a>

        <a href="<?= $base ?>/transport.php">
            Transport
        </a>

        <a href="<?= $base ?>/book.php">
            Plan Your Trip
        </a>

        <a href="<?= $base ?>/login.php">
            Customer Login
        </a>

    </div>


    <!-- CONTACT -->
    <div>

        <h3>Contact</h3>

        <p>
            <strong>Wildvista Safaris</strong>
        </p>

        <p>
            Arusha, Tanzania
        </p>

        <p>
            <a href="tel:+255621385106">
                +255 621 385 106
            </a>
        </p>

        <p>
            <a href="mailto:akilykaaya@gmail.com">
                akilykaaya@gmail.com
            </a>
        </p>

    </div>

</footer>


<div
    style="
        background:#041f19;
        color:#8fa89f;
        padding:18px 5vw;
        border-top:1px solid rgba(255,255,255,.06);
        font-size:12px;
        display:flex;
        justify-content:space-between;
        gap:15px;
        flex-wrap:wrap;
    "
>

    <span>
        © <?= date('Y') ?> Wildvista Safaris. All rights reserved.
    </span>

    <span>
        Explore Arusha Your Way
    </span>

</div>

</body>
</html>