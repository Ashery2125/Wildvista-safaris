# Wildvista V2 — Arusha Travel Platform

Wildvista is a responsive PHP/MySQL travel booking platform focused on **Arusha, Tanzania**. The package is designed as a deployable starter for InfinityFree and includes the public experience, customer booking flow, operator portal, admin control centre, database seed, local visual assets, loading/hover animations, and provider-neutral integration points for payments/email/SMS.

## What is included

- Public homepage with Wildvista branding and Arusha-first visual direction
- Tours, accommodation, transport and activities directories
- Active vs pending listing workflow
- Trip builder with live total calculation
- Optional accommodation and transport selection
- Guest/customer booking flow
- Booking references and Pending Payment state
- Customer dashboard
- Operator application and operator portal
- Operator submission of tours, accommodation and transport
- Admin dashboard
- Admin approval/activation of users and listings
- Admin booking/payment status controls
- Audit log foundation
- Email/SMS notification queue foundation
- Provider-neutral payment integration foundation
- Responsive CSS for desktop/tablet/mobile
- Original local SVG scenic assets + Wildvista logo assets
- Sample Arusha data and demo accounts
- SQL schema + seed data
- One-time setup helper for writing InfinityFree DB credentials

## Demo accounts

Password for all demo accounts: `Wildvista@2026`

| Role | Name | Email |
|---|---|---|
| Admin | Akily | akilykaaya@gmail.com |
| Operator | Patrick | patrick@wildvista.test |
| Customer | Aqil | aqil@wildvista.test |

Change the demo passwords immediately after deployment.

## Access

Public site: `https://YOUR-DOMAIN/`

Admin: `https://YOUR-DOMAIN/admin/index.php`

Operator: `https://YOUR-DOMAIN/operator/index.php`

Customer: `https://YOUR-DOMAIN/customer/index.php`

Operator application: `https://YOUR-DOMAIN/operator/apply.php`

One-time setup: `https://YOUR-DOMAIN/setup.php`

## InfinityFree deployment

InfinityFree currently advertises PHP 8.3, MySQL 8.0 / MariaDB 11.4, free SSL and full `.htaccess` support. Website files should be uploaded into the site's `htdocs` directory (main or domain-specific). See the official hosting site/docs for current control-panel details.

### 1. Create hosting + database

Create your InfinityFree hosting account, add your domain/subdomain, then create a MySQL database from the hosting control panel. Use the database hostname, database name, username and password shown by InfinityFree.

### 2. Upload the package

Extract the ZIP on your computer and upload the **contents of `wildvista_v2/`** into the site's `htdocs` folder.

Do not upload the ZIP file itself as the site's content. InfinityFree requires site files in the appropriate `htdocs` directory.

### 3. Run setup once

Open:

`https://YOUR-DOMAIN/setup.php`

Enter the exact MySQL credentials from InfinityFree. The setup page writes `app/config/config.php` and imports `database/schema_seed.sql`.

### 4. Delete setup.php

After seeing the “Setup completed” message, delete `setup.php` from the server.

### 5. Open the website

Go to your domain and test:

- homepage
- a tour
- booking builder
- customer login
- operator login
- admin login

## Database structure

The main tables are:

- `users`
- `destinations`
- `tours`
- `accommodations`
- `transports`
- `activities`
- `bookings`
- `booking_items`
- `payments`
- `notifications`
- `audit_logs`
- `settings`

The booking structure intentionally separates the core booking from its selected items so later additions do not require rebuilding the booking table.

## How the system works

### Customer

1. Customer explores Arusha.
2. Chooses a tour.
3. Selects date and guests.
4. Optionally selects accommodation.
5. Optionally selects transport.
6. Optionally selects activities.
7. Wildvista calculates the total.
8. Customer chooses a payment method placeholder.
9. Booking is created as `pending_payment`.
10. Admin can verify payment and mark the booking as confirmed.

### Operator

1. Operator applies.
2. Admin activates the account.
3. Operator enters portal.
4. Operator submits tours and optional services.
5. New submissions start as `pending`.
6. Admin activates them.
7. Active listings become selectable by customers.

### Admin

1. Admin reviews users.
2. Admin activates operators.
3. Admin reviews/activates listings.
4. Admin sees bookings.
5. Admin marks verified payments as paid.
6. Confirmed bookings become visible as confirmed to the relevant customer/operator.

## Payment integration

The current build is **provider-neutral**. The booking engine already stores a selected method such as mobile money, bank, card or control/Lipa number. Real provider API credentials and webhooks are intentionally not hardcoded into the ZIP.

Use `app/services/payments.php` as the integration boundary.

## Notifications

Email and SMS are modeled as notification channels. They are stored in the `notifications` table. Production provider credentials can later be connected without changing the booking tables.

Use `app/services/notifications.php` as the integration boundary.

## Visual assets

The interface uses local SVG assets so the site does not depend on external image CDNs for its initial visual presentation. This keeps the package portable and makes the background/cards/hero work immediately after upload.

## Important production notes

This package is **production-oriented starter code**, not a claim that all tourism, tax, payment-provider, data-protection or other regulatory requirements have been completed. Before commercial launch, configure the appropriate business/legal/operator requirements, payment provider, production email/SMS service, backups, and security hardening.

Do not keep `setup.php` on the live server.

Do not keep demo passwords in production.


## V2.1 live payment flow

A booking is created immediately and stays `pending_payment`. The customer is sent to `payment.php?ref=...` and can choose Mobile Money, Bank Transfer, Control/Lipa Number, or PayPal manual verification. They submit a transaction/reference number and optional proof image/PDF. Admin sees it under **Payments & Verification** and can Approve or Reject. Approval changes the payment to `paid` and booking to `confirmed`.

Payment instructions are configured by Admin → Settings. Real payment-provider API credentials are intentionally not bundled. PayPal Client ID/Secret fields are server-side settings for a future automatic adapter; manual PayPal transaction verification works without API credentials.

## Images

The package now uses local photographic demo assets for hero/cards instead of icon-only cards. They are derived from the Wildvista visual concept board and are **demo visuals**. Before commercial launch, replace them with photos you own, have licensed, or have permission to use. The database `image` field stores the local filename.

## Portal UI

Admin/Operator/Customer dashboards use a separate responsive portal shell and no longer inherit the public website header. Admin has its own sidebar/topbar command-centre layout.

### Operator image upload
Operators can upload JPG/PNG/WEBP images when adding a tour, accommodation or transport. Uploaded files are stored in `assets/uploads/` and the database stores the relative filename. Sample data uses bundled demo visuals.
