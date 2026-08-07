# Community Sharing Platform Implementation Plan

> **For Hermes:** Use subagent-driven-development skill to implement this plan task-by-task.

**Goal:** Build a modular Laravel + Velzon Laravel+Vue web platform and a single Flutter mobile app for the `orezone` community ride-sharing MVP, designed to expand later into a multi-service community marketplace.

**Architecture:** Start with a monolithic Laravel application with clean module boundaries, versioned REST APIs, and the supplied Velzon Laravel+Vue template as the only web UI foundation. The Flutter app should be capability-based, not role-based, so the same user can later become a host, traveler, service provider, or marketplace seller without creating a new account.

**Tech Stack:** Laravel, Vue 3, Inertia, Vite, Bootstrap 5, Velzon Laravel+Vue template, MySQL/PostgreSQL, Redis, Sanctum, Flutter, optional Firebase Cloud Messaging, optional Google Maps/Mapbox, private storage for documents.

## 0. Mandatory Product and UI Constraints

These constraints apply to every phase and override older recommendations elsewhere in this document.

- Product brand: `orezone`. Replace all Velzon/demo/template branding in user-facing web and mobile surfaces with `orezone`.
- Canonical web UI source: `/www/wwwroot/template/velzone/Laravel+Vue/modern`.
- The duplicated path supplied in the brief resolves to the canonical path above; use the existing Laravel+Vue project at that location.
- Use the Velzon template's existing layout, navigation, cards, tables, forms, modals, badges, charts, spacing, responsive behavior, icons, and theme variables. Extend existing components before creating new visual patterns.
- Do not introduce Filament, a second admin template, Tailwind UI, Material UI, a custom design system, or unrelated UI kits.
- Do not copy DriveMonD screens or styling. DriveMonD may inform domain workflows only; it is not a UI source.
- The Flutter app must use the same Velzon-derived color tokens and visual tone. Define shared documented color values from the template before implementing Flutter screens; do not invent a separate mobile palette.
- Phase 1 starts with the public `orezone` landing page. Backend modules and authenticated screens follow only after the landing-page phase passes its gate.
- Every phase must end with tests, manual verification, bug fixing, and a recorded phase gate before the next phase begins.

## 0.1 Phase Execution Protocol

For every phase:

1. Define the phase scope, acceptance criteria, affected web/mobile/API areas, and test cases before implementation.
2. Implement only the approved phase scope using the Velzon UI and `orezone` branding.
3. Run applicable automated tests, lint/build checks, responsive checks, accessibility checks, and manual user-flow checks.
4. Fix all discovered bugs and rerun the failing and full relevant test suites.
5. Record the result, remaining risks, screenshots or verification notes, and the exact approval gate.
6. Do not start the next phase until the current phase's acceptance criteria and regression checks pass.

## 0.2 Required Phase Order

1. Phase 1: `orezone` public landing page using the Velzon Laravel+Vue template.
2. Phase 2: Laravel foundation, authentication, profiles, and API contracts.
3. Phase 3: Host verification, vehicle documents, and admin operational screens.
4. Phase 4: Trip creation, search, trip details, and booking workflows.
5. Phase 5: Wallet ledger, platform fee, ratings, moderation, safety, and notifications.
6. Phase 6: Flutter app implementation using the shared Velzon-derived theme.
7. Phase 7: Integrated end-to-end QA, security hardening, performance checks, and release preparation.

Phase 1 is the first implementation deliverable and must not be skipped or merged into a later backend phase.

## 0.3 Phase 1 Landing Page Scope and Gate

Phase 1 builds only the public `orezone` landing page using the Velzon Laravel+Vue template. It must establish the product's web visual baseline before application workflows are added.

Required landing-page content:

- `orezone` logo/wordmark treatment using the template's branding locations
- hero section explaining community seat-sharing
- primary calls to action for finding a trip and offering a trip
- concise host and traveler value propositions
- how-it-works section
- trust, verification, safety, and transparent-fee messaging
- responsive navigation and footer using the template shell
- placeholder or approved product imagery that does not retain Velzon/demo branding
- mobile-responsive layout at phone, tablet, and desktop widths

Phase 1 must not include real booking, wallet, OTP, map, or admin business logic. CTAs may point to controlled placeholder routes or clearly marked coming-soon states until later phases implement them.

Phase 1 completion gate:

- Laravel/Vue production build succeeds from the target project
- no Velzon/demo product name remains in rendered user-facing content
- landing page renders correctly at mobile, tablet, and desktop breakpoints
- navigation, CTA, footer, and all interactive elements have verified behavior
- automated page/component tests cover the main content and CTA states
- accessibility checks cover headings, contrast, keyboard focus, labels, and image alternatives
- manual visual comparison confirms the Velzon layout and theme are being used rather than a new UI system
- all defects found during validation are fixed and the checks are rerun before Phase 2 starts

