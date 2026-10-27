<?php

require_once __DIR__ . '/../app/core/bootstrap.php';

$u = require_role(['admin']);


/*
|--------------------------------------------------------------------------
| ADMIN IMAGE UPLOAD
|--------------------------------------------------------------------------
*/

function admin_upload_image(string $field, string $fallback): string
{
    if (
        empty($_FILES[$field]['name']) ||
        ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK
    ) {
        return $fallback;
    }

    if (
        ($_FILES[$field]['size'] ?? 0) > 4 * 1024 * 1024
    ) {
        return $fallback;
    }

    $ext = strtolower(
        pathinfo(
            $_FILES[$field]['name'],
            PATHINFO_EXTENSION
        )
    );

    if (!in_array(
        $ext,
        ['jpg', 'jpeg', 'png', 'webp'],
        true
    )) {
        return $fallback;
    }

    $dir = __DIR__ . '/../assets/uploads';

    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }

    $name =
        'admin_' .
        date('YmdHis') .
        '_' .
        bin2hex(random_bytes(4)) .
        '.' .
        $ext;

    $destination = $dir . '/' . $name;

    if (
        move_uploaded_file(
            $_FILES[$field]['tmp_name'],
            $destination
        )
    ) {
        return 'uploads/' . $name;
    }

    return $fallback;
}


/*
|--------------------------------------------------------------------------
| TAB / ACTION
|--------------------------------------------------------------------------
*/

$tab = $_GET['tab'] ?? 'overview';

$action = $_POST['action'] ?? '';

$allowedTabs = [
    'overview',
    'bookings',
    'payments',
    'listings',
    'users',
    'content',
    'settings'
];

if (!in_array($tab, $allowedTabs, true)) {
    $tab = 'overview';
}


/*
|--------------------------------------------------------------------------
| POST ACTIONS
|--------------------------------------------------------------------------
*/

