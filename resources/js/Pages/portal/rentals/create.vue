<script>
import { Link, Head, useForm } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/portal-layout.vue';

export default {
    components: { Link, Head, PortalLayout },
    layout: PortalLayout,
    data() {
        return {
            form: useForm({
                rental_type: 'house',
                title: '',
                description: '',
                price_per_unit: '',
                price_unit: 'month',
                security_deposit: '',
                cleaning_fee: '',
                address_line1: '',
                address_line2: '',
                city: '',
                state: 'Tamil Nadu',
                pincode: '',
                instant_booking: false,
                rules: [],
                ruleInput: '',
                details: {},
                photos: [],
            }),
            submitting: false,
            previews: [],
        };
    },
    watch: {
        'form.rental_type'(val) {
            this.form.details = this.getDefaultDetails(val);
        },
    },
    methods: {
        getDefaultDetails(type) {
            switch (type) {
                case 'house': return { bedrooms: 1, bathrooms: 1, furnished: false, parking: false, ac: false, wifi: false, property_type: 'apartment', area_sqft: '' };
                case 'car': return { make: '', model: '', year: new Date().getFullYear(), fuel_type: 'petrol', transmission: 'manual', seats: 5, self_drive: true, with_driver: false };
                case 'commercial': return { property_type: 'office', area_sqft: '', furnished: false, ac: false, power_backup: false, parking: false, lift: false, lease_type: 'bare_shell' };
                case 'room': return { room_type: 'single', stay_type: 'pg', meals_included: false, meal_plan: 'none', ac: false, wifi: false, laundry: false, total_rooms: 1, available_rooms: 1 };
                default: return {};
            }
        },
        addRule() {
            if (this.form.ruleInput.trim()) {
                this.form.rules.push(this.form.ruleInput.trim());
                this.form.ruleInput = '';
            }
        },
        removeRule(index) {
            this.form.rules.splice(index, 1);
        },
        handlePhotos(e) {
            const files = Array.from(e.target.files);
            this.form.photos = [...this.form.photos, ...files];
            files.forEach(file => {
                const reader = new FileReader();
                reader.onload = (ev) => {
                    this.previews.push(ev.target.result);
                };
                reader.readAsDataURL(file);
            });
        },
        removePhoto(index) {
            this.form.photos.splice(index, 1);
            this.previews.splice(index, 1);
        },
        submit() {
            this.submitting = true;
            this.form.post('/portal/rentals', {
                onFinish: () => { this.submitting = false; },
            });
        },
    },
    mounted() {
        this.form.details = this.getDefaultDetails('house');
    },
};
</script>