---

# 1. Decisions Already Made

Based on the initial product brief, the implementation should assume:

- One user account for all future services.
- Capabilities and verification status instead of fixed user roles.
- One wallet per user for platform fees.
- Travel expenses settled directly between traveler and host.
- Platform service fee deducted after booking confirmation.
- Mobile OTP as the base authentication method.
- Phase 1 scope limited to community ride sharing only.
- Future expansion into food, home services, local marketplace, delivery, and emergency services.

---

# 2. Reference Source to Borrow Ideas From

Use the existing DriveMonD project as a reference for domain workflow patterns only:

`/www/wwwroot/template/Drivermond/drivemond/combo-v3.2/new`

Useful patterns to study:

- Module structure and domain separation
- Trip management flow
- Wallet/payment flow
- Driver/customer split patterns, adapted to host/traveler capabilities
- Real-time notification/broadcast flow
- Admin operational workflows, implemented in Velzon Laravel+Vue

Do not reuse DriveMonD visual styling, layouts, components, assets, or branding.

## 2a. Reference Project Investigation Findings

From the DriveMonD v3.2 codebase, these patterns should influence the new implementation:

Reference stack identified:
- Laravel 12 + PHP 8.2
- nwidart/laravel-modules for modular monolith structure
- Laravel Passport for API auth
- Laravel Reverb + Pusher for real-time broadcasts
- Firebase Cloud Messaging for push notifications
- Blade + Bootstrap admin UI with jQuery-style interactions
- Multiple payment gateway abstractions
- separate customer API layer and driver API layer for the same domain

This is useful because it proves the reference project already has:
- strong domain separation
- operational admin workflows
- dual real-time + push notification architecture
- wallet/account ledger patterns
- separate mobile API surfaces for different user types

### Backend patterns worth reusing conceptually

1. Use domain-separated Laravel modules for trip, user, vehicle, transaction, and admin concerns.
2. Keep trip state transitions explicit and audited.
3. Use a wallet/account model with separate ledger/account tables rather than only a single balance field.
4. Support both web and API entry points for the same domain.
5. Use broadcast channels / real-time events for trip status changes and chat-like updates.
6. Keep admin workflows strong around document review, approvals, refunds, safety alerts, and operational overrides.
7. Use config/settings modules for business rules rather than hardcoding everything inside controllers.

### Specific backend patterns worth copying conceptually

1. Use repository + service + interface layers to keep controllers thin.
2. Use explicit status constants for trip and booking state machines.
3. Store status history as a separate timeline entity, not only a status column.
4. Use dual balance/accounting models where needed: separate wallet balance from payable/receivable/accounting states.
5. Use API resource transformers for consistent mobile response shapes.
6. Use observers or service-layer hooks for audit logging and status-triggered actions.
7. Use admin config/settings tables for business rules that ops teams should change without code deployments.

### Admin UI patterns to borrow

1. Trip screens should show timeline/status history, inline actions, and support print/export where useful.
2. Vehicle/document review screens should separate request list, request detail, approve/reject action, and mail/notification trigger.
3. Refund/safety screens should be separate operational modules rather than hidden inside trip detail pages.
4. Use separate admin lists for active vs trashed records.
5. Use consistent inline menu patterns for approve/reject/history actions.

### Specific admin workflow patterns worth copying

1. Build separate operational screens for verification review, document review, refund review, and safety alerts.
2. Use detail pages with tabbed views: overview, status history, actions, documents, and related records.
3. Keep destructive actions behind confirmation modals or explicit review steps.
4. Support export/print for admin records where operations staff need offline workflows.
5. Keep active and archived/trashed data in separate views.

### Mobile UX patterns worth studying

1. Use a state-machine style ride flow with clear phases like search/request/accept/ongoing/complete.
2. Use bottom navigation with 4-5 primary tabs.
3. Use wallet as a dedicated section with balance card + transaction list.
4. Use safety setup screens with emergency contact management and SOS trigger.
5. Use notification badge handling for driver/customer trip events.
6. Use language/locale selection early in onboarding.

### Specific mobile UX patterns worth copying

1. Use an expandable bottom-sheet-driven map/search experience for trip creation and trip details.
2. Use swipe-to-confirm or explicit confirmation buttons for irreversible actions.
3. Use shimmer loading + paginated lists for search results, notifications, transactions, and histories.
4. Use dedicated wallet screens with balance card, action buttons, voucher/promo input, and filterable transaction history.
5. Use safety/alert screens with predefined reasons, countdown delay, and emergency contact triggers.
6. Use deep link handling so push notifications open the correct trip/booking/payment screen.