if (is_post()) {

    verify_csrf();


    /*
    |--------------------------------------------------------------------------
    | ACTIVATE USER
    |--------------------------------------------------------------------------
    */

    if ($action === 'approve_user') {

        $id = (int)($_POST['id'] ?? 0);

        if ($id > 0) {

            query(
                "UPDATE users SET status='active' WHERE id=?",
                'i',
                [$id]
            );

            safe_log(
                'activate_user',
                'user',
                $id
            );

            flash(
                'success',
                'User activated.'
            );
        }

        redirect(
            'admin/index.php?tab=users'
        );
        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | TOGGLE LISTING STATUS
    |--------------------------------------------------------------------------
    */

    if ($action === 'toggle_listing') {

        $table = $_POST['table'] ?? '';

        $id = (int)($_POST['id'] ?? 0);

        $allowedTables = [
            'tours',
            'accommodations',
            'transports',
            'activities',
            'destinations'
        ];

        if (
            in_array($table, $allowedTables, true) &&
            $id > 0
        ) {

            $newStatus =
                ($_POST['new_status'] ?? '') === 'active'
                    ? 'active'
                    : 'pending';

            query(
                "UPDATE {$table} SET status=? WHERE id=?",
                'si',
                [
                    $newStatus,
                    $id
                ]
            );

            safe_log(
                'listing_status',
                'listing',
                $id,
                [
                    'table' => $table,
                    'status' => $newStatus
                ]
            );

            flash(
                'success',
                'Listing status updated.'
            );
        }

        redirect(
            'admin/index.php?tab=listings'
        );
        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | EDIT TOUR
    |--------------------------------------------------------------------------
    */

    if ($action === 'edit_tour') {

        $id = (int)($_POST['id'] ?? 0);

        $tourTitle = trim(
            $_POST['title'] ?? ''
        );

        $description = trim(
            $_POST['description'] ?? ''
        );

        $price = (float)(
            $_POST['price'] ?? 0
        );

        $duration = (int)(
            $_POST['duration_days'] ?? 0
        );

        $maxGuests = (int)(
            $_POST['max_guests'] ?? 0
        );


        if ($id <= 0) {

            flash(
                'error',
                'Invalid tour.'
            );

            redirect(
                'admin/index.php?tab=listings'
            );
            exit;
        }


        if ($tourTitle === '') {

            flash(
                'error',
                'Tour title is required.'
            );

            redirect(
                'admin/index.php?tab=listings&edit_tour=' . $id
            );
            exit;
        }


        if ($price < 0) {

            flash(
                'error',
                'Tour price cannot be negative.'
            );

            redirect(
                'admin/index.php?tab=listings&edit_tour=' . $id
            );
            exit;
        }


        if ($duration < 1) {

            flash(
                'error',
                'Duration must be at least 1 day.'
            );

            redirect(
                'admin/index.php?tab=listings&edit_tour=' . $id
            );
            exit;
        }


        if ($maxGuests < 1) {

            flash(
                'error',
                'Maximum guests must be at least 1.'
            );

            redirect(
                'admin/index.php?tab=listings&edit_tour=' . $id
            );
            exit;
        }


        $tour = query(
            "
            SELECT *
            FROM tours
            WHERE id=?
            LIMIT 1
            ",
            'i',
            [$id]
        )->fetch_assoc();


        if (!$tour) {

            flash(
                'error',
                'Tour not found.'
            );

            redirect(
                'admin/index.php?tab=listings'
            );
            exit;
        }


        $image = admin_upload_image(
            'image',
            $tour['image'] ?? 'tour.svg'
        );


        query(
            "
            UPDATE tours
            SET
                title=?,
                description=?,
                price=?,
                duration_days=?,
                max_guests=?,
                image=?
            WHERE id=?
            ",
            'ssdiisi',
            [
                $tourTitle,
                $description,
                $price,
                $duration,
                $maxGuests,
                $image,
                $id
            ]
        );


        safe_log(
            'edit_tour',
            'tour',
            $id,
            [
                'title' => $tourTitle,
                'price' => $price,
                'duration_days' => $duration,
                'max_guests' => $maxGuests
            ]
        );


        flash(
            'success',
            'Tour updated successfully.'
        );


        redirect(
            'admin/index.php?tab=listings'
        );
        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | DELETE / ARCHIVE TOUR
    |--------------------------------------------------------------------------
    */

    if ($action === 'delete_tour') {

        $id = (int)($_POST['id'] ?? 0);


        if ($id <= 0) {

            flash(
                'error',
                'Invalid tour.'
            );

            redirect(
                'admin/index.php?tab=listings'
            );
            exit;
        }


        $tour = query(
            "
            SELECT *
            FROM tours
            WHERE id=?
            LIMIT 1
            ",
            'i',
            [$id]
        )->fetch_assoc();


        if (!$tour) {

            flash(
                'error',
                'Tour not found.'
            );

            redirect(
                'admin/index.php?tab=listings'
            );
            exit;
        }


        /*
        |--------------------------------------------------------------------------
        | CHECK BOOKING HISTORY
        |--------------------------------------------------------------------------
        */

        $bookingRow = query(
            "
            SELECT COUNT(*) AS n
            FROM bookings
            WHERE tour_id=?
            ",
            'i',
            [$id]
        )->fetch_assoc();


        $bookingCount = (int)(
            $bookingRow['n'] ?? 0
        );


        if ($bookingCount > 0) {

            /*
            | Never permanently delete a tour that
            | already has booking history.
            */

            query(
                "
                UPDATE tours
                SET status='inactive'
                WHERE id=?
                ",
                'i',
                [$id]
            );


            safe_log(
                'archive_tour',
                'tour',
                $id,
                [
                    'reason' => 'booking_history',
                    'bookings' => $bookingCount
                ]
            );


            flash(
                'success',
                'Tour has booking history, so it was archived instead of permanently deleted.'
            );

        } else {

            query(
                "
                DELETE FROM tours
                WHERE id=?
                ",
                'i',
                [$id]
            );


            safe_log(
                'delete_tour',
                'tour',
                $id
            );


            flash(
                'success',
                'Tour deleted.'
            );
        }


        redirect(
            'admin/index.php?tab=listings'
        );
        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | APPROVE PAYMENT
    |--------------------------------------------------------------------------
    */

    if ($action === 'approve_payment') {

        $paymentId = (int)(
            $_POST['payment_id'] ?? 0
        );


        $payment = query(
            "
            SELECT *
            FROM payments
            WHERE id=?
            LIMIT 1
            ",
            'i',
            [$paymentId]
        )->fetch_assoc();


        if (!$payment) {

            flash(
                'error',
                'Payment not found.'
            );

            redirect(
                'admin/index.php?tab=payments'
            );
            exit;
        }


        if ($payment['status'] !== 'pending') {

            flash(
                'error',
                'This payment is no longer pending.'
            );

            redirect(
                'admin/index.php?tab=payments'
            );
            exit;
        }


        query(
            "
            UPDATE payments
            SET
                status='paid',
                paid_at=NOW(),
                verified_by=?,
                verified_at=NOW()
            WHERE id=?
            ",
            'ii',
            [
                $u['id'],
                $paymentId
            ]
        );


        query(
            "
            UPDATE bookings
            SET
                payment_status='paid',
                status='confirmed'
            WHERE id=?
            ",
            'i',
            [
                $payment['booking_id']
            ]
        );


        $booking = query(
            "
            SELECT
                reference,
                customer_email,
                customer_phone,
                total_amount
            FROM bookings
            WHERE id=?
            LIMIT 1
            ",
            'i',
            [
                $payment['booking_id']
            ]
        )->fetch_assoc();


        if ($booking) {

            $subject =
                'Wildvista payment confirmed';

            $message =
                'Your Wildvista payment for booking '
                . $booking['reference']
                . ' has been verified. '
                . 'Your booking is now confirmed. '
                . 'Total paid: '
                . money(
                    (float)$booking['total_amount']
                )
                . '.';

            $channel = 'email';


            $notification = db()->prepare(
                "
                INSERT INTO notifications
                (
                    booking_id,
                    recipient_email,
                    recipient_phone,
                    channel,
                    subject,
                    message,
                    status
                )
                VALUES
                (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    'queued'
                )
                "
            );


            $notification->bind_param(
                'isssss',
                $payment['booking_id'],
                $booking['customer_email'],
                $booking['customer_phone'],
                $channel,
                $subject,
                $message
            );


            $notification->execute();
        }


        safe_log(
            'approve_payment',
            'payment',
            $paymentId,
            [
                'booking_id' =>
                    $payment['booking_id'],

                'method' =>
                    $payment['method'],

                'transaction_ref' =>
                    $payment['transaction_ref']
            ]
        );


        flash(
            'success',
            'Payment approved and booking confirmed.'
        );


        redirect(
            'admin/index.php?tab=payments'
        );
        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | REJECT PAYMENT
    |--------------------------------------------------------------------------
    */

    if ($action === 'reject_payment') {

        $paymentId = (int)(
            $_POST['payment_id'] ?? 0
        );


        $payment = query(
            "
            SELECT *
            FROM payments
            WHERE id=?
            LIMIT 1
            ",
            'i',
            [$paymentId]
        )->fetch_assoc();


        if (!$payment) {

            flash(
                'error',
                'Payment not found.'
            );

            redirect(
                'admin/index.php?tab=payments'
            );
            exit;
        }


        if ($payment['status'] !== 'pending') {

            flash(
                'error',
                'This payment is no longer pending.'
            );

            redirect(
                'admin/index.php?tab=payments'
            );
            exit;
        }


        query(
            "
            UPDATE payments
            SET
                status='failed',
                verified_by=?,
                verified_at=NOW()
            WHERE id=?
            ",
            'ii',
            [
                $u['id'],
                $paymentId
            ]
        );


        query(
            "
            UPDATE bookings
            SET
                payment_status='failed',
                status='pending_payment'
            WHERE id=?
            ",
            'i',
            [
                $payment['booking_id']
            ]
        );


        $booking = query(
            "
            SELECT
                reference,
                customer_email,
                customer_phone
            FROM bookings
            WHERE id=?
            LIMIT 1
            ",
            'i',
            [
                $payment['booking_id']
            ]
        )->fetch_assoc();


        if ($booking) {

            $subject =
                'Wildvista payment needs attention';

            $message =
                'The payment submitted for booking '
                . $booking['reference']
                . ' could not be verified. '
                . 'Please review your payment details '
                . 'and submit the transaction again.';

            $channel = 'email';


            $notification = db()->prepare(
                "
                INSERT INTO notifications
                (
                    booking_id,
                    recipient_email,
                    recipient_phone,
                    channel,
                    subject,
                    message,
                    status
                )
                VALUES
                (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    'queued'
                )
                "
            );


            $notification->bind_param(
                'isssss',
                $payment['booking_id'],
                $booking['customer_email'],
                $booking['customer_phone'],
                $channel,
                $subject,
                $message
            );


            $notification->execute();
        }


        safe_log(
            'reject_payment',
            'payment',
            $paymentId,
            [
                'booking_id' =>
                    $payment['booking_id'],

                'method' =>
                    $payment['method'],

                'transaction_ref' =>
                    $payment['transaction_ref']
            ]
        );


        flash(
            'success',
            'Payment rejected. Customer can submit again.'
        );


        redirect(
            'admin/index.php?tab=payments'
        );
        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | ADD DESTINATION
    |--------------------------------------------------------------------------
    */

    if ($action === 'add_destination') {

        $name = trim(
            $_POST['name'] ?? ''
        );

        $slug = slugify($name);

        $description = trim(
            $_POST['description'] ?? ''
        );

        $area = trim(
            $_POST['area'] ?? 'Arusha'
        );


        if ($name === '') {

            flash(
                'error',
                'Destination name is required.'
            );

            redirect(
                'admin/index.php?tab=content'
            );
            exit;
        }


        $img = admin_upload_image(
            'image',
            'park.svg'
        );


        query(
            "
            INSERT INTO destinations
            (
                name,
                slug,
                description,
                area,
                image,
                status,
                created_by
            )
            VALUES
            (
                ?,
                ?,
                ?,
                ?,
                ?,
                'active',
                ?
            )
            ",
            'sssssi',
            [
                $name,
                $slug,
                $description,
                $area,
                $img,
                $u['id']
            ]
        );


        $destinationId = db()->insert_id;


        safe_log(
            'add_destination',
            'destination',
            $destinationId,
            [
                'name' => $name
            ]
        );


        flash(
            'success',
            'Destination added.'
        );


        redirect(
            'admin/index.php?tab=content'
        );
        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | SAVE PAYMENT SETTINGS
    |--------------------------------------------------------------------------
    */

    if ($action === 'save_settings') {

        $keys = [

            'payment_mobile_money',
            'payment_bank',
            'payment_control_number',
            'payment_paypal',
            'paypal_link',
            'paypal_client_id',
            'paypal_secret'

        ];


        foreach ($keys as $key) {

            $value = trim(
                $_POST[$key] ?? ''
            );


            $st = db()->prepare(
                "
                INSERT INTO settings
                (
                    key_name,
                    value
                )
                VALUES
                (
                    ?,
                    ?
                )
                ON DUPLICATE KEY UPDATE
                    value=VALUES(value)
                "
            );


            $st->bind_param(
                'ss',
                $key,
                $value
            );


            $st->execute();
        }


        flash(
            'success',
            'Payment settings saved.'
        );


        redirect(
            'admin/index.php?tab=settings'
        );
        exit;
    }
}


/*
|--------------------------------------------------------------------------
| DASHBOARD STATISTICS
|--------------------------------------------------------------------------
*/

$stats = [

    'tours' => query(
        "
        SELECT COUNT(*) n
        FROM tours
        WHERE status='active'
        "
    )->fetch_assoc()['n'] ?? 0,


    'bookings' => query(
        "
        SELECT COUNT(*) n
        FROM bookings
        "
    )->fetch_assoc()['n'] ?? 0,


    'pending_payments' => query(
        "
        SELECT COUNT(*) n
        FROM payments
        WHERE status='pending'
        "
    )->fetch_assoc()['n'] ?? 0,


    'operators' => query(
        "
        SELECT COUNT(*) n
        FROM users
        WHERE role='operator'
        AND status='active'
        "
    )->fetch_assoc()['n'] ?? 0

];


/*
|--------------------------------------------------------------------------
| BOOKINGS
|--------------------------------------------------------------------------
*/

$bookings = query(
    "
    SELECT
        b.*,
        t.title AS tour

    FROM bookings b

    LEFT JOIN tours t
        ON t.id=b.tour_id

    ORDER BY b.id DESC

    LIMIT 50
    "
)->fetch_all(MYSQLI_ASSOC);


/*
|--------------------------------------------------------------------------
| PAYMENTS
|--------------------------------------------------------------------------
*/

$payments = query(
    "
    SELECT
        p.*,
        b.reference,
        b.customer_name,
        b.customer_email,
        b.customer_phone,
        b.total_amount,
        t.title AS tour

    FROM payments p

    LEFT JOIN bookings b
        ON b.id=p.booking_id

    LEFT JOIN tours t
        ON t.id=b.tour_id

    ORDER BY p.id DESC

    LIMIT 50
    "
)->fetch_all(MYSQLI_ASSOC);


/*
|--------------------------------------------------------------------------
| USERS
|--------------------------------------------------------------------------
*/

$users = query(
    "
    SELECT *
    FROM users
    ORDER BY created_at DESC
    "
)->fetch_all(MYSQLI_ASSOC);


/*
|--------------------------------------------------------------------------
| TOURS
|--------------------------------------------------------------------------
*/

$tours = query(
    "
    SELECT
        t.*,
        u.name AS operator_name

    FROM tours t

    LEFT JOIN users u
        ON u.id=t.operator_id

    ORDER BY t.id DESC
    "
)->fetch_all(MYSQLI_ASSOC);


/*
|--------------------------------------------------------------------------
| ACCOMMODATION
|--------------------------------------------------------------------------
*/

$acc = query(
    "
    SELECT
        a.*,
        u.name AS operator_name

    FROM accommodations a

    LEFT JOIN users u
        ON u.id=a.operator_id

    ORDER BY a.id DESC
    "
)->fetch_all(MYSQLI_ASSOC);


/*
|--------------------------------------------------------------------------
| TRANSPORT
|--------------------------------------------------------------------------
*/

$trs = query(
    "
    SELECT
        t.*,
        u.name AS operator_name

    FROM transports t

    LEFT JOIN users u
        ON u.id=t.operator_id

    ORDER BY t.id DESC
    "
)->fetch_all(MYSQLI_ASSOC);


/*
|--------------------------------------------------------------------------
| ACTIVITIES
|--------------------------------------------------------------------------
*/

$acts = query(
    "
    SELECT
        a.*,
        u.name AS operator_name

    FROM activities a

    LEFT JOIN users u
        ON u.id=a.operator_id

    ORDER BY a.id DESC
    "
)->fetch_all(MYSQLI_ASSOC);


/*
|--------------------------------------------------------------------------
| EDIT TOUR
|--------------------------------------------------------------------------
*/

$editTourId = (int)(
    $_GET['edit_tour'] ?? 0
);

$editTour = null;

if (
    $tab === 'listings' &&
    $editTourId > 0
) {

    $editTour = query(
        "
        SELECT *
        FROM tours
        WHERE id=?
        LIMIT 1
        ",
        'i',
        [$editTourId]
    )->fetch_assoc();

}


/*
|--------------------------------------------------------------------------
| PAGE TITLE
|--------------------------------------------------------------------------
*/

$title = [

    'overview' =>
        'Dashboard',

    'bookings' =>
        'Bookings',

    'payments' =>
        'Payments & Verification',

    'listings' =>
        'Listings',

    'users' =>
        'Users & Operators',

    'content' =>
        'Arusha Content',

    'settings' =>
        'System Settings'

][$tab] ?? 'Admin Dashboard';


/*
|--------------------------------------------------------------------------
| PORTAL HEADER
|--------------------------------------------------------------------------
*/

require __DIR__ . '/../app/views/portal_header.php';

?>


<?php if ($tab === 'overview'): ?>


    <div class="portal