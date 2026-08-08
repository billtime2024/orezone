# Orezone Security & Logic Fixes Plan

## P0 — Blockers

### 1. Lock down admin web routes with auth + admin gate
- Add an `is_admin` boolean column to the `users` table via migration.
- Create an `AdminGateServiceProvider` that defines an `access-admin` gate based on `$user->is_admin`.
- Wrap `/admin/*` routes in `middleware(['auth', 'can:access-admin'])`.
- Return proper 403 JSON/Inertia responses for unauthorized access.
- Verify only admin users can reach admin screens and list pages.

### 2. Remove or disable wallet top-up route that credits without payment verification
- Disable `POST /api/v1/wallet/topups` or return `501` while no provider integration exists.
- Leave wallet read routes intact.
- Verify the endpoint no longer mutates wallet balances.

### 3. Add OTP-specific throttling and strengthen authentication flow
- Add dedicated throttling to `auth/send-otp` and `auth/verify-otp`.
- Limit per phone number and per IP.
- Move user creation to after OTP verification where possible or clearly mark pending account status.
- Hash OTPs before caching.
- Remove dev OTP output once environment is production.
- Verify rate limiting blocks brute-force attempts.

### 4. Fix production environment configuration leak
- Ensure `APP_ENV=production` in the deployed environment.
- Remove or gate any dev-only response fields.
- Clear config cache after the fix.
- Verify the endpoint no longer returns `otp_for_dev` or dev-only payloads.

## P1 — High-impact logic fixes

### 5. Fix request-approval booking seat accounting
- Decide on one reservation model:
  - Reserve seats only on approval, or
  - Reserve seats on request and restore on rejection.
- Update `BookingController` and related booking status logic accordingly.
- Ensure `cancel`/`reject` restore seats only once.
- Verify a requested booking no longer double-decrements seats.

### 6. Validate vehicle ownership on trip update
- Ensure `UpdateTripRequest` or the trip update action only allows the authenticated user’s own vehicle when `vehicle_id` is provided.
- Verify the host cannot assign another user’s vehicle.

### 7. Block unsafe `total_seats` updates on trips
- Prevent seat-count updates once bookings exist.
- If updates are allowed on draft trips, recalculate `available_seats` safely.
- Verify seat totals remain consistent.

## P2 — Data integrity and safety fixes

### 8. Bind rating requests to verified relationship
- Validate the supplied `booking_id` belongs to the completed relationship between reviewer, reviewee, and trip.
- Ensure a user cannot attach an unrelated booking or create phantom ratings.
- Verify duplicate-rating guard still works.

### 9. Validate safety report relationships
- Require that the reporter, reported user, trip, and booking are legitimately related.
- Verify arbitrary cross-linked reports can no longer be created.

### 10. Fix SOS messaging to match actual behavior
- Change the SOS response message to reflect what actually happened.
- Optionally add notification scaffolding with explicit “pending handling” wording.
- Verify the response is no longer misleading.

### 11. Tighten verification document uploads
- Add an explicit file type allowlist for verification documents.
- Verify only intended file types are accepted.

## P3 — Quality and test stability

### 12. Repair the test environment and factory
- Fix `UserFactory` to use a test-compatible password hash.
- Restore or remove obsolete Fortify/Jetstream tests.
- Ensure `php artisan test` returns green.

### 13. Run code style fixes
- Run `php artisan pint` to fix formatting issues.
- Verify no remaining style failures.

### 14. Final verification
- Run `php artisan test`.
- Run `./vendor/bin/pint --test`.
- Run `php artisan route:list --path=admin`.
- Manually trace booking and wallet flows in code/tests.
- Commit after verification passes.