### Important adaptation note

DriveMonD uses a driver/customer split with real-time dispatch-style flows.
The new community app must adapt those ideas into a host/traveler, trip-based, seat-sharing model instead of a direct ride-hail dispatch model.

### What to reject or rework from the reference project

1. Rejected: driver/customer binary role model.
Accepted replacement: capability-based identity with separate verification states.

2. Rejected: dispatch-first ride-matching UX.
Accepted replacement: trip-first seat-sharing UX with origin/destination/departure/search.

3. Rejected: fare bidding as default pricing model.
Accepted replacement: simple platform fee model with direct host/traveler expense settlement.

4. Rejected: complex multi-gateway payment settlement in Phase 1.
Accepted replacement: limited wallet + platform fee deduction only.

5. Rejected: heavy backend helper functions spread across global helpers.
Accepted replacement: cleaner action/service classes with testable boundaries.

This plan should not copy that project directly, but should extract reusable UX and architecture patterns.

---

# 3. Product Scope for Phase 1

Build only the community ride-sharing MVP.

## 3.1 User Features

- Mobile OTP registration and login
- Profile setup
- Profile photo upload
- Language preference
- Emergency contacts
- Basic rating display
- Wallet balance
- Wallet transaction history

## 3.2 Host Features

- Add vehicle
- Upload driving license
- Upload RC book
- Upload insurance
- Upload vehicle photos
- Submit host verification
- Create trip
- Add origin and destination
- Set available seats
- Choose booking mode
- Accept/reject bookings
- Start and complete trip
- View trip history

## 3.3 Traveler Features

- Search trips
- Filter by date, location, seats, time
- View trip details
- View host profile and ratings
- Request booking
- View booking status
- Cancel booking
- View booking history
- Rate host after trip completion

## 3.4 Admin Features

- Dashboard
- User management
- Host verification
- Vehicle verification
- Trip management
- Booking management
- Wallet and fee ledger
- Platform fee configuration
- Cancellation policy configuration
- Ratings moderation
- Report/complaint handling
- SOS review
- Audit logs
- Notification management

---

# 4. Reference Architecture

## 4.1 Laravel Backend

This architecture is influenced by the reference project, but redesigned for community seat-sharing instead of taxi-style dispatch.

Recommended implementation style per module:
- Config/config.php for module settings
- Database/Migrations for schema
- Entities or Models for domain objects
- Http/Controllers with separate namespaces for API and admin/web where needed
- Http/Requests for validation
- Providers for repository/service bindings
- Repository + Interface for data access
- Service + Interface for business logic
- Resources/views for admin Blade screens
- Transformers/API Resources for mobile responses

Recommended cross-cutting concerns:
- explicit status constants
- separate status-history tables for timeline auditing
- form requests at API boundaries
- API resources for stable mobile contracts
- jobs/events for notifications
- admin audit logging for sensitive changes

### 4.1.1 Recommended Module Boundaries

```
app/Modules/
├── Identity
├── Verification
├── Wallet
├── RideSharing
├── Ratings
├── Safety
└── Notifications
```

### 4.1.2 Recommended Domain Groups

Identity:

- Users
- Profiles
- Devices
- Capabilities
- OTP/verification codes
- Consents

Verification:

- Verification requests
- Verification documents
- Review status
- Expiry tracking

Wallet:

- Wallets
- Ledger entries
- Top-ups
- Platform fee deduction
- Refunds
- Admin adjustments

RideSharing:

- Vehicles
- Trips
- Trip stops
- Bookings
- Search/matching
- Trip status transitions
- Cancellation rules

Ratings:

- Reviews
- Reported users
- Blocked users

Safety:

- Emergency contacts
- SOS alerts
- Incident logs

Notifications:

- Push notifications
- In-app notifications
- Email notifications
- SMS notifications
- Broadcast channels

## 4.2 Velzon Laravel+Vue Web UI

Recommended navigation structure:

- Dashboard
- Users
- Verification
  - Host Verification
  - Vehicle Documents
- Ride Sharing
  - Vehicles
  - Trips
  - Bookings
- Finance
  - Wallets
  - Ledger
  - Platform Fees
- Trust and Safety
  - Ratings
  - Reports
  - SOS
- Notifications
- CMS / Settings

All sensitive admin operations, audit-logged actions, document review, and wallet adjustments must be implemented inside the supplied Velzon Laravel+Vue layout and its existing Vue/Inertia components.

The web UI implementation must:

