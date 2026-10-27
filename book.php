<?php
require_once __DIR__.'/app/core/bootstrap.php';

$tourId=(int)($_GET['tour_id']??$_POST['tour_id']??0);

$st=db()->prepare("
    SELECT t.*, d.name destination
    FROM tours t
    LEFT JOIN destinations d ON d.id=t.destination_id
    WHERE t.id=? AND t.status='active'
    LIMIT 1
");
$st->bind_param('i',$tourId);
$st->execute();
$tour=$st->get_result()->fetch_assoc();

if(!$tour) redirect('tours.php');

$accom=query("SELECT * FROM accommodations WHERE status='active' ORDER BY name")
    ->fetch_all(MYSQLI_ASSOC);

$trans=query("SELECT * FROM transports WHERE status='active' ORDER BY name")
    ->fetch_all(MYSQLI_ASSOC);

$acts=query("SELECT * FROM activities WHERE status='active' ORDER BY name")
    ->fetch_all(MYSQLI_ASSOC);

$u=current_user();
$error='';

if(is_post()){
    verify_csrf();

    $name=trim($_POST['customer_name']??'');
    $email=trim($_POST['customer_email']??'');
    $phone=trim($_POST['customer_phone']??'');
    $date=$_POST['booking_date']??'';

    $guests=max(1,(int)($_POST['guests']??1));
    $acId=(int)($_POST['accommodation_id']??0);
    $nights=max(0,(int)($_POST['nights']??0));
    $trId=(int)($_POST['transport_id']??0);
    $activityIds=array_map('intval',$_POST['activities']??[]);
    $method=trim($_POST['payment_method']??'mobile_money');

    $allowedMethods=[
        'mobile_money',
        'bank',
        'control_number',
        'paypal'
    ];

    if(!$name || !filter_var($email,FILTER_VALIDATE_EMAIL) || !$date){
        $error='Please enter your name, valid email and travel date.';
    }
    elseif(!in_array($method,$allowedMethods,true)){
        $error='Please select a valid payment method.';
    }
    elseif(strtotime($date) === false || $date < date('Y-m-d', strtotime('+1 day'))){
        $error='Please choose a valid travel date from tomorrow onward.';
    }
    elseif($guests > (int)$tour['max_guests']){
        $error='The selected number of guests exceeds the tour capacity.';
    }
    else{
        /* Prevent accidental duplicate bookings for the same tour/date. */
        if ($u && !empty($u['id'])) {
            $dup = query(
                "SELECT reference FROM bookings WHERE customer_id=? AND tour_id=? AND booking_date=? AND status IN ('pending_payment','confirmed','completed') LIMIT 1",
                'iis',
                [(int)$u['id'], $tourId, $date]
            )->fetch_assoc();
        } else {
            $dup = query(
                "SELECT reference FROM bookings WHERE customer_email=? AND tour_id=? AND booking_date=? AND status IN ('pending_payment','confirmed','completed') LIMIT 1",
                'sis',
                [$email, $tourId, $date]
            )->fetch_assoc();
        }

        if ($dup) {
            $error = 'A booking already exists for this tour on '.$date.' ('.$dup['reference'].'). Open My Wildvista or use that booking instead of creating another one.';
        }
    }

    if($error===''){

        /*
         * ONE CORE TOUR PER BOOKING
         * Add-ons are optional.
         */
        $total=(float)$tour['base_price']*$guests;

        $items=[
            [
                'tour',
                (int)$tour['id'],
                $tour['title'],
                $guests,
                (float)$tour['base_price']
            ]
        ];

        /* Accommodation */
        if($acId>0){

            foreach($accom as $a){

                if((int)$a['id']===$acId){

                    if($nights<1){
                        $nights=1;
                    }

                    $line=(float)$a['price_per_night']*$nights;

                    $total+=$line;

                    $items[]=[
                        'accommodation',
                        (int)$a['id'],
                        $a['name'],
                        $nights,
                        (float)$a['price_per_night']
                    ];

                    break;
                }
            }
        }

        /* Transport */
        if($trId>0){

            foreach($trans as $tr){

                if((int)$tr['id']===$trId){

                    $total+=(float)$tr['price'];

                    $items[]=[
                        'transport',
                        (int)$tr['id'],
                        $tr['name'],
                        1,
                        (float)$tr['price']
                    ];

                    break;
                }
            }
        }

        /* Activities */
        foreach($acts as $a){

            if(in_array((int)$a['id'],$activityIds,true)){

                $price=(float)$a['price'];
                $line=$price*$guests;

                $total+=$line;

                $items[]=[
                    'activity',
                    (int)$a['id'],
                    $a['name'],
                    $guests,
                    $price
                ];
            }
        }

        $ref='WV-'.strtoupper(substr(bin2hex(random_bytes(5)),0,8));

        $customerId=$u['id']??null;
        $notes=trim($_POST['notes']??'');

        /*
         * CREATE BOOKING
         */
        $sql="
            INSERT INTO bookings
            (
                reference,
                customer_id,
                tour_id,
                booking_date,
                guests,
                accommodation_id,
                accommodation_nights,
                transport_id,
                total_amount,
                status,
                payment_status,
                customer_name,
                customer_email,
                customer_phone,
                notes
            )
            VALUES
            (
                ?,?,?,?,?,?,?,?,?,
                'pending_payment',
                'unpaid',
                ?,?,?,?
            )
        ";

        $stb=db()->prepare($sql);

        $stb->bind_param(
            'siisiiiidssss',
            $ref,
            $customerId,
            $tourId,
            $date,
            $guests,
            $acId,
            $nights,
            $trId,
            $total,
            $name,
            $email,
            $phone,
            $notes
        );

        if(!$stb->execute()){

            $error='Could not create booking: '.db()->error;

        }else{

            $bid=db()->insert_id;

            /*
             * PAYMENT
             */
            $provider=($method==='paypal')
                ? 'paypal_manual'
                : 'manual';

            $stp=db()->prepare("
                INSERT INTO payments
                (
                    booking_id,
                    provider,
                    method,
                    amount,
                    status
                )
                VALUES(?,?,?,?, 'pending')
            ");

            $stp->bind_param(
                'issd',
                $bid,
                $provider,
                $method,
                $total
            );

            $stp->execute();

            /*
             * BOOKING ITEMS
             */
            foreach($items as $it){

                $itemTotal=$it[3]*$it[4];

                $sti=db()->prepare("
                    INSERT INTO booking_items
                    (
                        booking_id,
                        item_type,
                        item_id,
                        item_name,
                        qty,
                        unit_price,
                        total_price
                    )
                    VALUES(?,?,?,?,?,?,?)
                ");

                $sti->bind_param(
                    'isidddd',
                    $bid,
                    $it[0],
                    $it[1],
                    $it[2],
                    $it[3],
                    $it[4],
                    $itemTotal
                );

                $sti->execute();
            }

            /*
             * NOTIFICATION
             */
            $channel='email';
            $subject='Wildvista booking received';

            $message="
Your Wildvista booking {$ref} has been created.

Total due: ".money($total)."

Payment method selected:
".str_replace('_',' ',$method)."

Please complete your payment and submit the transaction/reference number on the payment page.

Your booking will be confirmed after Wildvista verifies the payment.
";

            $sn=db()->prepare("
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
                VALUES(?,?,?,?,?,?, 'queued')
            ");

            $sn->bind_param(
                'isssss',
                $bid,
                $email,
                $phone,
                $channel,
                $subject,
                $message
            );

            $sn->execute();

            safe_log(
                'created_booking',
                'booking',
                $bid,
                [
                    'reference'=>$ref,
                    'amount'=>$total,
                    'payment_method'=>$method
                ]
            );

            flash(
                'success',
                'Booking '.$ref.' created. Continue to payment.'
            );

            redirect(
                'payment.php?ref='.urlencode($ref)
            );
        }
    }
}

$title='Build your trip';

require __DIR__.'/app/views/header.php';

$default=$u
    ? [
        'name'=>$u['name'],
        'email'=>$u['email'],
        'phone'=>$u['phone']
      ]
    : [
        'name'=>'',
        'email'=>'',
        'phone'=>''
      ];
?>

<section class="section">

<div class="eyebrow" style="color:var(--green)">
    Customize
</div>

<h1 style="font-family:Georgia,serif;font-size:52px">
    Build your Arusha trip
</h1>

<p class="muted">
    Choose your tour, then optionally add accommodation,
    transport and activities.
</p>

<?php if($error): ?>

<div class="toast error"
     style="position:relative;top:auto;right:auto;margin:15px 0;animation:none">
    <?=e($error)?>
</div>

<?php endif; ?>

<form method="post"
      data-builder
      data-base="<?=e((string)$tour['base_price'])?>">

<?=csrf_field()?>

<input type="hidden"
       name="tour_id"
       value="<?=$tourId?>">

<div class="steps">

<div class="step active">
    1 · Your trip
</div>

<div class="step">
    2 · Add-ons
</div>

<div class="step">
    3 · Summary
</div>

<div class="step">
    4 · Payment
</div>

</div>

<div class="builder-grid">

<div>

<div class="panel">

<div class="row-between">

<div>

<span class="tag">
    Tour
</span>

<h2 style="margin:6px 0">
    <?=e($tour['title'])?>
</h2>

<p class="muted">
    <?=e((string)$tour['duration_days'])?>
    days ·
    <?=e($tour['destination']??'Arusha')?>
</p>

</div>

<strong class="price">
    <?=money((float)$tour['base_price'])?>
</strong>

</div>

<div class="split">

<div>

<label>Travel date</label>

<input
    type="date"
    name="booking_date"
    required
    min="<?=date('Y-m-d',strtotime('+1 day'))?>">

</div>

<div>

<label>Guests</label>

<input
    type="number"
    name="guests"
    min="1"
    max="<?=$tour['max_guests']?>"
    value="2"
    required>

</div>

</div>

<div class="split">

<div>

<label>Your name</label>

<input
    name="customer_name"
    required
    value="<?=e($default['name'])?>">

</div>

<div>

<label>Email</label>

<input
    type="email"
    name="customer_email"
    required
    value="<?=e($default['email'])?>">

</div>

</div>

<label>Phone</label>

<input
    name="customer_phone"
    value="<?=e($default['phone'])?>"
    placeholder="+255 ...">

<label>Notes</label>

<textarea
    name="notes"
    placeholder="Special requests"></textarea>

</div>


<div class="panel" style="margin-top:18px">

<h2 style="margin-top:0">
    Accommodation
    <span class="muted" style="font-size:15px">
        (optional)
    </span>
</h2>

<select name="accommodation_id" id="accommodation">

<option value="0">
    I'll arrange my own accommodation
</option>

<?php foreach($accom as $a): ?>

<option
    value="<?=$a['id']?>"
    data-price="<?=$a['price_per_night']?>">

<?=e($a['name'])?>
· <?=money((float)$a['price_per_night'])?>/night

</option>

<?php endforeach; ?>

</select>

<label>Nights</label>

<input
    type="number"
    name="nights"
    min="0"
    value="0">

</div>


<div class="panel" style="margin-top:18px">

<h2 style="margin-top:0">
    Transport
    <span class="muted" style="font-size:15px">
        (optional)
    </span>
</h2>

<select name="transport_id">

<option value="0">
    I'll arrange my own transport
</option>

<?php foreach($trans as $tr): ?>

<option value="<?=$tr['id']?>">

<?=e($tr['name'])?>
· <?=money((float)$tr['price'])?>

</option>

<?php endforeach; ?>

</select>

</div>


<div class="panel" style="margin-top:18px">

<h2 style="margin-top:0">
    Activities
    <span class="muted" style="font-size:15px">
        (optional)
    </span>
</h2>

<?php foreach($acts as $a): ?>

<div class="option"
     data-price="<?=e((string)$a['price'])?>">

<div style="display:flex;gap:12px;align-items:center">

<img
    src="<?=e(media_url($a['image']))?>"
    alt="">

<div>

<strong>
    <?=e($a['name'])?>
</strong>

<div class="muted">
    <?=e((string)$a['duration_hours'])?>h ·
    <?=money((float)$a['price'])?>
    / guest
</div>

</div>

</div>

<input
    type="checkbox"
    name="activities[]"
    value="<?=$a['id']?>">

</div>

<?php endforeach; ?>

</div>

</div>


<div>

<div class="panel sticky-box">

<span class="eyebrow"
      style="color:var(--green)">
    Trip summary
</span>

<h2 style="margin:7px 0">
    Your trip
</h2>

<div class="summary-line">

<span>
    <?=e($tour['title'])?>
</span>

<strong>
    <?=money((float)$tour['base_price'])?>
    ×
    <span data-guests>2</span>
</strong>

</div>

<div class="summary-line">

<span>Accommodation</span>

<strong id="acTotal">—</strong>

</div>

<div class="summary-line">

<span>Transport</span>

<strong id="trTotal">—</strong>

</div>

<div class="summary-line">

<span>Activities</span>

<strong id="actTotal">—</strong>

</div>

<div class="summary-total">

<span>Total</span>

<span data-total>
<?=money((float)$tour['base_price']*2)?>
</span>

</div>

<label>
    Payment method
</label>

<select name="payment_method">

<option value="mobile_money">
    Mobile Money
</option>

<option value="bank">
    Bank Transfer
</option>

<option value="control_number">
    Control / Lipa Number
</option>

<option value="paypal">
    PayPal
</option>

</select>

<div class="notice">

<strong>Payment comes next.</strong>

After you create this booking,
you will be taken directly to the
payment page where you can complete
the selected payment and submit your
transaction/reference number.

</div>

<button
    class="btn btn-dark"
    style="width:100%;font-size:16px">

Continue to payment →

</button>

</div>

</div>

</div>

</form>

</section>

<script>

document.addEventListener('DOMContentLoaded',()=>{

const form=document.querySelector('[data-builder]');
const g=document.querySelector('[name=guests]');
const a=document.querySelector('[name=accommodation_id]');
const n=document.querySelector('[name=nights]');
const tr=document.querySelector('[name=transport_id]');

const prices=
<?=json_encode(array_column($trans,'price','id'))?>;

function c(){

let guests=+g.value||1;

let total=
<?=json_encode((float)$tour['base_price'])?>
*guests;

document.querySelector('[data-guests]')
    .textContent=guests;

let ap=
a.selectedOptions[0]?.dataset.price
    ? +a.selectedOptions[0].dataset.price
    : 0;

let av=
ap*(+n.value||0);

let tv=
tr.value
    ? +(prices[tr.value]||0)
    : 0;

let acts=0;

document
.querySelectorAll('.option input:checked')
.forEach(x=>{

acts+=
(+x.closest('.option').dataset.price||0)
*guests;

});

total+=av+tv+acts;

document.querySelector('[data-total]')
    .textContent=
    Wildvista.formatMoney(total);

document.getElementById('acTotal')
    .textContent=
    av
        ? Wildvista.formatMoney(av)
        : '—';

document.getElementById('trTotal')
    .textContent=
    tv
        ? Wildvista.formatMoney(tv)
        : '—';

document.getElementById('actTotal')
    .textContent=
    acts
        ? Wildvista.formatMoney(acts)
        : '—';

}

[g,a,n,tr].forEach(x=>
    x.addEventListener('change',c)
);

document
.querySelectorAll('.option input')
.forEach(x=>
    x.addEventListener('change',c)
);

c();

form?.addEventListener('submit',(event)=>{
    const date=document.querySelector('[name=booking_date]')?.value;
    const name=document.querySelector('[name=customer_name]')?.value.trim();
    const email=document.querySelector('[name=customer_email]')?.value.trim();
    if(!date || !name || !email){
        event.preventDefault();
        alert('Please complete your travel date, name and email before continuing to payment.');
        return;
    }
    const ok=confirm('Create this booking and continue to payment? You will not need to create another booking for the same tour and date.');
    if(!ok) event.preventDefault();
});

});

</script>

<?php require __DIR__.'/app/views/footer.php'; ?>