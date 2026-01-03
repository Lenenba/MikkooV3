# Mobile API expose (proposition)

Contexte
- La plateforme utilise Inertia + routes web (routes/web.php, routes/auth.php, routes/settings.php).
- Il n'y a pas de routes JSON publiques (pas de routes/api.php).
- Ce document propose une API REST JSON pour une app mobile, basee sur les controllers existants.

Principes techniques
- Base URL: /api/v1
- Auth: token Bearer (Laravel Sanctum recommande).
- Errors: 422 avec {"message": "...", "errors": {...}} pour la validation.
- Pagination: renvoyer data + meta (ou schema Laravel standard).
- Roles: parent, babysitter, admin/superadmin (controle par policies/middlewares).

Modeles (champs principaux)
- User: id, name, email, role, email_verified_at, created_at
- Address: street, city, province, postal_code, country, latitude, longitude
- Service: id, name, description, price, user_id
- Reservation: id, number, status, parent, babysitter, services, details, total_amount, notes
- Announcement: id, title, service, services[], children[], schedule*, status, applications_count
- Invoice: id, number, status, currency, subtotal_amount, tax_amount, total_amount, period_start/end, items[]
- Media: id, url, collection_name, mime_type, is_profile

Auth
- POST /api/v1/auth/register
  payload: first_name, last_name, email, password, password_confirmation, role (parent|babysitter)
  response: { user, token }
- POST /api/v1/auth/login
  payload: email, password
  response: { user, token }
- POST /api/v1/auth/logout (auth required)
- GET /api/v1/me (auth required)
- POST /api/v1/auth/forgot-password (email)
- POST /api/v1/auth/reset-password (token, email, password, password_confirmation)
- POST /api/v1/auth/email/verification-notification (auth, resend)
- GET /api/v1/auth/verify-email/{id}/{hash} (signed link)

Onboarding
- GET /api/v1/onboarding (auth)
  response: { step, role, account, address, profile }
- POST /api/v1/onboarding/profile (auth)
  babysitter: bio, experience, price_per_hour, payment_frequency, services
  parent: children[], preferences
- GET /api/v1/onboarding/address/search?query=...
  response: { results: [...] }
- POST /api/v1/onboarding/address (auth)
  payload: street, city, province, postal_code, country, latitude, longitude
- POST /api/v1/onboarding/availability (auth)
  payload: availability, availability_notes
- POST /api/v1/onboarding/finish (auth)

Recherche babysitter
- GET /api/v1/search/babysitters
  query: name, city, country, service, min_price, max_price, min_rating, payment_frequency, sort
  response: { data: [...], meta: {...}, serviceOptions: [...] }
- GET /api/v1/services/search?query=...&babysitter_id=...
  response: [ { id, name, description, price } ]

Reservations
- GET /api/v1/reservations (auth)
  response: pagination des reservations pour l'utilisateur
- GET /api/v1/reservations/{id} (auth)
- POST /api/v1/reservations (auth, parent)
  payload (existant): babysitter_id, schedule_type, start_date, start_time, end_time,
  recurrence_*, services[{id, quantity}], notes
- POST /api/v1/reservations/{id}/accept (auth, babysitter)
- POST /api/v1/reservations/{id}/cancel (auth)
- POST /api/v1/reservations/{id}/complete (auth, babysitter)
- POST /api/v1/reservations/{id}/ratings (auth)
  payload: rating (1-5), comment?

Announcements (parent / babysitter)
- GET /api/v1/announcements
  parent: ses annonces
  babysitter: annonces ouvertes qui matchent ses services
- POST /api/v1/announcements (auth, parent)
  payload: title, services[]|service, child_ids[], child_notes, description, location,
  schedule_type, start_date, start_time, end_time, recurrence_*
- GET /api/v1/announcements/{id}
  parent/admin: annonce + applications[]
  babysitter: annonce + myApplication
- PATCH /api/v1/announcements/{id} (auth, parent) payload: status (open|closed)
- DELETE /api/v1/announcements/{id} (auth, parent)
- POST /api/v1/announcements/{id}/apply (auth, babysitter) payload: message?
- POST /api/v1/announcements/{id}/applications/{application}/accept (auth, parent)
- POST /api/v1/announcements/{id}/applications/{application}/reject (auth, parent)
- POST /api/v1/announcements/{id}/applications/{application}/withdraw (auth, babysitter)

Services babysitter
- GET /api/v1/settings/services (auth, babysitter)
  response: { services[], catalog[], kpis }
- POST /api/v1/settings/services (auth, babysitter)
  payload: name, description?, price
- PATCH /api/v1/settings/services/{id} (auth, babysitter)
- DELETE /api/v1/settings/services/{id} (auth, babysitter)

Profil & compte
- GET /api/v1/settings/profile (auth)
  response: role, parentProfile?, babysitterProfile?, address?, media[]
- PATCH /api/v1/settings/profile (auth)
  payload: name, email
- PATCH /api/v1/settings/parent/profile (auth, parent)
  payload: first_name, last_name, birthdate, phone, address{...}, preferences?, availability?, children?
- PATCH /api/v1/settings/babysitter/profile (auth, babysitter)
  payload: first_name, last_name, birthdate, phone, bio?, experience?, price_per_hour,
  payment_frequency, address{...}, services?, availability?
- PUT /api/v1/settings/password (auth)
  payload: current_password, password, password_confirmation

Media
- GET /api/v1/settings/media (auth)
- POST /api/v1/settings/media (auth, multipart)
  payload: collection_name, images[]
- POST /api/v1/settings/media/set-profile (auth) payload: media_id
- DELETE /api/v1/settings/media/{id} (auth)

Invoices
- GET /api/v1/invoices (auth)
- GET /api/v1/invoices/{id} (auth)
- PATCH /api/v1/invoices/{id} (auth, babysitter, status=draft)
  payload: items[{id, description, service_date?, quantity, unit_price}]
- GET /api/v1/invoices/{id}/download (auth, PDF)

Dashboard
- GET /api/v1/dashboard (auth)
  response: { dashboard: { role, stats, kpis }, announcements: { items, suggestions } }

Notes de mise en place
- Creer routes/api.php + controllers API ou adapters JSON des controllers actuels.
- Appliquer auth:sanctum + policies identiques aux routes web.
- Standardiser les formats date/heure (ISO 8601) pour mobile.
- OpenAPI: docs/openapi.yaml
