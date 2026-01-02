# I18N Inventory (initial)

## UI pages by feature
- Core: resources/js/pages/Welcome.vue, resources/js/pages/Dashboard.vue
- Auth: resources/js/pages/auth/*.vue, resources/js/layouts/auth/*.vue
- Onboarding: resources/js/pages/onboarding/Index.vue
- Search: resources/js/pages/search/Index.vue
- Reservations: resources/js/pages/reservation/Index.vue, resources/js/pages/reservation/Create.vue, resources/js/pages/reservation/Show.vue
- Invoices: resources/js/pages/invoices/Index.vue, resources/js/pages/invoices/Show.vue
- Announcements: resources/js/pages/announcements/Index.vue, resources/js/pages/announcements/Show.vue
- Settings: resources/js/pages/settings/*.vue
- Superadmin: resources/js/pages/superadmin/Consultations.vue

## UI components with visible text (sample)
- resources/js/components/AppSidebar.vue
- resources/js/components/AppLogo.vue
- resources/js/components/AppLogoIcon.vue
- resources/js/components/Reservation/*
- resources/js/components/BabysitterList.vue
- resources/js/components/ui/*

## Backend user-facing text
### Controllers (flash/status)
- app/Http/Controllers/AcceptReservationController.php
- app/Http/Controllers/AnnouncementApplicationController.php
- app/Http/Controllers/AnnouncementController.php
- app/Http/Controllers/CancelReservationController.php
- app/Http/Controllers/CompleteReservationController.php
- app/Http/Controllers/InvoiceController.php
- app/Http/Controllers/ReservationController.php
- app/Http/Controllers/ReservationRatingController.php
- app/Http/Controllers/Settings/BabysitterProfileController.php
- app/Http/Controllers/Settings/BabysitterServicesController.php
- app/Http/Controllers/Settings/DeleteProfilePhotoController.php
- app/Http/Controllers/Settings/MediaController.php
- app/Http/Controllers/Settings/ParentProfileController.php
- app/Http/Controllers/Settings/SetAsProfilePhotoController.php
- app/Http/Controllers/Auth/*

### Requests/validation
- app/Http/Requests/Auth/LoginRequest.php
- app/Http/Requests/ReservationRequest.php
- app/Http/Requests/Settings/BabysitterProfileRequest.php
- app/Http/Requests/Settings/ParentProfileRequest.php
- app/Http/Requests/Settings/ProfileUpdateRequest.php

### Notifications + mail
- app/Notifications/*.php
- resources/views/emails/**/*.blade.php

### Other views
- resources/views/invoices/pdf.blade.php
- resources/views/reservations/confirmed.blade.php
- resources/views/app.blade.php

## Notes
- No lang directory exists yet; translations will be introduced in step 4.
- This is an initial inventory; more strings exist inside the files above.
