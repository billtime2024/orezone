# Food Services Module — Orezone Platform

> Focus: Homemade Food · Catering Services · Hotel/Food Services
> Stack: Laravel 11 + Inertia + Vue3 + Sanctum + Flutter + MySQL
> Community: PURE VEG ONLY — no non-veg items allowed

---

## 1. DATABASE SCHEMA

### 1.1 food_providers
| Field | Type | Notes |
|-------|------|-------|
| id | bigint PK | |
| user_id | bigint FK → users | Provider is a user |
| provider_type | enum('homemade','catering','hotel') | |
| business_name | varchar(255) | |
| description | text | |
| logo_url | varchar(500) | |
| cover_image_url | varchar(500) | |
| phone | varchar(20) | |
| email | varchar(255) | |
| address | text | |
| latitude | decimal(10,7) | |
| longitude | decimal(10,7) | |
| city | varchar(100) | |
| state | varchar(100) | |
| pincode | varchar(10) | |
| fssai_license | varchar(50) | FSSAI license number |
| fssai_expiry | date | |
| gst_number | varchar(20) | |
| pan_number | varchar(20) | |
| bank_account_number | varchar(30) | |
| bank_ifsc | varchar(15) | |
| upi_id | varchar(100) | |
| verification_status | enum('pending','approved','rejected') | default: pending |
| verified_at | timestamp nullable | |
| is_active | boolean | default: true |
| is_featured | boolean | default: false |
| avg_rating | decimal(3,2) | cached, default: 0 |
| total_orders | int | cached, default: 0 |
| total_revenue | decimal(12,2) | cached, default: 0 |
| commission_rate | decimal(5,2) | default: 10.00 |
| operating_hours | json | {"mon":{"open":"09:00","close":"21:00"},...} |
| delivery_radius_km | int | default: 5 |
| min_order_amount | decimal(8,2) | default: 0 |
| free_delivery_above | decimal(8,2) | nullable |
| created_at | timestamp | |
| updated_at | timestamp | |

### 1.2 food_categories
| Field | Type | Notes |
|-------|------|-------|
| id | bigint PK | |
| parent_id | bigint FK → food_categories nullable | For sub-categories |
| name | varchar(100) | e.g. "South Indian", "Continental" |
| slug | varchar(120) | unique |
| image_url | varchar(500) | |
| icon | varchar(50) | emoji or icon class |
| sort_order | int | default: 0 |
| is_active | boolean | default: true |
| created_at | timestamp | |
| updated_at | timestamp | |

Seed categories: Home Cooked, South Indian, North Indian, Gujarati, Rajasthani, Chinese (Veg), Continental (Veg), Biryani (Veg), Desserts, Beverages, Tiffin Service, Wedding Catering, Corporate Catering, Party Package, Buffet, Room Service, Street Food (Veg), Snacks & Chaat, South Indian Tiffin, Festival Special

### 1.3 food_items
| Field | Type | Notes |
|-------|------|-------|
| id | bigint PK | |
| provider_id | bigint FK → food_providers | |
| category_id | bigint FK → food_categories | |
| name | varchar(255) | |
| slug | varchar(280) | unique per provider |
| description | text | |
| image_url | varchar(500) | |
| price | decimal(8,2) | per portion/unit |
| discount_price | decimal(8,2) | nullable |
| unit | enum('plate','bowl','kg','ltr','dozen','parcel') | |
| min_quantity | int | default: 1 |
| max_quantity | int | default: 50 |
| preparation_time_min | int | minutes |
| is_jain | boolean | |
| is_vegan | boolean | |
| spice_level | enum('mild','medium','spicy','very_spicy') | |
| allergens | json | ["gluten","dairy","nuts","soy","eggs"] |
| ingredients | text | |
| is_available | boolean | default: true |
| is_featured | boolean | default: false |
| available_days | json | ["mon","tue","wed","thu","fri","sat","sun"] |
| available_from | time | |
| available_to | time | |
| total_orders | int | cached |
| avg_rating | decimal(3,2) | cached |
| created_at | timestamp | |
| updated_at | timestamp | |

### 1.4 food_item_media
| Field | Type | Notes |
|-------|------|-------|
| id | bigint PK | |
| food_item_id | bigint FK → food_items | |
| media_url | varchar(500) | |
| media_type | enum('image','video') | |
| sort_order | int | |
| created_at | timestamp | |

