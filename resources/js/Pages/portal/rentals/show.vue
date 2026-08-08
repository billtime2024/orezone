<script>
import { Link, Head, useForm } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/portal-layout.vue';

export default {
    components: { Link, Head, PortalLayout },
    layout: PortalLayout,
    props: {
        listing: Object,
    },
    data() {
        return {
            form: useForm({
                check_in: '',
                check_out: '',
                guests_count: 1,
                guest_message: '',
            }),
            submitting: false,
        };
    },
    computed: {
        nights() {
            if (!this.form.check_in || !this.form.check_out) return 0;
            const diff = new Date(this.form.check_out) - new Date(this.form.check_in);
            return Math.ceil(diff / (1000 * 60 * 60 * 24));
        },
        subtotal() { return this.listing.price_per_unit * this.nights; },
        serviceFee() { return Math.round(this.subtotal * 0.05); },
        total() { return this.subtotal + (this.listing.cleaning_fee || 0) + this.serviceFee; },
    },
    methods: {
        typeLabel(type) {
            const labels = { house: '🏠 House', car: '🚗 Car', commercial: '🏢 Commercial', room: '🛏️ Room' };
            return labels[type] || type;
        },
        formatCurrency(amount) {
            if (!amount) return '—';
            return '₹' + Number(amount).toLocaleString('en-IN');
        },
        submitBooking() {
            this.submitting = true;
            this.form.post(`/portal/rentals/${this.listing.id}/bookings`, {
                onFinish: () => { this.submitting = false; },
            });
        },
    },
};
</script>

<template>
    <Head :title="listing.title" />

    <div class="container-fluid">
        <Link href="/portal/rentals" class="text-decoration-none text-muted mb-3 d-inline-block">
            <i class="ri-arrow-left-line"></i> Back to Rentals
        </Link>

        <div class="row g-4">
            <!-- Listing Details -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div v-if="listing.photos?.length" class="card-img-top" style="height: 300px; overflow: hidden;">
                        <img :src="listing.photos[0]" class="w-100 h-100" style="object-fit: cover;" />
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-light text-dark">{{ typeLabel(listing.rental_type) }}</span>
                            <span class="fw-bold text-success fs-4">{{ formatCurrency(listing.price_per_unit) }}<small class="text-muted fw-normal">/{{ listing.price_unit }}</small></span>
                        </div>
                        <h4 class="fw-bold">{{ listing.title }}</h4>
                        <p class="text-muted"><i class="ri-map-pin-line"></i> {{ listing.address_line1 }}, {{ listing.city }}, {{ listing.state }} - {{ listing.pincode }}</p>
                        <p v-if="listing.description" class="mt-3">{{ listing.description }}</p>

                        <div class="mt-3" v-if="listing.details">
                            <h6 class="fw-bold">Details</h6>
                            <div class="d-flex flex-wrap gap-2">
                                <template v-if="listing.rental_type === 'house'">
                                    <span class="badge bg-light text-dark" v-if="listing.details.bedrooms">🛏️ {{ listing.details.bedrooms }} Beds</span>
                                    <span class="badge bg-light text-dark" v-if="listing.details.bathrooms">🚿 {{ listing.details.bathrooms }} Baths</span>
                                    <span class="badge bg-light text-dark" v-if="listing.details.furnished">🛋️ Furnished</span>
                                    <span class="badge bg-light text-dark" v-if="listing.details.parking">🅿️ Parking</span>
                                    <span class="badge bg-light text-dark" v-if="listing.details.ac">❄️ AC</span>
                                    <span class="badge bg-light text-dark" v-if="listing.details.wifi">📶 WiFi</span>
                                </template>
                                <template v-else-if="listing.rental_type === 'car'">
                                    <span class="badge bg-light text-dark">{{ listing.details.make }} {{ listing.details.model }}</span>
                                    <span class="badge bg-light text-dark" v-if="listing.details.year">📅 {{ listing.details.year }}</span>
                                    <span class="badge bg-light text-dark" v-if="listing.details.fuel_type">⛽ {{ listing.details.fuel_type }}</span>
                                    <span class="badge bg-light text-dark" v-if="listing.details.transmission">🔄 {{ listing.details.transmission }}</span>
                                </template>
                                <template v-else-if="listing.rental_type === 'room'">
                                    <span class="badge bg-light text-dark" v-if="listing.details.room_type">{{ listing.details.room_type }}</span>
                                    <span class="badge bg-light text-dark" v-if="listing.details.meals_included">🍽️ Meals Included</span>
                                    <span class="badge bg-light text-dark" v-if="listing.details.ac">❄️ AC</span>
                                    <span class="badge bg-light text-dark" v-if="listing.details.wifi">📶 WiFi</span>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Booking Sidebar -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">Book This Listing</h5>
                        <form @submit.prevent="submitBooking">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Check-in</label>
                                <input v-model="form.check_in" type="date" class="form-control" :min="new Date().toISOString().split('T')[0]" required />
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Check-out</label>
                                <input v-model="form.check_out" type="date" class="form-control" :min="form.check_in || new Date().toISOString().split('T')[0]" required />
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Guests</label>
                                <input v-model.number="form.guests_count" type="number" min="1" max="10" class="form-control" required />
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Message (optional)</label>
                                <textarea v-model="form.guest_message" class="form-control" rows="2" placeholder="Any special requests..."></textarea>
                            </div>

                            <div v-if="nights > 0" class="bg-light rounded-3 p-3 mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <small>{{ formatCurrency(listing.price_per_unit) }} × {{ nights }} nights</small>
                                    <small class="fw-semibold">{{ formatCurrency(subtotal) }}</small>
                                </div>
                                <div class="d-flex justify-content-between mb-1" v-if="listing.cleaning_fee > 0">
                                    <small>Cleaning Fee</small>
                                    <small>{{ formatCurrency(listing.cleaning_fee) }}</small>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <small>Service Fee (5%)</small>
                                    <small>{{ formatCurrency(serviceFee) }}</small>
                                </div>
                                <hr class="my-2" />
                                <div class="d-flex justify-content-between">
                                    <strong>Total</strong>
                                    <strong class="text-success">{{ formatCurrency(total) }}</strong>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100" :disabled="submitting || nights < 1">
                                {{ submitting ? 'Booking...' : 'Book Now' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