- start from `/www/wwwroot/template/velzone/Laravel+Vue/modern`
- preserve the template's layout shell and responsive navigation
- reuse the template's Bootstrap components and theme variables
- replace demo routes, labels, assets, and branding with `orezone` content
- add domain screens as Vue/Inertia pages and reusable components within the template structure
- keep admin and public landing-page screens visually consistent with the same template tokens

## 4.3 Flutter App

Recommended state management: Riverpod or Bloc.  
Recommended routing: GoRouter.  
Recommended networking: Dio.  
Recommended models: Freezed + json_serializable.  
Recommended storage: flutter_secure_storage.  
Recommended maps: Google Maps or Mapbox.

### Flutter Theme Requirement

Before implementing Flutter screens, extract and document the active Velzon theme tokens from the canonical template, including primary, secondary, success, warning, danger, background, surface, text, muted text, border, and dark-mode values where applicable. Implement those values as Flutter theme constants and use them across every mobile screen.

- Flutter must visually align with the Velzon web template while remaining native to Flutter.
- Do not use a separate mobile color palette, gradients, or default Material colors that conflict with the web theme.
- Verify theme parity with screenshots or side-by-side visual review at each Flutter phase gate.

Recommended bottom navigation:

- Home
- Find Trips
- Offer Trip
- My Activity
- Profile

The Offer Trip action should be disabled until host and vehicle verification is completed.

---

# 5. Data Model Plan

## 5.1 Core Tables

### users

Use for:

- identity
- contact
- status
- authentication metadata

Recommended fields:

- id
- name
- phone
- phone_verified_at
- preferred_language
- status
- avatar_path
- last_login_at
- timestamps

### user_profiles

Use for extended profile data.

Recommended fields:

- user_id
- bio
- gender
- date_of_birth
- address
- city
- country
- latitude
- longitude
- emergency_contact_name
- emergency_contact_phone
- timestamps

### user_capabilities

Use a normalized table instead of hardcoded role flags.

Recommended fields:

- id
- user_id
- capability
- status
- granted_at
- revoked_at
- timestamps

Examples:

- can_offer_trips
- can_book_trips
- can_offer_food
- can_offer_home_services

### user_devices

Recommended fields:

- id
- user_id
- device_type
- device_token
- platform
- last_active_at
- timestamps

### user_consents

Recommended fields:

- id
- user_id
- consent_type
- version
- accepted_at
- ip_address
- timestamps

## 5.2 Verification Tables

### verification_requests

Recommended fields:

- id
- user_id
- type
- status
- submitted_at
- reviewed_at
- reviewer_id
- rejection_reason
- expires_at
- timestamps

Types:

- profile
- host_identity
- vehicle

### verification_documents

Recommended fields:

- id
- request_id
- user_id
- document_type
- file_path
- file_type
- status
- rejection_reason
- reviewed_at
- timestamps

Document types:

- driving_license
- rc_book
- insurance
- vehicle_photo
- profile_photo
- aadhaar_reference

Use private storage only. Do not serve documents from public directory.

## 5.3 Vehicle Tables

### vehicles

Recommended fields:

- id
- user_id
- vehicle_category_id
- registration_number
- brand
- model
- year
- color
- seating_capacity
- verification_status
- is_active
- timestamps

### vehicle_documents

Recommended fields:

- id
- vehicle_id
- document_type
- file_path
- status
- reviewed_at
- timestamps

## 5.4 Trip Tables

### trips

Recommended fields:

- id
- host_id
- vehicle_id
- origin_name
- origin_address
- origin_lat
- origin_lng
- destination_name
- destination_address
- destination_lat
- destination_lng
- departure_at
- estimated_arrival_at
- total_seats
- available_seats
- booking_mode
- status
- route_polyline
- notes
- timestamps

Trip statuses:

- draft
- published
- full
- in_progress
- completed
- cancelled
- expired

Booking modes:

- instant
- request_approval

### trip_stops

Recommended fields:

- id
- trip_id
- type
- name
- address
- latitude
- longitude
- sequence
- timestamps

### trip_status_history

Recommended fields:

- id
- trip_id
- status
- changed_by
- metadata
- timestamps

## 5.5 Booking Tables

### bookings

Recommended fields:

- id
- trip_id
- traveler_id
- host_id
- seat_count
- pickup_stop_id
- drop_stop_id
- status
- platform_fee
- platform_fee_tax
- total_platform_fee
- fee_snapshot
- idempotency_key
- requested_at
- accepted_at
- confirmed_at
- cancelled_at
- completed_at
- timestamps

Booking statuses:

- requested
- accepted
- rejected
- payment_pending
- confirmed
- cancelled_by_traveler
- cancelled_by_host
- completed
- no_show
- disputed

### booking_status_history

Recommended fields:

- id
- booking_id
- status
- changed_by
- metadata
- timestamps

## 5.6 Wallet Tables