### 1.5 food_pricing_tiers
| Field | Type | Notes |
|-------|------|-------|
| id | bigint PK | |
| food_item_id | bigint FK → food_items | |
| tier_name | varchar(100) | "Small (200g)", "Family (1kg)" |
| quantity | decimal(8,2) | |
| unit | varchar(20) | |
| price | decimal(8,2) | |
| created_at | timestamp | |

### 1.6 food_delivery_slots
| Field | Type | Notes |
|-------|------|-------|
| id | bigint PK | |
| provider_id | bigint FK → food_providers | |
| day_of_week | tinyint | 0=Sun..6=Sat |
| slot_start | time | |
| slot_end | time | |
| max_orders | int | capacity per slot |
| current_orders | int | default: 0 |
| is_active | boolean | default: true |
| created_at | timestamp | |
| updated_at | timestamp | |

### 1.7 food_orders
| Field | Type | Notes |
|-------|------|-------|
| id | bigint PK | |
| order_number | varchar(30) | unique, auto-generated |
| user_id | bigint FK → users | Customer |
| provider_id | bigint FK → food_providers | |
| order_type | enum('homemade','catering','hotel') | |
| status | enum('placed','confirmed','preparing','ready','out_for_delivery','delivered','cancelled','refunded') | |
| delivery_type | enum('delivery','pickup') | |
| delivery_address | text | |
| delivery_latitude | decimal(10,7) | |
| delivery_longitude | decimal(10,7) | |
| delivery_slot_id | bigint FK → food_delivery_slots nullable | |
| scheduled_at | timestamp nullable | For pre-orders |
| subtotal | decimal(10,2) | |
| delivery_charge | decimal(8,2) | default: 0 |
| discount_amount | decimal(8,2) | default: 0 |
| tax_amount | decimal(8,2) | default: 0 |
| total_amount | decimal(10,2) | |
| commission_amount | decimal(8,2) | |
| payment_method | enum('wallet','upi','card','cash','netbanking') | |
| payment_status | enum('pending','paid','failed','refunded') | |
| payment_reference | varchar(100) | nullable |
| special_instructions | text | nullable |
| cancellation_reason | text | nullable |
| cancelled_at | timestamp nullable | |
| refunded_at | timestamp nullable | |
| refund_amount | decimal(10,2) | nullable |
| delivered_at | timestamp nullable | |
| created_at | timestamp | |
| updated_at | timestamp | |

### 1.8 food_order_items
| Field | Type | Notes |
|-------|------|-------|
| id | bigint PK | |
| food_order_id | bigint FK → food_orders | |
| food_item_id | bigint FK → food_items | |
| pricing_tier_id | bigint FK → food_pricing_tiers nullable | |
| name | varchar(255) | snapshot |
| price | decimal(8,2) | snapshot |
| quantity | int | |
| total | decimal(10,2) | |
| special_notes | varchar(255) | nullable |
| created_at | timestamp | |

### 1.9 catering_requests
| Field | Type | Notes |
|-------|------|-------|
| id | bigint PK | |
| request_number | varchar(30) | unique |
| user_id | bigint FK → users | Customer |
| provider_id | bigint FK → food_providers nullable | null until assigned |
| event_type | enum('wedding','birthday','corporate','party','festival','other') | |
| event_name | varchar(255) | |
| event_date | date | |
| event_end_date | date nullable | For multi-day |
| event_time | time | |
| venue_address | text | |
| venue_latitude | decimal(10,7) | |
| venue_longitude | decimal(10,7) | |
| guest_count | int | |
| budget_min | decimal(10,2) | nullable |
| budget_max | decimal(10,2) | nullable |
| cuisine_preferences | json | ["south_indian","north_indian"] |
| dietary_requirements | json | ["jain","vegan","gluten_free"] |
| menu_description | text | free text |
| special_requests | text | |
| tasting_requested | boolean | default: false |
| tasting_date | date nullable | |
| status | enum('pending','quotes_received','quote_selected','tasting_scheduled','confirmed','in_progress','completed','cancelled') | |
| total_amount | decimal(12,2) | nullable, set on confirmation |
| advance_paid | decimal(10,2) | default: 0 |
| payment_status | enum('pending','advance_paid','partially_paid','fully_paid','refunded') | |
| cancellation_reason | text | nullable |
| created_at | timestamp | |
| updated_at | timestamp | |

