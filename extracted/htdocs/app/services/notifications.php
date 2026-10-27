<?php
declare(strict_types=1);
function queue_email(int $bookingId,string $email,string $subject,string $message): bool {
    $st=db()->prepare("INSERT INTO notifications(booking_id,recipient_email,channel,subject,message,status) VALUES(?,?,?,?,?,'queued')");
    $channel='email';$st->bind_param('issss',$bookingId,$email,$channel,$subject,$message);return $st->execute();
}
function queue_sms(int $bookingId,string $phone,string $message): bool {
    $st=db()->prepare("INSERT INTO notifications(booking_id,recipient_phone,channel,message,status) VALUES(?,?,?,?,'queued')");
    $channel='sms';$st->bind_param('isss',$bookingId,$phone,$channel,$message);return $st->execute();
}
