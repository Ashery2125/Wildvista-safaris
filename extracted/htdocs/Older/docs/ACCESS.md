# Wildvista access map

## Public
- `/index.php` — homepage / Arusha discovery
- `/tours.php` — tours
- `/accommodation.php` — accommodation catalogue
- `/transport.php` — transport catalogue
- `/activities.php` — activities
- `/tour.php?id=ID` — tour detail
- `/book.php?tour_id=ID` — trip builder + booking
- `/search.php?q=...` — tour search
- `/login.php` — login
- `/signup.php` — customer signup
- `/operator/apply.php` — operator application

## Admin
`/admin/index.php`

Tabs: Overview, Bookings, Listings, Users, Content.

Admin can activate operators, activate/pause listings, view bookings, mark verified payments as paid, cancel bookings, and add destination content.

## Operator
`/operator/index.php`

Tabs: Dashboard, My Tours, Accommodation & Transport, Bookings.

Operator submissions start as `pending`; admin controls activation.

## Customer
`/customer/index.php`

Shows the signed-in customer's bookings and payment states.

## Integration boundaries
- Payment: `app/services/payments.php`
- Notifications: `app/services/notifications.php`

## Sample login
All three demo accounts use:

`Wildvista@2026`