### 1.10 catering_quotes
| Field | Type | Notes |
|-------|------|-------|
| id | bigint PK | |
| catering_request_id | bigint FK → catering_requests | |
| provider_id | bigint FK → food_providers | |
| quoted_amount | decimal(12,2) | |
| proposed_menu | json | [{name, description, price}] |
| includes_decor | boolean | default: false |
| includes_service_staff | boolean | default: false |
| staff_count | int | nullable |
| notes | text | |
| valid_until | timestamp | |
| status | enum('pending','accepted','rejected','expired') | |
| created_at | timestamp | |
| updated_at | timestamp | |

### 1.11 hotel_food_services
| Field | Type | Notes |
|-------|------|-------|
| id | bigint PK | |
| provider_id | bigint FK → food_providers | |
| service_type | enum('room_service','restaurant','buffet','special_occasion') | |
| name | varchar(255) | |
| description | text | |
| is_24hr | boolean | default: false |
| operating_start | time | nullable |
| operating_end | time | nullable |
| capacity | int | for restaurant reservations |
| is_active | boolean | default: true |
| created_at | timestamp | |
| updated_at | timestamp | |

### 1.12 hotel_reservations
| Field | Type | Notes |
|-------|------|-------|
| id | bigint PK | |
| user_id | bigint FK → users | |
| hotel_service_id | bigint FK → hotel_food_services | |
| reservation_date | date | |
| reservation_time | time | |
| party_size | int | |
| special_requests | text | nullable |
| status | enum('pending','confirmed','seated','completed','cancelled','no_show') | |
| created_at | timestamp | |
| updated_at | timestamp | |

### 1.13 food_reviews
| Field | Type | Notes |
|-------|------|-------|
| id | bigint PK | |
| user_id | bigint FK → users | |
| provider_id | bigint FK → food_providers | |
| food_item_id | bigint FK → food_items nullable | null = provider review |
| food_order_id | bigint FK → food_orders | |
| rating | tinyint | 1-5 |
| taste_rating | tinyint | 1-5 nullable |
| packaging_rating | tinyint | 1-5 nullable |
| delivery_rating | tinyint | 1-5 nullable |
| comment | text | nullable |
| reply | text | nullable, provider reply |
| replied_at | timestamp nullable | |
| created_at | timestamp | |
| updated_at | timestamp | |

### 1.14 food_promotions
| Field | Type | Notes |
|-------|------|-------|
| id | bigint PK | |
| provider_id | bigint FK → food_providers nullable | null = platform-wide |
| code | varchar(30) | unique |
| description | text | |
| discount_type | enum('percentage','fixed') | |
| discount_value | decimal(8,2) | |
| min_order_amount | decimal(8,2) | default: 0 |
| max_discount | decimal(8,2) | nullable |
| max_uses | int | nullable |
| used_count | int | default: 0 |
| applicable_to | enum('all','homemade','catering','hotel') | applies to all (pure veg) |
| starts_at | timestamp | |
| expires_at | timestamp | |
| is_active | boolean | default: true |
| created_at | timestamp | |
| updated_at | timestamp | |

### 1.15 food_cart
| Field | Type | Notes |
|-------|------|-------|
| id | bigint PK | |
| user_id | bigint FK → users | |
| food_item_id | bigint FK → food_items | |
| pricing_tier_id | bigint FK → food_pricing_tiers nullable | |
| quantity | int | default: 1 |
| special_notes | varchar(255) | nullable |
| created_at | timestamp | |
| updated_at | timestamp | |

### 1.16 food_wishlists
| Field | Type | Notes |
|-------|------|-------|
| id | bigint PK | |
| user_id | bigint FK → users | |
| food_item_id | bigint FK → food_items | |
| created_at | timestamp | |
| unique(user_id, food_item_id) | | |

---

## 2. USER ROLES & PERMISSIONS

| Role | Description | Key Permissions |
|------|-------------|-----------------|
| **Customer** | End user ordering food | Browse, order, pay, review, cancel |
| **Homemade Cook** | Individual home-based food seller | Manage menu, accept orders, set schedule, receive payments |
| **Catering Provider** | Catering company/professional | Manage events, send quotes, handle large orders |
| **Hotel Provider** | Hotel/restaurant food services | Room service, reservations, buffet management |
| **Delivery Agent** | Food delivery personnel | Accept delivery tasks, mark delivered, SOS |
| **Admin** | Platform admin | Full control, verification, commission, analytics |