### wallets

Recommended fields:

- id
- user_id
- balance
- currency
- is_active
- timestamps

### wallet_transactions

Recommended fields:

- id
- wallet_id
- user_id
- direction
- amount
- balance_before
- balance_after
- type
- status
- reference_type
- reference_id
- idempotency_key
- metadata
- timestamps

Types:

- topup
- platform_fee
- refund
- admin_adjustment
- promotional_credit
- reversal

### payment_orders

Use this for top-up attempts and payment gateway reconciliation.

Recommended fields:

- id
- user_id
- amount
- currency
- provider
- provider_order_id
- provider_payment_id
- status
- metadata
- timestamps

## 5.7 Ratings Tables

### reviews

Recommended fields:

- id
- reviewer_id
- reviewee_id
- trip_id
- booking_id
- rating
- comment
- is_visible
- timestamps

### reports

Recommended fields:

- id
- reporter_id
- reported_user_id
- trip_id
- booking_id
- reason
- description
- status
- reviewed_by
- reviewed_at
- timestamps

### blocked_users

Recommended fields:

- id
- user_id
- blocked_user_id
- timestamps

## 5.8 Safety Tables

### emergency_contacts

Recommended fields:

- id
- user_id
- name
- phone
- relation
- timestamps

### sos_alerts

Recommended fields:

- id
- user_id
- trip_id
- latitude
- longitude
- message
- status
- reviewed_by
- reviewed_at
- timestamps

## 5.9 Notifications Tables

### notifications

Recommended fields:

- id
- user_id
- type
- title
- body
- data
- read_at
- timestamps

### notification_templates

Recommended fields:

- id
- type
- channel
- title_template
- body_template
- is_active
- timestamps

## 5.10 Audit Logs

### audit_logs

Recommended fields:

- id
- user_id
- auditable_type
- auditable_id
- action
- old_values
- new_values
- ip_address
- user_agent
- timestamps

---

# 6. Critical Workflow Plans

## 6.1 Authentication Flow

1. User enters phone number
2. System creates or retrieves user
3. OTP is sent
4. OTP is verified
5. Device token is saved
6. Sanctum token is issued
7. Profile completion state is checked
8. Host verification state is checked before offering a trip

## 6.2 Host Onboarding Flow

1. User enables "Offer Trip"
2. System checks profile completeness
3. User adds vehicle details
4. User uploads driving license, RC book, insurance, vehicle photos
5. System creates verification request
6. Admin reviews and approves or rejects
7. If approved, capability `can_offer_trips` is granted
8. User can now publish trips

## 6.3 Trip Creation Flow

1. Host selects origin and destination
2. Host adds stops if supported
3. Host sets departure time and seats
4. Host chooses booking mode
5. Host publishes trip
6. Trip appears in traveler search
7. Seats are reduced only after successful booking confirmation

## 6.4 Booking Flow

### Request Approval Mode

1. Traveler requests seats
2. Booking is created in `requested` status
3. Host accepts or rejects
4. If accepted, booking moves to `payment_pending`
5. System checks wallet balance
6. If balance sufficient, platform fee is deducted atomically
7. Booking becomes `confirmed`
8. Available seats are updated
9. Notifications are sent

### Instant Booking Mode

1. Traveler requests seats
2. System immediately checks wallet balance
3. Platform fee is deducted atomically
4. Booking becomes `confirmed`
5. Available seats are updated
6. Notifications are sent

## 6.5 Cancellation Flow

Create separate rules for:

- traveler before host acceptance
- traveler after confirmation
- host after confirmation
- host after trip start
- no-show handling

Each rule must define:

- platform fee outcome
- refund or credit outcome
- penalty outcome
- notification outcome

Store cancellation policies in a configurable settings table, not hardcoded only in code.

## 6.6 Wallet Transaction Flow

Every financial operation must:

1. Start a DB transaction
2. Lock wallet row
3. Calculate balance before
4. Create ledger entry
5. Update balance
6. Store idempotency key
7. Commit transaction
8. Send notification asynchronously

Use immutable ledger entries. Never delete financial records.

## 6.7 Safety / SOS Flow

1. User triggers SOS
2. App captures location
3. System creates SOS alert
4. App notifies emergency contact and admin
5. Admin sees alert in the Velzon `orezone` admin dashboard
6. Admin updates status to reviewed / resolved

---

# 7. API Surface Plan

Use versioned API:

```
/api/v1
```

## 7.1 Auth API

- POST /auth/send-otp
- POST /auth/verify-otp
- POST /auth/logout
- GET /auth/me

## 7.2 Profile API

- GET /profile
- PATCH /profile
- POST /profile/avatar

## 7.3 Verification API

