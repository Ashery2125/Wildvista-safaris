-- Wildvista live-database compatibility patch
-- Run this AFTER the existing Wildvista database has been imported.
-- Safe to run more than once.

INSERT INTO settings (key_name, value) VALUES
('paypal_link', ''),
('payment_mobile_money', 'Pay via Mobile Money to Wildvista: +255 621 385 106. After payment, enter your transaction/reference number below.'),
('payment_paypal', 'PayPal manual verification is available. Pay using the configured PayPal link, then enter the PayPal transaction ID below.')
ON DUPLICATE KEY UPDATE value = VALUES(value);