### Capability Flags (user_capabilities extension)
```
can_provide_food     → boolean
can_deliver_food     → boolean
can_cater            → boolean
food_provider_type   → enum('homemade','catering','hotel')
```

---

## 3. CORE FEATURES

### 3.1 Homemade Food
- **Menu Management**: Add/edit/delete food items with photos, pricing, portions
- **Weekly Menu Scheduling**: Set which dishes available on which days
- **Pre-Orders**: Customers order 1-2 days in advance
- **Portion Pricing**: Small / Medium / Family sizing
- **Dietary Filters**: Jain / Vegan / Gluten-Free labels (all items pure veg)
- **Delivery/Pickup Slots**: Provider sets available time windows
- **Cook Profile**: Bio, specialty cuisine, years of experience, FSSAI details
- **Availability Toggle**: One-click on/off for daily availability
- **Minimum Order**: Set minimum order amount
- **Delivery Radius**: Define service area (km)

### 3.2 Catering Services
- **Event-Based Booking**: Wedding, Birthday, Corporate, Party, Festival
- **Guest Count & Budget**: Customer specifies scale
- **Menu Customization**: Propose dishes, get quotes
- **Quote System**: Multiple providers send quotes; customer selects
- **Tasting Sessions**: Schedule pre-event tasting
- **Advance Booking**: Book 7-90 days in advance
- **Multi-Day Catering**: Multi-day events with per-day menus
- **Service Staff**: Include/exclude serving staff in quotes
- **Decoration Add-ons**: Optional decor packages
- **Milestone Payments**: Advance → Mid → Final payment schedule
- **Event Timeline**: Timeline of preparation, setup, service

### 3.3 Hotel/Food Services
- **Room Service Ordering**: In-room dining for hotel guests
- **Restaurant Reservations**: Book tables with time/size
- **Buffet Packages**: Fixed-price buffet deals
- **Special Occasion Packages**: Birthday, Anniversary celebration packages
- **24/7 Service Indicator**: Show round-the-clock availability
- **Course-Based Menus**: Appetizer → Main → Dessert ordering flow

---

## 4. API ENDPOINTS

### 4.1 Public / Customer Routes (auth:sanctum)

**Food Items & Discovery**
| Method | Path | Description |
|--------|------|-------------|
| GET | /api/food/categories | List categories |
| GET | /api/food/items | Search/filter food items |
| GET | /api/food/items/{slug} | Single item detail |
| GET | /api/food/nearby | Location-based nearby food |
| GET | /api/food/featured | Featured items |
| GET | /api/food/providers | List food providers |
| GET | /api/food/providers/{slug} | Provider profile + menu |

**Cart & Orders**
| Method | Path | Description |
|--------|------|-------------|
| GET | /api/food/cart | Get cart |
| POST | /api/food/cart | Add to cart |
| PUT | /api/food/cart/{id} | Update cart item |
| DELETE | /api/food/cart/{id} | Remove from cart |
| POST | /api/food/orders | Place order |
| GET | /api/food/orders | Order history |
| GET | /api/food/orders/{id} | Order detail |
| POST | /api/food/orders/{id}/cancel | Cancel order |
| POST | /api/food/orders/{id}/rate | Rate order |

**Catering**
| Method | Path | Description |
|--------|------|-------------|
| POST | /api/food/catering/request | Create catering request |
| GET | /api/food/catering/requests | My catering requests |
| GET | /api/food/catering/requests/{id} | Request detail + quotes |
| POST | /api/food/catering/requests/{id}/select-quote | Accept a quote |
| POST | /api/food/catering/requests/{id}/cancel | Cancel request |

**Hotel Food**
| Method | Path | Description |
|--------|------|-------------|
| GET | /api/food/hotel/{id}/menu | Hotel menu |
| POST | /api/food/hotel/{id}/room-service | Order room service |
| POST | /api/food/hotel/{id}/reserve | Make reservation |
| GET | /api/food/hotel/reservations | My reservations |

**Reviews & Wishlist**
| Method | Path | Description |
|--------|------|-------------|
| POST | /api/food/reviews | Submit review |
| GET | /api/food/wishlist | My wishlist |
| POST | /api/food/wishlist/{itemId} | Toggle wishlist |

### 4.2 Provider Routes (auth:sanctum + food_provider middleware)