- GET /verification
- POST /verification/documents
- GET /verification/documents
- DELETE /verification/documents/{document}

## 7.4 Vehicle API

- GET /vehicles
- POST /vehicles
- GET /vehicles/{vehicle}
- PATCH /vehicles/{vehicle}
- DELETE /vehicles/{vehicle}
- POST /vehicles/{vehicle}/submit-verification

## 7.5 Trip API

- GET /trips/search
- POST /trips
- GET /trips/{trip}
- PATCH /trips/{trip}
- POST /trips/{trip}/publish
- POST /trips/{trip}/cancel
- POST /trips/{trip}/start
- POST /trips/{trip}/complete
- GET /trips/my
- GET /trips/{trip}/booking-requests

## 7.6 Booking API

- POST /trips/{trip}/bookings
- GET /bookings
- GET /bookings/{booking}
- POST /bookings/{booking}/accept
- POST /bookings/{booking}/reject
- POST /bookings/{booking}/cancel
- POST /bookings/{booking}/complete

## 7.7 Wallet API

- GET /wallet
- GET /wallet/transactions
- POST /wallet/topups

## 7.8 Ratings API

- POST /ratings
- GET /users/{user}/ratings

## 7.9 Safety API

- POST /reports
- POST /users/{user}/block
- POST /sos
- GET /notifications

---

# 8. Admin Web UI Plan

## 8.1 Velzon Laravel+Vue Admin Screens to Create

- Users screen
- Verification requests screen
- Vehicles screen
- Trips screen
- Bookings screen
- Wallets screen
- Wallet transactions screen
- Reviews screen
- Reports screen
- SOS alerts screen
- Notifications screen
- Settings screen

## 8.2 Admin Operational Flows

Verification review workflow:

1. Open pending verification list
2. Open request
3. Review documents
4. Approve or reject with reason
5. Update user capability
6. Log action

Booking support workflow:

1. Search by traveler, host, trip, or booking ID
2. Open booking details
3. View timeline and ledger entry
4. Create manual adjustment if required
5. Log adjustment and notify user

SOS workflow:

1. Open SOS dashboard view
2. Review latest alerts
3. Update status
4. Add resolution notes
5. Optionally suspend or restrict user

## 8.3 Admin Design Principles

Use the supplied Velzon Laravel+Vue UI as the visual source of truth. Do not introduce Notion/Linear styling or another visual language.

Admin screens should include:

- clear filters
- status badges
- timeline/history views
- document preview
- manual override controls
- audit trail for sensitive actions

---

# 9. Flutter App Plan

## 9.1 Screen Groups

Onboarding:

- Splash
- Language selection
- Phone login
- OTP
- Profile setup

Home and search:

- Home
- Search form
- Search results
- Trip details
- Host profile
- Booking request screen

Host flow:

- Offer Trip wizard
- Vehicle selector
- Host verification flow
- Uploaded documents screen
- Booking request inbox
- Trip management screen

Activity:

- My trips
- My bookings
- Wallet
- Wallet history

Safety and settings:

- Emergency contacts
- SOS
- Notifications
- Profile settings
- Ratings
- Report/block user

## 9.2 Recommended UX Patterns

- Bottom navigation with 4 to 5 tabs
- Capability gating, not fixed role switching
- Bottom sheets for lightweight actions
- Toast/snackbar feedback for booking state changes
- Timeline or status stepper for trip and booking progress
- Wallet balance shown as a header card with transaction history below
- Documents shown with status chips: pending, approved, rejected
- Pull-to-refresh for lists
- Skeleton loaders for search results

## 9.3 Mobile Notification Flow

- Save device token on login
- Update token on refresh
- Handle notification tap to open relevant trip/booking
- Show unread badge count
- Keep an in-app notification screen for history

---

# 10. Recommended Tech Implementation Details

## 10.1 Backend

Use modular Laravel structure without over-engineering.

Use:

- Form Requests for validation
- API Resources for response shaping
- Policies for ownership and authorization
- Service or Action classes for booking and wallet operations
- Events and listeners for notifications
- Jobs for SMS, push, and document processing
- Database transactions for booking and wallet mutations

Use private storage for:

- identity documents
- vehicle documents
- profile photos

Use config tables for:

- platform fee percentage or flat fee
- cancellation policy
- search radius
- verification requirements
- feature toggles

## 10.2 Booking Concurrency

This is the most important technical requirement.

Use:

- row locks on trip
- row locks on traveler wallet
- rechecking seat availability inside the same transaction
- idempotency keys on booking requests
- unique constraints to prevent duplicate confirmed bookings

Required test scenarios:

- two travelers book the last seat simultaneously
- traveler books while host cancels trip
- host accepts booking while traveler cancels request
- wallet deduction fails after booking creation attempt

