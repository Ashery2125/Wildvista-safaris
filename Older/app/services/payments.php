<?php
declare(strict_types=1);
/*
 * Payment adapter boundary. Core booking does not depend on a provider.
 * Manual payment verification is active out of the box.
 * Automatic PayPal can be connected later with server-side credentials.
 */
function payment_create(array $booking, string $method): array {
    return ['ok'=>true,'status'=>'pending','provider'=>$method==='paypal'?'paypal_manual':'manual','message'=>'Payment submission is handled by the Wildvista verification flow.'];
}
function payment_verify(string $transactionRef): array {
    return ['ok'=>false,'status'=>'pending','message'=>'Automatic provider verification is not configured; admin verification is required.'];
}