| Method | Path | Description |
|--------|------|-------------|
| GET | /api/food/provider/dashboard | Provider dashboard stats |
| GET/POST/PUT/DELETE | /api/food/provider/items | CRUD food items |
| GET/POST/PUT/DELETE | /api/food/provider/categories | Manage categories |
| GET/POST | /api/food/provider/delivery-slots | Manage delivery slots |
| GET | /api/food/provider/orders | Incoming orders |
| PUT | /api/food/provider/orders/{id}/status | Update order status |
| GET | /api/food/provider/catering/quotes | Pending quote requests |
| POST | /api/food/provider/catering/quotes | Send catering quote |
| GET | /api/food/provider/reviews | Provider reviews |
| PUT | /api/food/provider/profile | Update provider profile |

### 4.3 Admin Routes (auth + admin gate)

| Method | Path | Description |
|--------|------|-------------|
| GET | /admin/food-providers | All food providers |
| GET | /admin/food-providers/{id} | Provider detail |
| PATCH | /admin/food-providers/{id}/verify | Approve/reject |
| GET | /admin/food-orders | All food orders |
| GET | /admin/food-orders/{id} | Order detail |
| GET | /admin/food-catering | All catering requests |
| GET | /admin/food-analytics | Module analytics |
| GET | /admin/food-commissions | Commission reports |

---

## 5. BUSINESS LOGIC

### 5.1 Commission Model
| Provider Type | Default Commission | Notes |
|---------------|-------------------|-------|
| Homemade Cook | 10% | Lower to encourage adoption |
| Catering Company | 8% | Higher value, lower rate |
| Hotel | 12% | Premium positioning |

- Admin can override per provider
- Commission deducted from provider payout
- Platform fee + GST on commission

### 5.2 Payment Flow
1. **Homemade/Catering**: Customer pays → Hold in platform escrow → Delivery confirmed → Commission deducted → Provider paid (T+2 days)
2. **Catering**: Advance (30%) → Milestone (40%) → Final (30%)
3. **Hotel**: Direct charge at time of service
4. **Wallet Integration**: Use existing Wallet model for payments and payouts

### 5.3 Cancellation Policy
| Timing | Customer Refund | Provider Penalty |
|--------|----------------|------------------|
| 2+ hours before slot | 100% refund | None |
| 1-2 hours before slot | 75% refund | None |
| < 1 hour | 50% refund | Warning |
| No-show | 0% refund | Commission earned |
| Provider cancels | 100% refund + 10% credit | Strike 1-3 system |

### 5.4 Refund Handling
- Refunds credited to wallet (instant) or original payment method (3-5 days)
- Partial refunds for item-level cancellations
- Catering refunds follow milestone-based schedule
- Dispute resolution: Customer escalates → Admin mediates

---

## 6. SEARCH & DISCOVERY

### 6.1 Filters
| Filter | Type | Notes |
|--------|------|-------|
| Cuisine | multi-select | South Indian, North Indian, etc. |
| Price Range | slider | Min-Max per plate |
| Distance | slider | 1-50 km from user location |
| Rating | 1-5 stars | Minimum rating |
| Dietary | checkboxes | Jain, Vegan, Gluten-Free (all pure veg) |
| Spice Level | radio | Mild / Medium / Spicy |
| Availability | toggle | Currently available |
| Provider Type | tabs | Homemade / Catering / Hotel |
| Sort By | dropdown | Relevance, Rating, Price-Low, Price-High, Distance, Popularity |

### 6.2 Search Implementation
- Full-text search on: item name, description, provider name, cuisine tags
- Geospatial query: Haversine formula for distance filtering
- Redis cache for popular searches and nearby results
- Debounced search with instant results

---

## 7. NOTIFICATION SYSTEM

### 7.1 Notification Events
| Event | Channel | Recipient |
|-------|---------|-----------|
| Order Placed | Push + In-App + SMS | Customer + Provider |
| Order Confirmed | Push + In-App | Customer |
| Order Preparing | Push + In-App | Customer |
| Order Ready | Push + In-App | Customer |
| Out for Delivery | Push + In-App + SMS | Customer |
| Order Delivered | Push + In-App | Customer |
| Order Cancelled | Push + In-App + Email | Both |
| New Catering Request | Push + In-App | Provider |
| Quote Received | Push + In-App | Customer |
| Quote Accepted | Push + In-App + SMS | Provider |
| Tasting Reminder | Push + In-App | Both |
| Catering Reminder (3 days before) | Push + In-App + SMS | Both |
| Payment Received | Push + In-App | Provider |
| Review Received | Push + In-App | Provider |
| New Promotion | Push + In-App | Customer |