All must preserve consistency.

## 10.3 Admin Security

All admin actions should be audit logged.

Admin wallet adjustments should require:

- reason
- optional reference
- confirmation step
- separate ledger entry

Sensitive admin pages should include:

- status filters
- history panels
- inline document review
- rejection reason capture

---

# 11. Recommended Migration Order

1. Users, profiles, devices, consents
2. OTP and auth settings
3. Capabilities
4. Verification requests and documents
5. Vehicle categories and vehicles
6. Trips and trip stops
7. Bookings
8. Wallets and ledger
9. Ratings, reports, blocked users
10. Emergency contacts and SOS
11. Notifications
12. Audit logs
13. Admin settings and fee configuration

---

# 12. Recommended Backend Task Order

1. Project setup and base architecture
2. Authentication and OTP flow
3. User profile and device registration
4. Capabilities service
5. Document storage service
6. Verification module
7. Vehicle module
8. Trip module
9. Trip search and seat logic
10. Booking state machine
11. Wallet ledger
12. Platform fee deduction
13. Cancellation policy engine
14. Ratings and reviews
15. Reports and moderation
16. SOS and emergency handling
17. Notifications
18. Velzon Laravel+Vue admin screens
19. Admin audit logging
20. API hardening and tests

---

# 13. Recommended Flutter Task Order

1. Project shell, theme, routing, localization
2. API client, token storage, error handling
3. OTP login and profile setup
4. Home screen and search UI
5. Trip details and booking request UI
6. Vehicle and document upload flow
7. Host verification status UI
8. Offer Trip wizard
9. My trips / my bookings
10. Wallet and transaction history
11. Notifications screen
12. Profile, ratings, settings
13. Safety and SOS
14. Push notification handling
15. Final QA and app store preparation

---

# 14. Test Plan

## 14.1 Laravel Tests

Write tests for:

- OTP flow and rate limiting
- Profile completion logic
- Capability grants and revocations
- Document upload and access control
- Verification approve/reject flow
- Vehicle ownership rules
- Trip publish/cancel/start/complete rules
- Seat availability calculations
- Booking request, accept, reject, confirm flows
- Wallet ledger correctness
- Platform fee deduction rules
- Cancellation outcomes
- Rating eligibility
- SOS creation and admin review
- Admin audit log creation

## 14.2 Flutter Tests

Write tests for:

- OTP and auth flow
- Profile completion flow
- Search and filter behavior
- Trip details rendering
- Booking request flow
- Host request management
- Wallet display and refresh
- Notification handling
- Offline/retry behavior
- Accessibility and loading states

## 14.3 Critical Edge Cases

- duplicate booking request submission
- late host acceptance after traveler cancellation
- trip publish with zero seats
- wallet deduction failure after acceptance
- document resubmission after rejection
- expired verification documents
- SOS without network
- admin adjustment with zero amount
- same user attempting to book own trip

---

# 15. Risks and Tradeoffs

## 15.1 Wallet Complexity

If the wallet becomes a real stored-value product, it may trigger additional legal, compliance, and payment requirements. Keep the wallet limited to platform fees in the first version unless the business decides otherwise.

Recommended alternative for launch:

- booking-level payment authorization
- admin-issued credits
- wallet only for platform service fees

## 15.2 Route Matching Complexity

Do not build advanced route optimization in Phase 1. Start with radius-based origin and destination search.

## 15.3 Real-time GPS Complexity

For the first version, optional live tracking is enough. Full continuous GPS tracking is a separate large feature.

## 15.4 Single Codebase Expansion

The platform should be designed to add future service modules, but only ride sharing should be built now. Overbuilding marketplace modules early will delay launch without real user feedback.

---

# 16. Open Questions to Resolve Before Build

1. Should the first version support instant booking or request approval only?
2. What should be the exact wallet funding model in the pilot?
3. Should the platform fee be percentage-based, flat, or both?
4. What is the required search radius for Phase 1 cities?
5. Should Aadhaar reference be mandatory in pilot or configurable?
6. Should SOS notify only admin or also emergency contacts in the first version?
7. Should ratings be visible to both host and traveler immediately or after mutual rating?
8. Should trip search support multiple stops in Phase 1 or only origin/destination?

---

# 17. Suggested First Deliverables

The implementation sequence is phase-gated and must be followed in this order:

1. Phase 1: `orezone` public landing page from the Velzon Laravel+Vue template
2. Phase 2: Laravel foundation, auth, profiles, and API contracts
3. Phase 3: Vehicle and verification modules plus Velzon admin operations
4. Phase 4: Trip creation, search, booking, and state transitions
5. Phase 5: Wallet ledger, ratings, safety, notifications, and moderation
6. Phase 6: Flutter onboarding, shared theme, search, booking, host, and activity flows
7. Phase 7: Integrated regression, security, performance, accessibility, and release QA

