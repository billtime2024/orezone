<script>
import { Link, Head, useForm } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/portal-layout.vue';

export default {
    components: { Link, Head, PortalLayout },
    layout: PortalLayout,
    props: { listing: Object },
    data() {
        const l = this.listing;
        return {
            form: useForm({
                title: l.title,
                description: l.description || '',
                price_per_unit: l.price_per_unit,
                price_unit: l.price_unit,
                security_deposit: l.security_deposit,
                cleaning_fee: l.cleaning_fee,
                address_line1: l.address_line1,
                address_line2: l.address_line2 || '',
                city: l.city,
                state: l.state,
                pincode: l.pincode,
                instant_booking: l.instant_booking,
                rules: l.rules || [],
                ruleInput: '',
                details: this.getTypeDetails(l),
                photos: [],
                remove_photos: [],
            }),
            submitting: false,
            existingPhotos: l.photos || [],
            newPreviews: [],
        };
    },
    computed: {
        typeLabel() {
            return { house: '🏠 House', car: '🚗 Car', commercial: '🏢 Commercial', room: '🛏️ Room' }[this.listing.rental_type] || '';
        },
        allPhotos() {
            return [...this.existingPhotos];
        },
    },
    methods: {
        getTypeDetails(l) {
            if (l.house_details) return l.house_details;
            if (l.car_details) return l.car_details;
            if (l.commercial_details) return l.commercial_details;
            if (l.room_details) return l.room_details;
            return {};
        },
        addRule() {
            if (this.form.ruleInput.trim()) { this.form.rules.push(this.form.ruleInput.trim()); this.form.ruleInput = ''; }
        },
        removeRule(i) { this.form.rules.splice(i, 1); },
        removeExistingPhoto(index) {
            const photoUrl = this.existingPhotos[index];
            this.form.remove_photos.push(photoUrl);
            this.existingPhotos.splice(index, 1);
        },
        handleNewPhotos(e) {
            const files = Array.from(e.target.files);
            this.form.photos = [...this.form.photos, ...files];
            files.forEach(file => {
                const reader = new FileReader();
                reader.onload = (ev) => {
                    this.newPreviews.push(ev.target.result);
                };
                reader.readAsDataURL(file);
            });
        },
        removeNewPhoto(index) {
            this.form.photos.splice(index, 1);
            this.newPreviews.splice(index, 1);
        },
        submit() {
            this.submitting = true;
            this.form.put(`/portal/rentals/${this.listing.id}`, {
                onFinish: () => { this.submitting = false; },
            });
        },
    },
};
</script>

<template>
    <Head :title="`Edit: ${listing.title}`" />
    <div class="container-fluid" style="max-width: 900px;">
        <Link href="/portal/rentals/my" class="text-decoration-none text-muted mb-3 d-inline-block">
            <i class="ri-arrow-left-line"></i> My Listings
        </Link>
        <h4 class="fw-bold mb-4">Edit Listing <span class="badge bg-light text-dark">{{ typeLabel }}</span></h4>

        <form @submit.prevent="submit">
            <!-- Photos Section -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">📸 Photos</h6>

                    <!-- Existing Photos -->
                    <div v-if="existingPhotos.length" class="mb-3">
                        <small class="text-muted fw-semibold">Current Photos (click × to remove)</small>
                        <div class="row g-3 mt-1">
                            <div v-for="(photo, i) in existingPhotos" :key="'existing-'+i" class="col-4 col-md-3">
                                <div class="position-relative">
                                    <img :src="photo" class="rounded w-100" style="height: 120px; object-fit: cover;" />
                                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 rounded-circle" style="width: 24px; height: 24px; font-size: 12px; line-height: 1;" @click="removeExistingPhoto(i)">×</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- New Photos -->
                    <div v-if="newPreviews.length" class="mb-3">
                        <small class="text-muted fw-semibold">New Photos (not yet saved)</small>
                        <div class="row g-3 mt-1">
                            <div v-for="(preview, i) in newPreviews" :key="'new-'+i" class="col-4 col-md-3">
                                <div class="position-relative">
                                    <img :src="preview" class="rounded w-100" style="height: 120px; object-fit: cover;" />
                                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 rounded-circle" style="width: 24px; height: 24px; font-size: 12px; line-height: 1;" @click="removeNewPhoto(i)">×</button>
                                    <span class="badge bg-warning position-absolute bottom-0 start-0 m-1" style="font-size: 10px;">NEW</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Add More -->
                    <div v-if="(existingPhotos.length + newPreviews.length) < 10" class="mt-2">
                        <label class="btn btn-outline-primary">
                            <i class="ri-camera-line me-1"></i> Add More Photos
                            <input type="file" accept="image/*" multiple class="d-none" @change="handleNewPhotos" />
                        </label>
                        <small class="text-muted ms-2">Max 10 photos total. JPG, PNG, WebP. 5MB each.</small>
                    </div>

                    <div v-if="form.remove_photos.length" class="alert alert-warning mt-3 mb-0">
                        <small>⚠️ {{ form.remove_photos.length }} photo(s) will be removed on save.</small>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Basic Information</h6>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Title *</label>
                        <input v-model="form.title" type="text" class="form-control" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Description</label>
                        <textarea v-model="form.description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Price *</label>
                            <input v-model="form.price_per_unit" type="number" class="form-control" required />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Per *</label>
                            <select v-model="form.price_unit" class="form-select"><option value="hour">Hour</option><option value="day">Day</option><option value="month">Month</option><option value="year">Year</option></select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Security Deposit</label>
                            <input v-model="form.security_deposit" type="number" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Cleaning Fee</label>
                            <input v-model="form.cleaning_fee" type="number" class="form-control" />
                        </div>
                    </div>
                    <div class="form-check mt-3">
                        <input v-model="form.instant_booking" type="checkbox" class="form-check-input" id="instantBooking" />
                        <label class="form-check-label" for="instantBooking">Instant Booking</label>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Location</h6>
                    <div class="mb-3"><input v-model="form.address_line1" type="text" class="form-control" placeholder="Address *" required /></div>
                    <div class="mb-3"><input v-model="form.address_line2" type="text" class="form-control" placeholder="Address line 2" /></div>
                    <div class="row g-3">
                        <div class="col-md-4"><input v-model="form.city" type="text" class="form-control" placeholder="City *" required /></div>
                        <div class="col-md-4"><input v-model="form.state" type="text" class="form-control" placeholder="State *" required /></div>
                        <div class="col-md-4"><input v-model="form.pincode" type="text" class="form-control" placeholder="Pincode *" required /></div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Rules</h6>
                    <div class="d-flex gap-2 mb-2">
                        <input v-model="form.ruleInput" type="text" class="form-control" placeholder="Add rule" @keyup.enter.prevent="addRule" />
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
                {{ submitting ? 'Updating...' : 'Update Listing' }}
            </button>
        </form>
    </div>
</template>