### 7.2 Notification Templates
Extend existing `notification_templates` table with food-specific templates.

---

## 8. ADMIN FEATURES

### 8.1 Provider Verification
- Document upload: FSSAI license, GST, PAN, Bank details
- Admin review queue with approve/reject
- Rejection reason with re-upload option
- Verification badge on approved providers

### 8.2 Order Management
- View all orders with filters (date, status, provider, type)
- Order detail with item breakdown
- Cancel/refund orders on behalf of customer
- Export orders to CSV/Excel

### 8.3 Commission Tracking
- Per-provider commission summary
- Commission ledger with dates
- Payout schedule and history
- Disputed commissions queue

### 8.4 Analytics Dashboard
- Total orders, revenue, commission by period
- Top providers by revenue/rating
- Category performance
- Geographic heat map of orders
- Customer retention metrics
- Average order value trends

### 8.5 Sidebar Module (Admin Panel)
```
Food Services (top-level module)
├── Dashboard (analytics)
├── Providers
│   ├── All Providers
│   ├── Pending Verification
│   └── Featured Providers
├── Orders
│   ├── All Orders
│   ├── Active Orders
│   └── Refunds
├── Catering
│   ├── Requests
│   └── Completed Events
├── Hotel Services
├── Reviews
├── Promotions
└── Commission Reports
```

---

## 9. FLUTTER APP SCREENS

### 9.1 Customer App Screens
| Screen | Description |
|--------|-------------|
| FoodHome | Category grid, featured items, nearby food |
| FoodSearch | Search with filters |
| FoodProviderList | List/grid of providers |
| FoodProviderDetail | Provider profile + full menu |
| FoodItemDetail | Item detail with variants, photos, reviews |
| FoodCart | Cart with quantity adjustment, promo code |
| FoodCheckout | Address, payment method, slot selection |
| FoodOrderConfirmation | Order placed success |
| FoodOrderTracking | Live order status with timeline |
| FoodOrderHistory | Past orders list |
| FoodOrderDetail | Full order detail with reorder |
| FoodReview | Rate & review order |
| FoodWishlist | Saved items |
| CateringRequest | Create catering request form |
| CateringRequestList | My catering requests |
| CateringRequestDetail | Detail + quotes |
| HotelMenu | Hotel food menu |
| HotelReservation | Book table |
| RoomService | In-room dining order |

### 9.2 Provider App Screens (or Provider mode in same app)
| Screen | Description |
|--------|-------------|
| ProviderDashboard | Stats: orders, revenue, rating |
| ProviderProfile | Edit business details, photos |
| MenuManager | List of food items with toggle |
| FoodItemForm | Add/edit food item |
| OrderQueue | Incoming orders with accept/reject |
| OrderDetail | Order detail + status updates |
| CateringInbox | Incoming catering requests |
| CateringQuoteForm | Create catering quote |
| DeliverySlots | Manage delivery time slots |
| ProviderReviews | View and reply to reviews |
| Payouts | Earnings and payout history |

### 9.3 Provider Verification Screens
| Screen | Description |
|--------|-------------|
| FoodProviderOnboarding | Step-by-step registration |
| DocumentUpload | Upload FSSAI, GST, PAN |
| VerificationStatus | Track verification progress |

---

## 10. INTEGRATION POINTS

| Existing System | Integration |
|-----------------|-------------|
| **User Model** | Extend `user_capabilities` with food provider flags |
| **Wallet System** | Use existing Wallet + WalletTransaction for food payments |
| **Notifications** | Use existing Notification + NotificationTemplate system |
| **SOS System** | Food delivery agents get SOS button in provider app |
| **AuditLog** | Log all food order status changes, payments, cancellations |
| **Modular Sidebar** | Add "Food Services" top-level module to admin sidebar |
| **User Profiles** | Food providers link to user profile for contact info |
| **Geo Search** | Reuse latitude/longitude patterns from ride-sharing |
| **API Pattern** | Follow existing API resource + controller patterns |
| **Flutter App** | Add Food Services tab/mode to existing customer + provider apps |

---

## IMPLEMENTATION PHASES

