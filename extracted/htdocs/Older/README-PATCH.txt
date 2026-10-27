WILDVISTA V2.2 - TARGETED PATCH ONLY

Do not replace the whole Wildvista project.
Copy these files into the existing live project, keeping the same folder paths.

MAIN FILES:
- admin/index.php
- app/views/portal_header.php
- app/views/header.php
- app/views/footer.php
- assets/css/app.css
- assets/js/app.js
- index.php
- payment.php
- book.php
- login.php
- signup.php

DATABASE:
1. Run database/update_v22.sql once in phpMyAdmin.
2. Only if payment columns are missing from your live payments table, run database/payment_columns_if_missing.sql.

CHANGES:
- Admin portal uses the separate dark-sidebar/cream command-centre layout and CSS cache-busting.
- Homepage hero rotates photographic backgrounds automatically every 6.5 seconds with fade/zoom and a local fallback.
- Manual mobile-money payment displays +255 621 385 106.
- PayPal manual payment is supported: customer opens the configured PayPal payment link, pays, then submits the PayPal transaction ID for admin verification.
- Admin -> Settings now has a PayPal payment-link field.
- Customer registration is explicitly linked from the public header and login page.
- Booking payment-method list is aligned with the payment page; the unsupported card placeholder was removed.

PAYPAL:
The actual PayPal account/payment link is not invented. Put the real PayPal.Me/payment URL in Admin -> Settings -> PayPal payment link. Automatic PayPal API checkout is not enabled until real PayPal credentials are supplied.
