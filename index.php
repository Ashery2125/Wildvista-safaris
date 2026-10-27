<?php
require_once __DIR__ . '/app/core/bootstrap.php';

$title = 'Explore Arusha';

/*
|--------------------------------------------------------------------------
| Featured destinations
|--------------------------------------------------------------------------
*/
$dest = query("
    SELECT *
    FROM destinations
    WHERE status = 'active'
    ORDER BY id ASC
    LIMIT 6
")->fetch_all(MYSQLI_ASSOC);


/*
|--------------------------------------------------------------------------
| Featured tours
|--------------------------------------------------------------------------
*/
$tours = query("
    SELECT
        t.*,
        d.name AS destination
    FROM tours t
    LEFT JOIN destinations d
        ON d.id = t.destination_id
    WHERE t.status = 'active'
    ORDER BY
        t.featured DESC,
        t.id DESC
    LIMIT 6
")->fetch_all(MYSQLI_ASSOC);


/*
|--------------------------------------------------------------------------
| Accommodation
|--------------------------------------------------------------------------
*/
$stays = query("
    SELECT *
    FROM accommodations
    WHERE status = 'active'
    ORDER BY id DESC
    LIMIT 3
")->fetch_all(MYSQLI_ASSOC);


/*
|--------------------------------------------------------------------------
| Hero images
|
| IMPORTANT:
| Replace these JPG files later with your own licensed photographs.
| Do not change the filenames unless you also change them here.
|--------------------------------------------------------------------------
*/
$heroImages = [
    '01-arusha.jpg',
    '02-tarangire.jpg',
    '03-kilimanjaro.jpg',
    '04-manyara.jpg',
    '05-ngorongoro.jpg',
    '06-serengeti.jpg',
    '07-zanzibar.jpg'
];


require __DIR__ . '/app/views/header.php';
?>


<!-- =========================================================
     WILDVISTA HERO
     ========================================================= -->

<section
    class="hero hero-slideshow"
    data-hero-slider
    aria-label="Wildvista destinations"
>


    <!-- =====================================================
         HERO IMAGE LAYER
         ===================================================== -->

    <div
        class="hero-slides"
        aria-hidden="true"
    >

        <?php foreach ($heroImages as $index => $image): ?>

            <div
                class="hero-slide <?= $index === 0 ? 'active' : '' ?>"
                data-slide="<?= $index ?>"
                style="
                    --hero-image:
                    url('<?= e(url('assets/img/hero/' . $image)) ?>');
                "
            ></div>

        <?php endforeach; ?>

    </div>


    <!-- =====================================================
         HERO DARK OVERLAY
         ===================================================== -->

    <div class="hero-shade"></div>


    <!-- =====================================================
         HERO CONTENT
         ===================================================== -->

    <div class="hero-inner">


        <div class="eyebrow">
            ARUSHA · TANZANIA
        </div>


        <h1>
            Explore
            <span>Arusha</span>
        </h1>


        <p>
            Wild landscapes, unforgettable journeys and
            experiences built around the way you want to travel.
        </p>


        <!-- =================================================
             SEARCH / DISCOVERY
             ================================================= -->

        <form
            class="searchbox"
            action="<?= e(url('search.php')) ?>"
            method="get"
            data-loader
        >

            <div class="search-field">

                <label for="hero-search">
                    Experience
                </label>

                <input
                    id="hero-search"
                    type="text"
                    name="q"
                    placeholder="Safari, mountain, hotel..."
                    autocomplete="off"
                >

            </div>


            <div class="search-field">

                <label for="hero-date">
                    Date
                </label>

                <input
                    id="hero-date"
                    type="date"
                    name="date"
                >

            </div>


            <div class="search-field">

                <label for="hero-guests">
                    Guests
                </label>

                <select
                    id="hero-guests"
                    name="guests"
                >

                    <option value="1">
                        1 Guest
                    </option>

                    <option
                        value="2"
                        selected
                    >
                        2 Guests
                    </option>

                    <option value="3">
                        3 Guests
                    </option>

                    <option value="4">
                        4 Guests
                    </option>

                    <option value="5">
                        5 Guests
                    </option>

                    <option value="6">
                        6 Guests
                    </option>

                    <option value="7">
                        7 Guests
                    </option>

                    <option value="8">
                        8 Guests
                    </option>

                    <option value="9">
                        9 Guests
                    </option>

                    <option value="10">
                        10 Guests
                    </option>

                </select>

            </div>


            <button
                type="submit"
                class="search-submit"
            >
                Search
            </button>

        </form>


    </div>


    <!-- =====================================================
         HERO SLIDESHOW DOTS
         ===================================================== -->

    <div
        class="hero-dots"
        aria-label="Hero image navigation"
    >

        <?php foreach ($heroImages as $index => $image): ?>

            <button
                type="button"
                class="hero-dot <?= $index === 0 ? 'active' : '' ?>"
                data-slide-to="<?= $index ?>"
                aria-label="Show destination image <?= $index + 1 ?>"
            ></button>

        <?php endforeach; ?>

    </div>


</section>



<!-- =========================================================
     EXPLORE ARUSHA
     ========================================================= -->

<section
    class="section"
    id="explore"
>

    <div class="section-head">

        <div>

            <div
                class="eyebrow"
                style="color:var(--green)"
            >
                START HERE
            </div>

            <h2>
                Explore Arusha
            </h2>

            <p class="muted">
                Build your experience around the places,
                activities and services you actually need.
            </p>

        </div>


        <a
            class="btn btn-dark"
            href="<?= e(url('tours.php')) ?>"
        >
            View all tours →
        </a>

    </div>


    <div class="tiles">


        <!-- Tours -->

        <a
            class="tile"
            href="<?= e(url('tours.php')) ?>"
        >

            <img
                src="<?= e(url('assets/img/safari-card.jpg')) ?>"
                alt="Safari tours in Tanzania"
                loading="lazy"
            >

            <div class="tile-content">

                <h3>
                    Tours & Safaris
                </h3>

                <span>
                    Adventure awaits
                </span>

            </div>

        </a>


        <!-- Accommodation -->

        <a
            class="tile"
            href="<?= e(url('accommodation.php')) ?>"
        >

            <img
                src="<?= e(url('assets/img/stay-card.jpg')) ?>"
                alt="Accommodation in Arusha"
                loading="lazy"
            >

            <div class="tile-content">

                <h3>
                    Accommodation
                </h3>

                <span>
                    Stay in comfort
                </span>

            </div>

        </a>


        <!-- Transport -->

        <a
            class="tile"
            href="<?= e(url('transport.php')) ?>"
        >

            <img
                src="<?= e(url('assets/img/transport-card.jpg')) ?>"
                alt="Safari transport"
                loading="lazy"
            >

            <div class="tile-content">

                <h3>
                    Transport
                </h3>

                <span>
                    Travel with ease
                </span>

            </div>

        </a>


        <!-- Activities -->

        <a
            class="tile"
            href="<?= e(url('activities.php')) ?>"
        >

            <img
                src="<?= e(url('assets/img/activity-card.jpg')) ?>"
                alt="Activities in Tanzania"
                loading="lazy"
            >

            <div class="tile-content">

                <h3>
                    Activities
                </h3>

                <span>
                    Make the day yours
                </span>

            </div>

        </a>


        <!-- Destinations -->

        <a
            class="tile"
            href="#destinations"
        >

            <img
                src="<?= e(url('assets/img/mountain-card.jpg')) ?>"
                alt="Tanzania destinations"
                loading="lazy"
            >

            <div class="tile-content">

                <h3>
                    Destinations
                </h3>

                <span>
                    Go beyond the usual
                </span>

            </div>

        </a>


    </div>

</section>



<!-- =========================================================
     FEATURED TOURS
     ========================================================= -->

<section class="section">

    <div class="section-head">

        <div>

            <div
                class="eyebrow"
                style="color:var(--green)"
            >
                HANDPICKED EXPERIENCES
            </div>

            <h2>
                Featured tours
            </h2>

        </div>


        <a href="<?= e(url('tours.php')) ?>">
            See all →
        </a>

    </div>


    <div class="grid">

        <?php if (!$tours): ?>

            <div class="empty-state">

                <h3>
                    Tours are being prepared
                </h3>

                <p class="muted">
                    Check back soon for new Wildvista experiences.
                </p>

            </div>

        <?php endif; ?>


        <?php foreach ($tours as $t): ?>

            <article class="card">


                <a
                    href="<?= e(url('tour.php?id=' . (int)$t['id'])) ?>"
                >

                    <div class="card-img">

                        <img
                            src="<?= e(media_url($t['image'])) ?>"
                            alt="<?= e($t['title']) ?>"
                            loading="lazy"
                        >

                    </div>

                </a>


                <div class="card-body">


                    <div class="meta">

                        <span>
                            ◷
                            <?= (int)$t['duration_days'] ?>
                            day<?= ((int)$t['duration_days'] !== 1 ? 's' : '') ?>
                        </span>


                        <?php if (!empty($t['destination'])): ?>

                            <span>
                                ●
                                <?= e($t['destination']) ?>
                            </span>

                        <?php endif; ?>

                    </div>


                    <h3>
                        <?= e($t['title']) ?>
                    </h3>


                    <?php if (!empty($t['short_description'])): ?>

                        <p class="muted">
                            <?= e($t['short_description']) ?>
                        </p>

                    <?php endif; ?>


                    <div class="row-between">


                        <span class="price">

                            From

                            <?= money((float)$t['base_price']) ?>

                        </span>


                        <a
                            class="btn btn-dark"
                            href="<?= e(url('tour.php?id=' . (int)$t['id'])) ?>"
                        >
                            Explore
                        </a>


                    </div>


                </div>

            </article>

        <?php endforeach; ?>


    </div>

</section>



<!-- =========================================================
     DESTINATIONS
     ========================================================= -->

<section
    class="section"
    id="destinations"
>

    <div class="section-head">

        <div>

            <div
                class="eyebrow"
                style="color:var(--green)"
            >
                LOCAL PLACES
            </div>

            <h2>
                Go beyond the usual
            </h2>

            <p class="muted">
                Explore destinations across Tanzania.
                Wildvista starts in Arusha and can grow from there.
            </p>

        </div>

    </div>


    <div class="grid">


        <?php if (!$dest): ?>

            <div class="empty-state">

                <h3>
                    Destinations are being prepared
                </h3>

            </div>

        <?php endif; ?>


        <?php foreach ($dest as $d): ?>

            <article class="card">


                <div class="card-img">

                    <img
                        src="<?= e(media_url($d['image'])) ?>"
                        alt="<?= e($d['name']) ?>"
                        loading="lazy"
                    >

                </div>


                <div class="card-body">


                    <div class="meta">

                        <?php if (!empty($d['area'])): ?>

                            <span>
                                <?= e($d['area']) ?>
                            </span>

                        <?php endif; ?>


                        <span class="tag">
                            Active
                        </span>

                    </div>


                    <h3>
                        <?= e($d['name']) ?>
                    </h3>


                    <?php if (!empty($d['description'])): ?>

                        <p class="muted">
                            <?= e($d['description']) ?>
                        </p>

                    <?php endif; ?>


                </div>

            </article>

        <?php endforeach; ?>


    </div>

</section>



<!-- =========================================================
     ACCOMMODATION
     ========================================================= -->

<section class="section">

    <div class="section-head">

        <div>

            <div
                class="eyebrow"
                style="color:var(--green)"
            >
                STAY YOUR WAY
            </div>

            <h2>
                Popular accommodation
            </h2>

        </div>


        <a href="<?= e(url('accommodation.php')) ?>">
            View all →
        </a>

    </div>


    <div class="grid">


        <?php if (!$stays): ?>

            <div class="empty-state">

                <h3>
                    Accommodation is being prepared
                </h3>

                <p class="muted">
                    New places to stay will appear here.
                </p>

            </div>

        <?php endif; ?>


        <?php foreach ($stays as $s): ?>

            <article class="card">


                <div class="card-img">

                    <img
                        src="<?= e(media_url($s['image'])) ?>"
                        alt="<?= e($s['name']) ?>"
                        loading="lazy"
                    >

                </div>


                <div class="card-body">


                    <div class="row-between">

                        <span class="tag">
                            Available
                        </span>


                        <?php if (!empty($s['type'])): ?>

                            <span class="muted">
                                <?= e($s['type']) ?>
                            </span>

                        <?php endif; ?>


                    </div>


                    <h3>
                        <?= e($s['name']) ?>
                    </h3>


                    <p class="muted">

                        <?= e($s['location']) ?>

                        <?php if (!empty($s['max_guests'])): ?>

                            · Up to
                            <?= (int)$s['max_guests'] ?>
                            guests

                        <?php endif; ?>

                    </p>


                    <div class="row-between">


                        <span class="price">

                            <?= money((float)$s['price_per_night']) ?>

                            <small>
                                / night
                            </small>

                        </span>


                        <a
                            href="<?= e(url('accommodation.php')) ?>"
                        >
                            Details →
                        </a>


                    </div>


                </div>

            </article>

        <?php endforeach; ?>


    </div>

</section>



<!-- =========================================================
     TRIP BUILDER CTA
     ========================================================= -->

<section class="section">

    <div class="cta">

        <div class="eyebrow">
            BUILD YOUR TRIP
        </div>


        <h2>
            Choose the experience.
            Add a stay.
            Add transport.
        </h2>


        <p>
            Wildvista lets you combine the services you need
            while keeping accommodation and transport optional.
        </p>


        <a
            class="btn"
            href="<?= e(url('tours.php')) ?>"
        >
            Start exploring →
        </a>

    </div>

</section>


<?php
require __DIR__ . '/app/views/footer.php';
?>