(function () {
    'use strict';


    /* =========================================================
       WILDVISTA GLOBAL
       ========================================================= */

    window.Wildvista = {

        formatMoney: function (n) {

            return 'USD ' + Number(n || 0).toLocaleString(
                undefined,
                {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }
            );

        }

    };


    /* =========================================================
       DOM READY
       ========================================================= */

    document.addEventListener('DOMContentLoaded', function () {


        /* =====================================================
           1. TOAST / FLASH MESSAGES
           ===================================================== */

        document
            .querySelectorAll('.toast')
            .forEach(function (toast) {

                setTimeout(function () {

                    toast.style.opacity = '0';
                    toast.style.transform = 'translateY(-8px)';

                    setTimeout(function () {
                        toast.remove();
                    }, 350);

                }, 4500);

            });


        /* =====================================================
           2. PUBLIC MOBILE MENU
           ===================================================== */

        const menuButton =
            document.querySelector('.hamb');

        const navLinks =
            document.querySelector('.navlinks');


        if (menuButton && navLinks) {

            menuButton.addEventListener(
                'click',
                function () {

                    const isOpen =
                        navLinks.classList.contains('mobile-open');


                    if (isOpen) {

                        navLinks.classList.remove(
                            'mobile-open'
                        );

                        navLinks.style.display = '';

                        menuButton.setAttribute(
                            'aria-expanded',
                            'false'
                        );

                    } else {

                        navLinks.classList.add(
                            'mobile-open'
                        );

                        navLinks.style.display = 'flex';
                        navLinks.style.position = 'absolute';
                        navLinks.style.top = '100%';
                        navLinks.style.left = '0';
                        navLinks.style.right = '0';
                        navLinks.style.padding = '18px 20px';
                        navLinks.style.background =
                            'var(--cream, #f5f1e8)';
                        navLinks.style.flexDirection =
                            'column';
                        navLinks.style.alignItems =
                            'stretch';
                        navLinks.style.gap = '4px';
                        navLinks.style.boxShadow =
                            '0 18px 35px rgba(0,0,0,.12)';

                        menuButton.setAttribute(
                            'aria-expanded',
                            'true'
                        );

                    }

                }
            );


            /* Close mobile menu after selecting a link */

            navLinks
                .querySelectorAll('a')
                .forEach(function (link) {

                    link.addEventListener(
                        'click',
                        function () {

                            navLinks.classList.remove(
                                'mobile-open'
                            );

                            navLinks.style.display = '';

                            menuButton.setAttribute(
                                'aria-expanded',
                                'false'
                            );

                        }
                    );

                });


            /* Close menu when clicking outside */

            document.addEventListener(
                'click',
                function (event) {

                    if (
                        !navLinks.contains(event.target) &&
                        !menuButton.contains(event.target)
                    ) {

                        navLinks.classList.remove(
                            'mobile-open'
                        );

                        navLinks.style.display = '';

                        menuButton.setAttribute(
                            'aria-expanded',
                            'false'
                        );

                    }

                }
            );

        }


        /* =====================================================
           3. PORTAL / ADMIN MOBILE SIDEBAR
           ===================================================== */

        const portalMenu =
            document.querySelector('.mobile-menu');

        const portalSide =
            document.querySelector('.portal-side');


        if (portalMenu && portalSide) {

            portalMenu.addEventListener(
                'click',
                function () {

                    portalSide.classList.toggle(
                        'open'
                    );

                }
            );


            /* Close portal sidebar when clicking outside */

            document.addEventListener(
                'click',
                function (event) {

                    if (
                        portalSide.classList.contains('open') &&
                        !portalSide.contains(event.target) &&
                        !portalMenu.contains(event.target)
                    ) {

                        portalSide.classList.remove(
                            'open'
                        );

                    }

                }
            );

        }


        /* =====================================================
           4. WILDVISTA HERO SLIDESHOW
           ===================================================== */

        const hero =
            document.querySelector(
                '[data-hero-slider]'
            );


        if (hero) {

            const slides =
                Array.from(
                    hero.querySelectorAll(
                        '.hero-slide'
                    )
                );


            const dots =
                Array.from(
                    hero.querySelectorAll(
                        '.hero-dot'
                    )
                );


            if (slides.length > 0) {

                let currentSlide = 0;

                let timer = null;

                let isPaused = false;


                /* ---------------------------------------------
                   Make sure first slide is active
                   --------------------------------------------- */

                slides.forEach(
                    function (slide, index) {

                        slide.classList.toggle(
                            'active',
                            index === 0
                        );

                    }
                );


                dots.forEach(
                    function (dot, index) {

                        dot.classList.toggle(
                            'active',
                            index === 0
                        );

                    }
                );


                /* ---------------------------------------------
                   Preload hero images
                   --------------------------------------------- */

                slides.forEach(
                    function (slide) {

                        const style =
                            window.getComputedStyle(
                                slide
                            );

                        const background =
                            style.backgroundImage;


                        if (
                            background &&
                            background !== 'none'
                        ) {

                            const match =
                                background.match(
                                    /url\(["']?(.*?)["']?\)/
                                );


                            if (
                                match &&
                                match[1]
                            ) {

                                const image =
                                    new Image();

                                image.src =
                                    match[1];

                            }

                        }

                    }
                );


                /* ---------------------------------------------
                   Show selected slide
                   --------------------------------------------- */

                function showSlide(index) {

                    if (!slides.length) {
                        return;
                    }


                    if (
                        index < 0
                    ) {
                        index =
                            slides.length - 1;
                    }


                    if (
                        index >= slides.length
                    ) {
                        index = 0;
                    }


                    slides.forEach(
                        function (slide, i) {

                            slide.classList.toggle(
                                'active',
                                i === index
                            );

                        }
                    );


                    dots.forEach(
                        function (dot, i) {

                            dot.classList.toggle(
                                'active',
                                i === index
                            );

                        }
                    );


                    currentSlide = index;

                }


                /* ---------------------------------------------
                   Next slide
                   --------------------------------------------- */

                function nextSlide() {

                    showSlide(
                        (currentSlide + 1) %
                        slides.length
                    );

                }


                /* ---------------------------------------------
                   Start automatic slideshow
                   --------------------------------------------- */

                function startSlider() {

                    stopSlider();


                    timer =
                        window.setInterval(
                            function () {

                                if (!isPaused) {
                                    nextSlide();
                                }

                            },
                            6500
                        );

                }


                /* ---------------------------------------------
                   Stop slideshow
                   --------------------------------------------- */

                function stopSlider() {

                    if (timer !== null) {

                        window.clearInterval(
                            timer
                        );

                        timer = null;

                    }

                }


                /* ---------------------------------------------
                   Dot navigation
                   --------------------------------------------- */

                dots.forEach(
                    function (dot, index) {

                        dot.addEventListener(
                            'click',
                            function () {

                                showSlide(index);

                                startSlider();

                            }
                        );

                    }
                );


                /* ---------------------------------------------
                   Pause while user is touching hero
                   Mobile friendly
                   --------------------------------------------- */

                hero.addEventListener(
                    'mouseenter',
                    function () {

                        isPaused = true;

                    }
                );


                hero.addEventListener(
                    'mouseleave',
                    function () {

                        isPaused = false;

                    }
                );


                hero.addEventListener(
                    'touchstart',
                    function () {

                        isPaused = true;

                    },
                    {
                        passive: true
                    }
                );


                hero.addEventListener(
                    'touchend',
                    function () {

                        window.setTimeout(
                            function () {
                                isPaused = false;
                            },
                            1500
                        );

                    },
                    {
                        passive: true
                    }
                );


                /* ---------------------------------------------
                   Keyboard controls
                   --------------------------------------------- */

                document.addEventListener(
                    'keydown',
                    function (event) {

                        if (
                            !hero.matches(':hover') &&
                            document.activeElement &&
                            (
                                document.activeElement.tagName === 'INPUT' ||
                                document.activeElement.tagName === 'SELECT' ||
                                document.activeElement.tagName === 'TEXTAREA'
                            )
                        ) {
                            return;
                        }


                        if (event.key === 'ArrowRight') {

                            showSlide(
                                (currentSlide + 1) %
                                slides.length
                            );

                            startSlider();

                        }


                        if (event.key === 'ArrowLeft') {

                            showSlide(
                                (currentSlide - 1 +
                                    slides.length) %
                                slides.length
                            );

                            startSlider();

                        }

                    }
                );


                /* ---------------------------------------------
                   Respect reduced-motion accessibility
                   --------------------------------------------- */

                const reducedMotion =
                    window.matchMedia &&
                    window.matchMedia(
                        '(prefers-reduced-motion: reduce)'
                    ).matches;


                if (!reducedMotion) {

                    startSlider();

                }


                /* ---------------------------------------------
                   Pause when browser tab is hidden
                   --------------------------------------------- */

                document.addEventListener(
                    'visibilitychange',
                    function () {

                        if (
                            document.hidden
                        ) {

                            stopSlider();

                        } else if (
                            !reducedMotion
                        ) {

                            startSlider();

                        }

                    }
                );

            }

        }


        /* =====================================================
           5. FORM LOADING STATE
           ===================================================== */

        document
            .querySelectorAll('[data-loader]')
            .forEach(function (form) {

                form.addEventListener(
                    'submit',
                    function () {

                        const button =
                            form.querySelector(
                                'button[type="submit"]'
                            );


                        if (!button) {
                            return;
                        }


                        if (
                            form.dataset.submitting === '1'
                        ) {
                            return;
                        }


                        form.dataset.submitting =
                            '1';


                        button.disabled = true;


                        button.innerHTML =
                            '<span class="loader" ' +
                            'style="' +
                            'display:inline-block;' +
                            'width:18px;' +
                            'height:18px;' +
                            'border-width:2px;' +
                            'vertical-align:middle;' +
                            'margin-right:8px' +
                            '">' +
                            '</span>' +
                            'Please wait';

                    }
                );

            });


        /* =====================================================
           6. TRIP BUILDER TOTAL
           ===================================================== */

        document
            .querySelectorAll('[data-builder]')
            .forEach(function (box) {


                const totalElement =
                    box.querySelector(
                        '[data-total]'
                    );


                const items =
                    box.querySelectorAll(
                        '[data-price]'
                    );


                const hiddenTotal =
                    box.querySelector(
                        '[name="calculated_total"]'
                    );


                function calculateTotal() {

                    let total =
                        Number(
                            box.dataset.base || 0
                        );


                    items.forEach(
                        function (item) {

                            const input =
                                item.querySelector(
                                    'input, select'
                                );


                            if (!input) {
                                return;
                            }


                            const price =
                                Number(
                                    item.dataset.price || 0
                                );


                            if (
                                input.type === 'checkbox'
                            ) {

                                if (
                                    input.checked
                                ) {

                                    total += price;

                                }

                                return;

                            }


                            if (
                                input.tagName === 'SELECT'
                            ) {

                                if (
                                    input.value !== ''
                                ) {

                                    total += price;

                                }

                            }

                        }
                    );


                    if (totalElement) {

                        totalElement.textContent =
                            Wildvista.formatMoney(
                                total
                            );

                    }


                    if (hiddenTotal) {

                        hiddenTotal.value =
                            total.toFixed(2);

                    }

                }


                items.forEach(
                    function (item) {

                        item.addEventListener(
                            'change',
                            calculateTotal
                        );


                        const input =
                            item.querySelector(
                                'input, select'
                            );


                        if (input) {

                            input.addEventListener(
                                'change',
                                calculateTotal
                            );

                        }

                    }
                );


                calculateTotal();

            });


        /* =====================================================
           7. IMAGE ERROR FALLBACK
           ===================================================== */

        document
            .querySelectorAll('img')
            .forEach(function (image) {

                image.addEventListener(
                    'error',
                    function () {

                        image.classList.add(
                            'image-error'
                        );

                    }
                );

            });


        /* =====================================================
           8. SMOOTH ANCHOR SCROLL
           ===================================================== */

        document
            .querySelectorAll(
                'a[href^="#"]'
            )
            .forEach(function (link) {

                link.addEventListener(
                    'click',
                    function (event) {

                        const targetId =
                            link.getAttribute(
                                'href'
                            );


                        if (
                            !targetId ||
                            targetId === '#'
                        ) {
                            return;
                        }


                        const target =
                            document.querySelector(
                                targetId
                            );


                        if (!target) {
                            return;
                        }


                        event.preventDefault();


                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });

                    }
                );

            });


        /* =====================================================
           9. PREVENT DOUBLE CLICK ON IMPORTANT BUTTONS
           ===================================================== */

        document
            .querySelectorAll(
                '[data-single-submit]'
            )
            .forEach(function (button) {

                button.addEventListener(
                    'click',
                    function () {

                        if (
                            button.dataset.locked === '1'
                        ) {

                            return;

                        }


                        button.dataset.locked =
                            '1';


                        window.setTimeout(
                            function () {

                                button.dataset.locked =
                                    '0';

                            },
                            4000
                        );

                    }
                );

            });

    });

})();