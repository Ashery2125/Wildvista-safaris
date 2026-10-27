<?php
require_once __DIR__ . '/app/core/bootstrap.php';

$ref = trim($_GET['ref'] ?? $_POST['ref'] ?? '');

if ($ref === '') {
    redirect('index.php');
}

/*
|--------------------------------------------------------------------------
| Load booking + latest payment
|--------------------------------------------------------------------------
*/
$st = db()->prepare("
    SELECT
        b.*,
        t.title AS tour,
        p.id AS payment_id,
        p.provider,
        p.method,
        p.transaction_ref,
        p.status AS payment_state,
        p.proof_path,
        p.customer_note
    FROM bookings b
    JOIN tours t ON t.id = b.tour_id
    LEFT JOIN payments p ON p.booking_id = b.id
    WHERE b.reference = ?
    ORDER BY p.id DESC
    LIMIT 1
");

$st->bind_param('s', $ref);
$st->execute();

$booking = $st->get_result()->fetch_assoc();

if (!$booking) {
    redirect('index.php');
}

$error   = '';
$success = '';

/*
|--------------------------------------------------------------------------
| Payment methods
|--------------------------------------------------------------------------
*/
$allowedMethods = [
    'mobile_money',
    'bank',
    'control_number',
    'paypal'
];

/*
|--------------------------------------------------------------------------
| Payment submission
|--------------------------------------------------------------------------
*/
if (is_post()) {

    verify_csrf();

    $method = trim($_POST['method'] ?? '');
    $tx     = trim($_POST['transaction_ref'] ?? '');
    $note   = trim($_POST['note'] ?? '');

    if (!in_array($method, $allowedMethods, true)) {

        $error = 'Please select a valid payment method.';

    } elseif ($tx === '') {

        $error = 'Enter the transaction or reference number after making payment.';

    } else {

        $proofPath = $booking['proof_path'] ?? null;

        /*
        |--------------------------------------------------------------------------
        | Payment proof upload
        |--------------------------------------------------------------------------
        */
        if (
            isset($_FILES['proof']) &&
            ($_FILES['proof']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE
        ) {

            if ($_FILES['proof']['error'] !== UPLOAD_ERR_OK) {

                $error = 'The payment proof could not be uploaded.';

            } elseif ($_FILES['proof']['size'] > (3 * 1024 * 1024)) {

                $error = 'Payment proof must be 3MB or smaller.';

            } else {

                $ext = strtolower(
                    pathinfo($_FILES['proof']['name'], PATHINFO_EXTENSION)
                );

                $allowedProof = [
                    'jpg',
                    'jpeg',
                    'png',
                    'webp',
                    'pdf'
                ];

                if (!in_array($ext, $allowedProof, true)) {

                    $error = 'Proof must be JPG, PNG, WEBP or PDF.';

                } else {

                    $uploadDir = __DIR__ . '/uploads/payments';

                    if (!is_dir($uploadDir)) {
                        @mkdir($uploadDir, 0755, true);
                    }

                    try {
                        $random = bin2hex(random_bytes(5));
                    } catch (Throwable $e) {
                        $random = uniqid();
                    }

                    $filename =
                        'payment_' .
                        (int)$booking['id'] .
                        '_' .
                        $random .
                        '.' .
                        $ext;

                    $destination = $uploadDir . '/' . $filename;

                    if (
                        move_uploaded_file(
                            $_FILES['proof']['tmp_name'],
                            $destination
                        )
                    ) {

                        $proofPath = 'uploads/payments/' . $filename;

                    } else {

                        $error = 'Could not save the payment proof.';
                    }
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Save payment
        |--------------------------------------------------------------------------
        */
        if ($error === '') {

            $provider =
                $method === 'paypal'
                    ? 'paypal_manual'
                    : 'manual';

            if (!empty($booking['payment_id'])) {

                $paymentId = (int)$booking['payment_id'];

                $stp = db()->prepare("
                    UPDATE payments
                    SET
                        provider = ?,
                        method = ?,
                        transaction_ref = ?,
                        proof_path = ?,
                        customer_note = ?,
                        status = 'pending',
                        submitted_at = NOW()
                    WHERE id = ?
                ");

                $stp->bind_param(
                    'sssssi',
                    $provider,
                    $method,
                    $tx,
                    $proofPath,
                    $note,
                    $paymentId
                );

                $stp->execute();

            } else {

                $amount = (float)$booking['total_amount'];
                $bookingId = (int)$booking['id'];

                $stp = db()->prepare("
                    INSERT INTO payments
                    (
                        booking_id,
                        provider,
                        method,
                        transaction_ref,
                        amount,
                        status,
                        proof_path,
                        customer_note,
                        submitted_at
                    )
                    VALUES
                    (
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        'pending',
                        ?,
                        ?,
                        NOW()
                    )
                ");

                $stp->bind_param(
                    'isssdss',
                    $bookingId,
                    $provider,
                    $method,
                    $tx,
                    $amount,
                    $proofPath,
                    $note
                );

                $stp->execute();

                $paymentId = db()->insert_id;
            }

            /*
            |--------------------------------------------------------------------------
            | Booking remains pending until admin verifies payment
            |--------------------------------------------------------------------------
            */
            query(
                "
                UPDATE bookings
                SET
                    payment_status = 'pending',
                    status = 'pending_payment'
                WHERE id = ?
                ",
                'i',
                [(int)$booking['id']]
            );

            /*
            |--------------------------------------------------------------------------
            | Notification queue
            |--------------------------------------------------------------------------
            */
            $subject = 'Wildvista payment submitted';

            $message =
                "Payment submitted for booking " .
                $booking['reference'] .
                ". Transaction/reference: " .
                $tx .
                ". Admin verification is pending.";

            $channel = 'email';

            $recipientEmail =
                $booking['customer_email'] ?? '';

            $recipientPhone =
                $booking['customer_phone'] ?? '';

            $sn = db()->prepare("
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
            ");

            $bookingId = (int)$booking['id'];

            $sn->bind_param(
                'isssss',
                $bookingId,
                $recipientEmail,
                $recipientPhone,
                $channel,
                $subject,
                $message
            );

            $sn->execute();

            safe_log(
                'payment_submitted',
                'payment',
                $paymentId ?? null,
                [
                    'booking' => $booking['reference'],
                    'method' => $method,
                    'reference' => $tx
                ]
            );

            $success =
                'Payment details submitted successfully. ' .
                'Wildvista will verify the transaction before confirming your booking.';

            /*
            |--------------------------------------------------------------------------
            | Update page state
            |--------------------------------------------------------------------------
            */
            $booking['payment_state']   = 'pending';
            $booking['method']          = $method;
            $booking['transaction_ref'] = $tx;
            $booking['proof_path']      = $proofPath;
        }
    }
}

/*
|--------------------------------------------------------------------------
| Payment settings
|--------------------------------------------------------------------------
*/
$mobileMoneyInstructions =
    trim((string)setting('payment_mobile_money'));

$bankInstructions =
    trim((string)setting('payment_bank'));

$controlInstructions =
    trim((string)setting('payment_control_number'));

$paypalInstructions =
    trim((string)setting('payment_paypal'));

$paypalLink =
    trim((string)setting('paypal_link'));

/*
|--------------------------------------------------------------------------
| Safe defaults
|--------------------------------------------------------------------------
*/
if ($mobileMoneyInstructions === '') {
    $mobileMoneyInstructions =
        'Pay to Wildvista Mobile Money: +255 621 385 106';
}

if ($bankInstructions === '') {
    $bankInstructions =
        'Use the bank details provided by Wildvista.';
}

if ($controlInstructions === '') {
    $controlInstructions =
        'Use the control / Lipa number provided by Wildvista.';
}

if ($paypalInstructions === '') {
    $paypalInstructions =
        'Open the Wildvista PayPal payment link, complete the payment, then enter your PayPal transaction ID.';
}

$title = 'Payment · ' . $booking['reference'];

require __DIR__ . '/app/views/header.php';
?>

<section class="section payment-page">

    <div class="eyebrow" style="color:var(--green)">
        BOOKING PAYMENT
    </div>

    <h1 style="font-family:Georgia,serif;font-size:52px">
        Complete your payment
    </h1>

    <p class="muted" style="max-width:760px">
        Booking
        <strong><?= e($booking['reference']) ?></strong>
        has been saved successfully.
        Choose your payment method, complete the payment, then submit
        your transaction/reference number for verification.
    </p>

    <?php if ($error): ?>

        <div
            class="toast error"
            style="
                position:relative;
                top:auto;
                right:auto;
                margin:18px 0;
                animation:none;
            "
        >
            <?= e($error) ?>
        </div>

    <?php endif; ?>


    <?php if ($success): ?>

        <div
            class="notice"
            style="
                background:#eaf5ee;
                color:#245f42;
                margin:18px 0;
            "
        >
            <strong>Payment submitted.</strong>
            <?= e($success) ?>
        </div>

    <?php endif; ?>


    <div class="builder-grid">

        <!-- PAYMENT METHODS -->
        <div class="panel">

            <h2 style="margin-top:0">
                Choose payment method
            </h2>

            <div class="payment-methods">

                <!-- MOBILE MONEY -->
                <label class="payment-method">

                    <input
                        type="radio"
                        name="method_pick"
                        value="mobile_money"
                        checked
                        form="payment-form"
                    >

                    <span>
                        <strong>Mobile Money</strong>

                        <small class="muted">
                            Pay to
                            <strong>+255 621 385 106</strong>.
                            Use the transaction number returned by
                            your mobile-money service.
                        </small>
                    </span>

                </label>


                <!-- BANK -->
                <label class="payment-method">

                    <input
                        type="radio"
                        name="method_pick"
                        value="bank"
                        form="payment-form"
                    >

                    <span>
                        <strong>Bank Transfer</strong>

                        <small class="muted">
                            Transfer using the bank details provided
                            by Wildvista.
                        </small>
                    </span>

                </label>


                <!-- CONTROL NUMBER -->
                <label class="payment-method">

                    <input
                        type="radio"
                        name="method_pick"
                        value="control_number"
                        form="payment-form"
                    >

                    <span>
                        <strong>Control / Lipa Number</strong>

                        <small class="muted">
                            Use the control or Lipa number provided
                            by Wildvista.
                        </small>
                    </span>

                </label>


                <!-- PAYPAL -->
                <label class="payment-method">

                    <input
                        type="radio"
                        name="method_pick"
                        value="paypal"
                        form="payment-form"
                    >

                    <span>
                        <strong>PayPal</strong>

                        <small class="muted">
                            Pay using the Wildvista PayPal link,
                            then enter the PayPal transaction ID.
                        </small>
                    </span>

                </label>

            </div>


            <!-- DYNAMIC INSTRUCTIONS -->
            <div
                class="payment-instructions"
                id="paymentInstructions"
            ></div>


            <!-- PAYPAL BUTTON -->
            <div
                id="paypalAction"
                class="paypal-action"
                hidden
            >

                <?php if ($paypalLink): ?>

                    <a
                        class="btn paypal-btn"
                        href="<?= e($paypalLink) ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        Open PayPal payment →
                    </a>

                    <p
                        class="muted"
                        style="margin-top:10px"
                    >
                        After completing PayPal payment,
                        return here and enter the PayPal
                        transaction ID below.
                    </p>

                <?php else: ?>

                    <div class="notice">
                        PayPal payment link has not been configured
                        by the Wildvista administrator yet.
                    </div>

                <?php endif; ?>

            </div>


            <!-- PAYMENT FORM -->
            <form
                id="payment-form"
                method="post"
                enctype="multipart/form-data"
                data-payment-form
            >

                <?= csrf_field() ?>

                <input
                    type="hidden"
                    name="ref"
                    value="<?= e($ref) ?>"
                >

                <input
                    type="hidden"
                    name="method"
                    id="methodValue"
                    value="mobile_money"
                >


                <label>
                    Transaction / reference number
                </label>

                <input
                    name="transaction_ref"
                    required
                    value="<?= e($booking['transaction_ref'] ?? '') ?>"
                    placeholder="e.g. 1234567890"
                >


                <label>
                    Payment proof
                    <span class="muted">(optional)</span>
                </label>

                <input
                    type="file"
                    name="proof"
                    accept="
                        image/jpeg,
                        image/png,
                        image/webp,
                        application/pdf
                    "
                >

                <small class="muted">
                    JPG, PNG, WEBP or PDF · Maximum 3MB
                </small>


                <label>
                    Note
                    <span class="muted">(optional)</span>
                </label>

                <textarea
                    name="note"
                    placeholder="Any useful payment detail..."
                ><?= e($booking['customer_note'] ?? '') ?></textarea>


                <button
                    class="btn btn-dark"
                    style="
                        width:100%;
                        margin-top:14px;
                    "
                    type="submit"
                >
                    Submit payment for verification →
                </button>

            </form>

        </div>


        <!-- SUMMARY -->
        <div>

            <div class="panel sticky-box">

                <span
                    class="eyebrow"
                    style="color:var(--green)"
                >
                    PAYMENT SUMMARY
                </span>

                <h2 style="margin:7px 0">
                    <?= e($booking['tour']) ?>
                </h2>


                <div class="summary-line">
                    <span>Reference</span>
                    <strong>
                        <?= e($booking['reference']) ?>
                    </strong>
                </div>


                <div class="summary-line">
                    <span>Travel date</span>
                    <strong>
                        <?= e($booking['booking_date']) ?>
                    </strong>
                </div>


                <div class="summary-line">
                    <span>Guests</span>
                    <strong>
                        <?= e($booking['guests']) ?>
                    </strong>
                </div>


                <div class="summary-total">

                    <span>Total due</span>

                    <span>
                        <?= money((float)$booking['total_amount']) ?>
                    </span>

                </div>


                <div class="notice">

                    Your booking has been saved.

                    <strong>
                        Payment verification is required
                    </strong>
                    before the booking becomes confirmed.

                </div>

            </div>

        </div>

    </div>

</section>


<script>
(function () {

    const notes = {

        mobile_money:
            <?= json_encode($mobileMoneyInstructions) ?>,

        bank:
            <?= json_encode($bankInstructions) ?>,

        control_number:
            <?= json_encode($controlInstructions) ?>,

        paypal:
            <?= json_encode($paypalInstructions) ?>

    };


    const radios =
        document.querySelectorAll('[name="method_pick"]');

    const box =
        document.getElementById('paymentInstructions');

    const hidden =
        document.getElementById('methodValue');

    const paypal =
        document.getElementById('paypalAction');


    function setPaymentMethod() {

        const selected =
            document.querySelector(
                '[name="method_pick"]:checked'
            );

        if (!selected) {
            return;
        }

        const value = selected.value;

        hidden.value = value;


        box.innerHTML =
            '<strong>How to pay</strong>' +
            '<p style="white-space:pre-line;margin:7px 0 0">' +
            escapeHtml(notes[value] || '') +
            '</p>';


        if (paypal) {
            paypal.hidden = value !== 'paypal';
        }
    }


    function escapeHtml(value) {

        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

    }


    radios.forEach(function (radio) {

        radio.addEventListener(
            'change',
            setPaymentMethod
        );

    });


    setPaymentMethod();

})();
</script>


<?php require __DIR__ . '/app/views/footer.php'; ?>