<?php

require_once __DIR__ . '/../app/core/bootstrap.php';

$u = current_user();

if (!$u) {
    redirect('login.php');
}

$title = 'My Wildvista';

$db = db();

$userId = (int)($u['id'] ?? 0);
$userEmail = trim((string)($u['email'] ?? ''));

$bookings = [];

/*
|--------------------------------------------------------------------------
| Load customer bookings
|--------------------------------------------------------------------------
*/

try {

    $stmt = $db->prepare("
        SELECT
            b.id,
            b.reference,
            b.customer_id,
            b.customer_name,
            b.customer_email,
            b.tour_id,
            b.booking_date,
            b.guests,
            b.total_amount,
            b.status,
            b.payment_status,
            b.created_at,
            t.title AS tour_title
        FROM bookings b
        LEFT JOIN tours t ON t.id = b.tour_id
        WHERE
            b.customer_id = ?
            OR b.customer_email = ?
        ORDER BY b.id DESC
    ");

    $stmt->bind_param('is', $userId, $userEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    $bookings = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

} catch (Throwable $e) {

    $bookings = [];

    // Keep the customer portal usable even if a non-critical query fails.
}


/*
|--------------------------------------------------------------------------
| Get payment state
|--------------------------------------------------------------------------
*/

function get_customer_payment_state($db, $booking)
{
    $payment = null;

    try {

        $stmt = $db->prepare("
            SELECT
                id,
                status,
                transaction_ref,
                proof_path,
                submitted_at,
                verified_at,
                paid_at,
                created_at
            FROM payments
            WHERE booking_id = ?
            ORDER BY id DESC
            LIMIT 1
        ");

        $bookingId = (int)$booking['id'];
        $stmt->bind_param('i', $bookingId);
        $stmt->execute();
        $result = $stmt->get_result();
        $payment = $result ? $result->fetch_assoc() : null;

    } catch (Throwable $e) {

        $payment = null;
    }


    $bookingPaymentStatus = strtolower(
        trim((string)($booking['payment_status'] ?? ''))
    );

    $bookingStatus = strtolower(
        trim((string)($booking['status'] ?? ''))
    );

    $paymentStatus = strtolower(
        trim((string)($payment['status'] ?? ''))
    );


    /*
    |--------------------------------------------------------------------------
    | PAID
    |--------------------------------------------------------------------------
    */

    if (
        $bookingPaymentStatus === 'paid' ||
        $paymentStatus === 'paid' ||
        $bookingStatus === 'confirmed'
    ) {

        return [
            'state' => 'paid',
            'label' => 'Payment confirmed',
            'message' => 'Your payment has been verified.',
            'action' => ''
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | PAYMENT SUBMITTED / WAITING FOR VERIFICATION
    |--------------------------------------------------------------------------
    */

    if (
        $paymentStatus === 'pending' &&
        (
            !empty($payment['transaction_ref']) ||
            !empty($payment['proof_path']) ||
            !empty($payment['submitted_at'])
        )
    ) {

        return [
            'state' => 'review',
            'label' => 'Payment under review',
            'message' => 'Your payment has been submitted and is waiting for verification.',
            'action' => ''
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | FAILED PAYMENT
    |--------------------------------------------------------------------------
    */

    if (
        $paymentStatus === 'failed' ||
        $bookingPaymentStatus === 'failed'
    ) {

        return [
            'state' => 'failed',
            'label' => 'Payment needs attention',
            'message' => 'Your previous payment submission was not approved. You can submit payment again.',
            'action' => 'resubmit'
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | UNPAID
    |--------------------------------------------------------------------------
    */

    return [
        'state' => 'unpaid',
        'label' => 'Pending payment',
        'message' => 'Payment is still pending. Complete payment and submit your transaction/reference number.',
        'action' => 'continue'
    ];
}


/*
|--------------------------------------------------------------------------
| Portal header
|--------------------------------------------------------------------------
*/

require __DIR__ . '/../app/views/portal_header.php';

?>

<section class="portal-page-head">

    <div>

        <div class="portal-eyebrow">
            WILDVISTA / CUSTOMER
        </div>

        <h1>
            My Wildvista
        </h1>

        <p class="portal-muted">
            Manage your bookings and payment status.
        </p>

    </div>

</section>


<?php if (empty($bookings)): ?>

    <section class="portal-card empty-state">

        <div class="empty-icon">
            ✦
        </div>

        <h2>
            No bookings yet
        </h2>

        <p>
            You do not have any bookings yet.
            Explore our safari experiences and start planning your trip.
        </p>

        <a
            class="btn btn-primary"
            href="<?=e(url('index.php'))?>"
        >
            Explore Wildvista
        </a>

    </section>

<?php else: ?>


    <section class="booking-grid">

        <?php foreach ($bookings as $booking): ?>

            <?php

            $payment = get_customer_payment_state(
                $db,
                $booking
            );

            $reference = (string)(
                $booking['reference'] ?? ''
            );

            $tourTitle = trim(
                (string)(
                    $booking['tour_title'] ?? ''
                )
            );

            if ($tourTitle === '') {
                $tourTitle = 'Wildvista Safari';
            }


            $date = !empty($booking['booking_date'])
                ? date(
                    'Y-m-d',
                    strtotime($booking['booking_date'])
                )
                : 'Not set';


            $guests = max(
                1,
                (int)($booking['guests'] ?? 1)
            );


            $total = number_format(
                (float)($booking['total_amount'] ?? 0),
                2
            );


            /*
            |--------------------------------------------------------------------------
            | IMPORTANT:
            | Use url(), because that is the helper your project actually uses.
            |--------------------------------------------------------------------------
            */

            $paymentUrl = url(
                'payment.php?ref=' .
                rawurlencode($reference)
            );

            ?>


            <article class="booking-card">


                <div class="booking-status">

                    <span class="status-pill status-<?=e(
                        $payment['state']
                    )?>">

                        <?=e(
                            $payment['label']
                        )?>

                    </span>

                </div>


                <h2>
                    <?=e($tourTitle)?>
                </h2>


                <div class="booking-meta">


                    <div>

                        <span>
                            Reference
                        </span>

                        <strong>
                            <?=e($reference)?>
                        </strong>

                    </div>


                    <div>

                        <span>
                            Date
                        </span>

                        <strong>
                            <?=e($date)?>
                        </strong>

                    </div>


                    <div>

                        <span>
                            Guests
                        </span>

                        <strong>
                            <?=e($guests)?>
                        </strong>

                    </div>


                </div>


                <div class="booking-total">

                    <span>
                        Total
                    </span>

                    <strong>
                        USD <?=e($total)?>
                    </strong>

                </div>


                <div class="booking-payment-message">

                    <?=e(
                        $payment['message']
                    )?>

                </div>


                <?php if ($payment['action'] === 'continue'): ?>


                    <a
                        class="booking-action"
                        href="<?=e($paymentUrl)?>"
                    >
                        Continue payment →
                    </a>


                <?php elseif ($payment['action'] === 'resubmit'): ?>


                    <a
                        class="booking-action"
                        href="<?=e($paymentUrl)?>"
                    >
                        Resubmit payment →
                    </a>


                <?php elseif ($payment['state'] === 'review'): ?>


                    <div class="booking-review-note">

                        Payment submitted.
                        Please wait for verification.

                    </div>


                <?php elseif ($payment['state'] === 'paid'): ?>


                    <div class="booking-paid-note">

                        ✓ Payment confirmed

                    </div>


                <?php endif; ?>


            </article>


        <?php endforeach; ?>

    </section>


<?php endif; ?>


<style>

.portal-page-head {
    margin-bottom: 28px;
}

.portal-eyebrow {
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 2px;
    color: #0f5a45;
    margin-bottom: 8px;
}

.portal-page-head h1 {
    margin: 0;
    color: #092f25;
    font-family: Georgia, serif;
    font-size: clamp(30px, 4vw, 42px);
}

.portal-muted {
    color: #6b756f;
    margin: 8px 0 0;
}

.booking-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 24px;
}

.booking-card {
    background: #fffdf8;
    border: 1px solid #e4e8df;
    border-radius: 24px;
    padding: 24px;
    box-shadow: 0 18px 50px rgba(5,45,34,.10);
}

.booking-status {
    margin-bottom: 16px;
}

.status-pill {
    display: inline-flex;
    align-items: center;
    padding: 7px 12px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 800;
}

.status-unpaid {
    background: #fff1cf;
    color: #89651f;
}

.status-review {
    background: #eaf1ec;
    color: #0f5a45;
}

.status-failed {
    background: #fde9e7;
    color: #a13e38;
}

.status-paid {
    background: #e5f4e9;
    color: #28734c;
}

.booking-card h2 {
    margin: 0 0 22px;
    color: #092f25;
    font-family: Georgia, serif;
    font-size: 25px;
    line-height: 1.15;
}

.booking-meta {
    display: grid;
    gap: 16px;
}

.booking-meta > div {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    padding-bottom: 12px;
    border-bottom: 1px solid #e7e9e3;
}

.booking-meta span,
.booking-total span {
    color: #6b756f;
    font-size: 13px;
}

.booking-meta strong {
    color: #214238;
    font-size: 14px;
}

.booking-total {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    margin-top: 18px;
    padding-top: 4px;
}

.booking-total strong {
    color: #092f25;
    font-size: 20px;
}

.booking-payment-message {
    margin-top: 18px;
    padding: 14px 15px;
    border-radius: 15px;
    background: #fff7e4;
    border: 1px solid #f0dfb7;
    color: #705c32;
    font-size: 13px;
    line-height: 1.6;
}

.booking-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-top: 14px;
    width: 100%;
    min-height: 48px;
    padding: 12px 18px;
    border-radius: 14px;
    background: #06382c;
    color: #fff;
    text-decoration: none;
    font-weight: 800;
    transition: .2s ease;
}

.booking-action:hover {
    background: #0f5a45;
    transform: translateY(-1px);
}

.booking-review-note,
.booking-paid-note {
    margin-top: 14px;
    padding: 13px 15px;
    border-radius: 14px;
    font-size: 13px;
    font-weight: 700;
}

.booking-review-note {
    background: #edf4ef;
    color: #28634d;
}

.booking-paid-note {
    background: #e8f5eb;
    color: #28734c;
}

.empty-state {
    max-width: 620px;
    text-align: center;
    padding: 50px 30px;
    margin: 20px auto;
}

.empty-icon {
    width: 60px;
    height: 60px;
    margin: 0 auto 18px;
    display: grid;
    place-items: center;
    border-radius: 50%;
    background: #e9f1eb;
    color: #0f5a45;
    font-size: 25px;
}

.empty-state h2 {
    margin: 0 0 10px;
    color: #092f25;
    font-family: Georgia, serif;
}

.empty-state p {
    color: #6b756f;
    line-height: 1.7;
    margin-bottom: 24px;
}

@media (max-width: 1100px) {

    .booking-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

}

@media (max-width: 720px) {

    .booking-grid {
        grid-template-columns: 1fr;
    }

    .booking-card {
        padding: 20px;
    }

    .booking-card h2 {
        font-size: 23px;
    }

}

</style>