Each numbered phase has its own test-and-fix gate. A phase is not complete when code is merely implemented; it is complete only after its acceptance criteria pass and identified bugs are fixed.

---

# 18. Success Criteria

The implementation is successful when:

- a traveler can search and request a booking
- a host can manage requests and trips
- platform fee is deducted consistently and auditably
- admin can review documents and manage bookings
- wallet ledger stays consistent under concurrent use
- the app is expandable to future community services without redesigning core identity and wallet patterns

# 19. Reference Source Inventory

## 19.1 Backend reference

Path:
/www/wwwroot/template/Drivermond/drivemond/combo-v3.2/new

Useful modules observed:
- TripManagement
- UserManagement
- VehicleManagement
- TransactionManagement
- AdminModule
- ReviewModule
- FareManagement
- PromotionManagement
- BusinessManagement
- Gateways
- AuthManagement
- BlogManagement
- ParcelManagement
- AiModule
- ChattingManagement
- ZoneManagement

These are for reference/workflow inspiration only, not direct reuse.

### Reference backend architecture notes

Observed internal module structure:
- Config/config.php
- Database/Migrations
- Entities (Eloquent models)
- Http/Controllers with separate Api/Customer, Api/Driver, and Web/Admin namespaces
- Http/Requests
- Providers with service/repository bindings
- Repository + Eloquent repository pattern
- Service + Interface pattern
- Resources/views/admin
- Transformers for API responses

Observed operational patterns:
- explicit status constants for ride/trip flow
- separate TripStatus timeline entity
- wallet/account ledger with multiple balance states
- admin activity logs with polymorphic recording
- broadcast events for real-time trip updates
- firebase jobs/helper functions for push notifications
- form requests and dedicated transformers for consistent validation/output

These patterns suggest the new project should use:
- clean domain modules
- explicit service classes
- audit-friendly status history
- separate admin/web/api controllers where needed
- consistent mobile API resource contracts

## 19.2 Flutter reference

The Flutter source was not directly present as a working project tree inside:
/www/wwwroot/template/Drivermond/drivemond/combo-v3.2/new

The user app source was found inside:
/www/wwwroot/template/Drivermond/drivemond/combo-v3.2/drivemond-user-app-3.2.zip

When extracted, the Flutter project had:
- name: ride_sharing_user_app
- internal branding: HexaRide
- state management: GetX
- dependencies: firebase_core, firebase_messaging, google_maps_flutter, flutter_localization, geolocator, dio-like networking, cached_network_image, flutter_widget_to_image, shimmer, lottie, and similar UI packages

This confirms the reference app uses a mobile-first, map-first, notification-heavy UX.

### Reference Flutter architecture notes

Observed app architecture:
- main.dart bootstraps Firebase, DI/notification helpers, then launches splash/login routing
- feature-based folder structure with controllers, domain/models, repositories, services, screens, widgets
- repository/service/interface chain per feature
- GetX routing and navigation
- pusher websocket channels for real-time trip events
- firebase messaging for push notifications
- map-based home with search, category/banner widgets, saved addresses, and promotional sections
- ride flow modeled as a state machine: initial, fare/search, finding rider, accepted, OTP, pickup, ongoing, complete
- wallet flow with balance card, add funds, transfer, voucher/promo, and filterable transaction history
- safety flow with predefined alert reasons, countdown/delay, emergency contacts, and share-location tools
- notifications with deep link routing into relevant trip/payment screens
- loading/refresh patterns like shimmer, paginated lists, pull-to-refresh, and confirmation bottom sheets

This supports the new project plan by validating:
- map-first home UX
- state-machine ride/booking UX
- dedicated wallet and notification UX
- strong need for deep link handling from push notifications
- benefit of feature-first mobile architecture

## 19.3 Main adaptation conclusion

For the new community platform, borrow:
- modular admin workflow structure
- trip status timeline concept
- wallet/ledger concept
- safety/notification patterns
- mobile bottom navigation and dedicated wallet/notification screens

But replace:
- driver/customer binary roles with capability-based identity
- pure dispatch UX with seat-sharing trip UX
- single-service assumption with future-service-ready module boundaries

### Final implementation implication

The new project should borrow:
- admin operational workflow structure
- trip timeline/state machine concept
- wallet ledger concept
- notification + deep link concept
- feature-first mobile architecture

But redesign around:
- host/traveler identity model
- seat-based trips instead of dispatch rides
- platform fee only in Phase 1
- cleaner, more testable backend boundaries than global helper-heavy code