<template>
    <Head title="Create Listing" />
    <div class="container-fluid" style="max-width: 900px;">
        <Link href="/portal/rentals/my" class="text-decoration-none text-muted mb-3 d-inline-block">
            <i class="ri-arrow-left-line"></i> My Listings
        </Link>
        <h4 class="fw-bold mb-4">Create New Listing</h4>

        <form @submit.prevent="submit">
            <!-- Rental Type -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Rental Type</h6>
                    <div class="row g-2">
                        <div class="col-3" v-for="t in [{v:'house',l:'🏠 House'},{v:'car',l:'🚗 Car'},{v:'commercial',l:'🏢 Commercial'},{v:'room',l:'🛏️ Room'}]" :key="t.v">
                            <label class="btn btn-outline-primary w-100" :class="{ active: form.rental_type === t.v }">
                                <input type="radio" v-model="form.rental_type" :value="t.v" class="d-none" /> {{ t.l }}
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Photos -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">📸 Photos (up to 10)</h6>
                    <div class="row g-3">
                        <div v-for="(preview, i) in previews" :key="i" class="col-4 col-md-3">
                            <div class="position-relative">
                                <img :src="preview" class="rounded w-100" style="height: 120px; object-fit: cover;" />
                                <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 rounded-circle" style="width: 24px; height: 24px; font-size: 12px; line-height: 1;" @click="removePhoto(i)">×</button>
                            </div>
                        </div>
                        <div v-if="previews.length < 10" class="col-4 col-md-3">
                            <label class="d-flex align-items-center justify-content-center border border-2 border-dashed rounded cursor-pointer" style="height: 120px; cursor: pointer;">
                                <div class="text-center">
                                    <i class="ri-camera-line fs-3 text-muted"></i>
                                    <div class="small text-muted">Add Photo</div>
                                </div>
                                <input type="file" accept="image/*" multiple class="d-none" @change="handlePhotos" />
                            </label>
                        </div>
                    </div>
                    <small class="text-muted mt-2 d-block">JPG, PNG, WebP. Max 5MB each.</small>
                </div>
            </div>

            <!-- Basic Info -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Basic Information</h6>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Title *</label>
                        <input v-model="form.title" type="text" class="form-control" placeholder="e.g. Spacious 3BHK in Anna Nagar" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Description</label>
                        <textarea v-model="form.description" class="form-control" rows="3" placeholder="Describe your listing..."></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Price *</label>
                            <input v-model="form.price_per_unit" type="number" class="form-control" placeholder="e.g. 25000" required />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Per *</label>
                            <select v-model="form.price_unit" class="form-select">
                                <option value="hour">Hour</option>
                                <option value="day">Day</option>
                                <option value="month">Month</option>
                                <option value="year">Year</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Security Deposit</label>
                            <input v-model="form.security_deposit" type="number" class="form-control" placeholder="₹ 0" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Cleaning Fee</label>
                            <input v-model="form.cleaning_fee" type="number" class="form-control" placeholder="₹ 0" />
                        </div>
                    </div>
                    <div class="form-check mt-3">
                        <input v-model="form.instant_booking" type="checkbox" class="form-check-input" id="instantBooking" />
                        <label class="form-check-label" for="instantBooking">Instant Booking (auto-confirm)</label>
                    </div>
                </div>
            </div>

            <!-- Location -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Location</h6>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Address *</label>
                        <input v-model="form.address_line1" type="text" class="form-control" required />
                    </div>
                    <div class="mb-3">
                        <input v-model="form.address_line2" type="text" class="form-control" placeholder="Address line 2 (optional)" />
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">City *</label>
                            <input v-model="form.city" type="text" class="form-control" required />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">State *</label>
                            <input v-model="form.state" type="text" class="form-control" required />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Pincode *</label>
                            <input v-model="form.pincode" type="text" class="form-control" required />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Type-Specific Details -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">{{ form.rental_type === 'house' ? '🏠 House' : form.rental_type === 'car' ? '🚗 Car' : form.rental_type === 'commercial' ? '🏢 Commercial' : '🛏️ Room' }} Details</h6>

                    <template v-if="form.rental_type === 'house'">
                        <div class="row g-3">
                            <div class="col-md-4"><label class="form-label small">Bedrooms</label><input v-model.number="form.details.bedrooms" type="number" min="1" class="form-control" /></div>
                            <div class="col-md-4"><label class="form-label small">Bathrooms</label><input v-model.number="form.details.bathrooms" type="number" min="1" class="form-control" /></div>
                            <div class="col-md-4"><label class="form-label small">Area (sqft)</label><input v-model="form.details.area_sqft" type="number" class="form-control" /></div>
                            <div class="col-md-4"><label class="form-label small">Property Type</label><select v-model="form.details.property_type" class="form-select"><option value="apartment">Apartment</option><option value="independent_house">Independent House</option><option value="villa">Villa</option></select></div>
                            <div class="col-md-8 d-flex gap-4 align-items-end"><div class="form-check"><input v-model="form.details.furnished" type="checkbox" class="form-check-input" id="furnished" /><label class="form-check-label" for="furnished">Furnished</label></div><div class="form-check"><input v-model="form.details.parking" type="checkbox" class="form-check-input" id="parking" /><label class="form-check-label" for="parking">Parking</label></div><div class="form-check"><input v-model="form.details.ac" type="checkbox" class="form-check-input" id="ac" /><label class="form-check-label" for="ac">AC</label></div><div class="form-check"><input v-model="form.details.wifi" type="checkbox" class="form-check-input" id="wifi" /><label class="form-check-label" for="wifi">WiFi</label></div></div>
                        </div>
                    </template>

                    <template v-if="form.rental_type === 'car'">
                        <div class="row g-3">
                            <div class="col-md-3"><label class="form-label small">Make *</label><input v-model="form.details.make" type="text" class="form-control" placeholder="Toyota" required /></div>
                            <div class="col-md-3"><label class="form-label small">Model *</label><input v-model="form.details.model" type="text" class="form-control" placeholder="Innova" required /></div>
                            <div class="col-md-3"><label class="form-label small">Year *</label><input v-model.number="form.details.year" type="number" class="form-control" required /></div>
                            <div class="col-md-3"><label class="form-label small">Seats</label><input v-model.number="form.details.seats" type="number" min="1" class="form-control" /></div>
                            <div class="col-md-4"><label class="form-label small">Fuel Type</label><select v-model="form.details.fuel_type" class="form-select"><option value="petrol">Petrol</option><option value="diesel">Diesel</option><option value="electric">Electric</option><option value="hybrid">Hybrid</option></select></div>
                            <div class="col-md-4"><label class="form-label small">Transmission</label><select v-model="form.details.transmission" class="form-select"><option value="manual">Manual</option><option value="automatic">Automatic</option></select></div>
                            <div class="col-md-4 d-flex gap-4 align-items-end"><div class="form-check"><input v-model="form.details.self_drive" type="checkbox" class="form-check-input" id="selfDrive" /><label class="form-check-label" for="selfDrive">Self Drive</label></div><div class="form-check"><input v-model="form.details.with_driver" type="checkbox" class="form-check-input" id="withDriver" /><label class="form-check-label" for="withDriver">With Driver</label></div></div>
                        </div>
                    </template>

                    <template v-if="form.rental_type === 'commercial'">
                        <div class="row g-3">
                            <div class="col-md-4"><label class="form-label small">Property Type</label><select v-model="form.details.property_type" class="form-select"><option value="office">Office</option><option value="shop">Shop</option><option value="warehouse">Warehouse</option><option value="co_working">Co-working</option></select></div>
                            <div class="col-md-4"><label class="form-label small">Area (sqft) *</label><input v-model="form.details.area_sqft" type="number" class="form-control" required /></div>
                            <div class="col-md-4"><label class="form-label small">Lease Type</label><select v-model="form.details.lease_type" class="form-select"><option value="bare_shell">Bare Shell</option><option value="fitted">Fitted</option><option value="semi_furnished">Semi Furnished</option><option value="fully_furnished">Fully Furnished</option></select></div>
                            <div class="col-md-8 d-flex gap-4 align-items-end flex-wrap"><div class="form-check"><input v-model="form.details.furnished" type="checkbox" class="form-check-input" id="cFurnished" /><label class="form-check-label" for="cFurnished">Furnished</label></div><div class="form-check"><input v-model="form.details.ac" type="checkbox" class="form-check-input" id="cAc" /><label class="form-check-label" for="cAc">AC</label></div><div class="form-check"><input v-model="form.details.power_backup" type="checkbox" class="form-check-input" id="cPower" /><label class="form-check-label" for="cPower">Power Backup</label></div><div class="form-check"><input v-model="form.details.parking" type="checkbox" class="form-check-input" id="cParking" /><label class="form-check-label" for="cParking">Parking</label></div><div class="form-check"><input v-model="form.details.lift" type="checkbox" class="form-check-input" id="cLift" /><label class="form-check-label" for="cLift">Lift</label></div></div>
                        </div>
                    </template>

                    <template v-if="form.rental_type === 'room'">
                        <div class="row g-3">
                            <div class="col-md-4"><label class="form-label small">Room Type</label><select v-model="form.details.room_type" class="form-select"><option value="single">Single</option><option value="double">Double</option><option value="triple">Triple</option><option value="dorm">Dorm</option><option value="suite">Suite</option></select></div>
                            <div class="col-md-4"><label class="form-label small">Stay Type</label><select v-model="form.details.stay_type" class="form-select"><option value="pg">PG</option><option value="hostel">Hostel</option><option value="hotel">Hotel</option><option value="homestay">Homestay</option></select></div>
                            <div class="col-md-4"><label class="form-label small">Total Rooms</label><input v-model.number="form.details.total_rooms" type="number" min="1" class="form-control" /></div>
                            <div class="col-md-8 d-flex gap-4 align-items-end flex-wrap"><div class="form-check"><input v-model="form.details.meals_included" type="checkbox" class="form-check-input" id="rMeals" /><label class="form-check-label" for="rMeals">Meals Included</label></div><div class="form-check"><input v-model="form.details.ac" type="checkbox" class="form-check-input" id="rAc" /><label class="form-check-label" for="rAc">AC</label></div><div class="form-check"><input v-model="form.details.wifi" type="checkbox" class="form-check-input" id="rWifi" /><label class="form-check-label" for="rWifi">WiFi</label></div><div class="form-check"><input v-model="form.details.laundry" type="checkbox" class="form-check-input" id="rLaundry" /><label class="form-check-label" for="rLaundry">Laundry</label></div></div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Rules -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">House Rules</h6>
                    <div class="d-flex gap-2 mb-2">
                        <input v-model="form.ruleInput" type="text" class="form-control" placeholder="e.g. No smoking" @keyup.enter.prevent="addRule" />
                        <button type="button" class="btn btn-outline-primary" @click="addRule">Add</button>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <span v-for="(rule, i) in form.rules" :key="i" class="badge bg-light text-dark d-flex align-items-center gap-1">
                            {{ rule }} <button type="button" class="btn-close btn-close-sm" @click="removeRule(i)" style="font-size: 10px;"></button>
                        </span>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-100 mb-4" :disabled="submitting">
                {{ submitting ? 'Creating...' : 'Create Listing' }}
            </button>
        </form>
    </div>
</template>