| Phase | Scope | Effort |
|-------|-------|--------|
| **Phase 1: Foundation** | Migrations, Models, Provider CRUD, Basic Menu | 3-4 days |
| **Phase 2: Customer Flow** | Search, Cart, Orders, Payments | 3-4 days |
| **Phase 3: Catering** | Catering requests, quotes, event management | 2-3 days |
| **Phase 4: Hotel Services** | Room service, reservations, buffets | 1-2 days |
| **Phase 5: Admin Panel** | Verification, order mgmt, analytics, sidebar | 2-3 days |
| **Phase 6: Notifications** | Push, SMS, email templates | 1-2 days |
| **Phase 7: Flutter App** | Customer + Provider screens | 5-7 days |
| **Phase 8: Polish** | Reviews, promos, search optimization, testing | 2-3 days |

**Total Estimated: 19-28 days** (parallel dev with Flutter = ~15-20 days)

---

## FILES TO CREATE

### Backend (Laravel)
```
database/migrations/
  2026_08_08_100001_create_food_providers_table.php
  2026_08_08_100002_create_food_categories_table.php
  2026_08_08_100003_create_food_items_table.php
  2026_08_08_100004_create_food_item_media_table.php
  2026_08_08_100005_create_food_pricing_tiers_table.php
  2026_08_08_100006_create_food_delivery_slots_table.php
  2026_08_08_100007_create_food_orders_table.php
  2026_08_08_100008_create_food_order_items_table.php
  2026_08_08_100009_create_catering_requests_table.php
  2026_08_08_100010_create_catering_quotes_table.php
  2026_08_08_100011_create_hotel_food_services_table.php
  2026_08_08_100012_create_hotel_reservations_table.php
  2026_08_08_100013_create_food_reviews_table.php
  2026_08_08_100014_create_food_promotions_table.php
  2026_08_08_100015_create_food_cart_table.php
  2026_08_08_100016_create_food_wishlists_table.php

app/Models/Food/
  FoodProvider.php
  FoodCategory.php
  FoodItem.php
  FoodItemMedia.php
  FoodPricingTier.php
  FoodDeliverySlot.php
  FoodOrder.php
  FoodOrderItem.php
  CateringRequest.php
  CateringQuote.php
  HotelFoodService.php
  HotelReservation.php
  FoodReview.php
  FoodPromotion.php
  FoodCart.php
  FoodWishlist.php

app/Services/Food/
  FoodSearchService.php
  FoodOrderService.php
  FoodCartService.php
  FoodDeliveryService.php
  CateringService.php
  HotelFoodService.php
  FoodCommissionService.php

app/Http/Controllers/Api/Food/
  FoodCategoryController.php
  FoodItemController.php
  FoodProviderController.php
  FoodCartController.php
  FoodOrderController.php
  CateringController.php
  HotelFoodController.php
  FoodReviewController.php

app/Http/Controllers/Api/Food/Provider/
  ProviderDashboardController.php
  ProviderMenuController.php
  ProviderOrderController.php
  ProviderCateringController.php
  ProviderProfileController.php

app/Http/Controllers/Admin/
  AdminFoodProviderController.php
  AdminFoodOrderController.php
  AdminFoodCateringController.php
  AdminFoodAnalyticsController.php

routes/food.php (included from web.php and api.php)

database/seeders/FoodCategorySeeder.php
database/seeders/FoodAllergenSeeder.php
```

### Frontend (Vue3 Inertia — Admin Panel)
```
resources/js/Pages/Admin/Food/
  Dashboard.vue
  Providers/Index.vue
  Providers/Show.vue
  Providers/Verify.vue
  Orders/Index.vue
  Orders/Show.vue
  Catering/Index.vue
  Reviews/Index.vue
  Promotions/Index.vue
  Commissions/Index.vue
```

### Flutter (Mobile)
```
lib/features/food/
  screens/
    food_home.dart
    food_search.dart
    food_provider_detail.dart
    food_item_detail.dart
    food_cart.dart
    food_checkout.dart
    food_order_tracking.dart
    food_order_history.dart
    catering_request.dart
    catering_detail.dart
    hotel_menu.dart
  provider/
    provider_dashboard.dart
    menu_manager.dart
    order_queue.dart
    catering_inbox.dart
    delivery_slots.dart
  widgets/
    food_card.dart
    food_filter_chips.dart
    order_status_timeline.dart
    catering_quote_card.dart
  models/
    food_item.dart
    food_order.dart
    catering_request.dart
  services/
    food_api_service.dart
    food_search_service.dart
```
