<script>
import { Link, Head, useForm } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/portal-layout.vue';

export default {
    components: { Link, Head, PortalLayout },
    props: {
        user: Object,
        trip: Object,
    },
    data() {
        return {
            form: useForm({
                seat_count: 1,
                pickup_stop_id: '',
                drop_stop_id: '',
                notes: '',
            }),
        };
    },
    computed: {
        maxSeats() {
            return this.trip?.available_seats || 1;
        },
    },
    methods: {
        submit() {
            this.form.post(`/portal/trips/${this.trip.id}/bookings`, {
                onSuccess: () => {
                    this.$inertia.visit('/portal/bookings');
                },
            });
        },
        formatDate(date) {
            if (!date) return '—';
            return new Date(date).toLocaleDateString('en-US', {
                year: 'numeric', month: 'short', day: 'numeric',
                hour: '2-digit', minute: '2-digit',
            });
        },
    },
};
</script>

<template>
    <Head :title="`Book Trip - orezone`" />

    <PortalLayout>
        <div class="mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><Link href="/portal/trips/search">Search Trips</Link></li>
                    <li class="breadcrumb-item"><Link :href="`/portal/trips/${trip.id}`">{{ trip.origin_name }} → {{ trip.destination_name }}</Link></li>
                    <li class="breadcrumb-item active">Book</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-1">Book This Trip</h4>
            <p class="text-muted mb-0">Reserve your seat(s) on this ride</p>
        </div>

        <div class="row g-4">
            <!-- Booking Form -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <form @submit.prevent="submit">
                            <!-- Seat Count -->
                            <div class="mb-3">
                                <label for="seat_count" class="form-label fw-semibold">Number of Seats</label>
                                <input
                                    id="seat_count"
                                    v-model="form.seat_count"
                                    type="number"
                                    class="form-control"
                                    :class="{ 'is-invalid': form.errors.seat_count }"
                                    min="1"
                                    :max="maxSeats"
                                    required
                                />
                                <div class="invalid-feedback">{{ form.errors.seat_count }}</div>
                                <small class="text-muted">{{ maxSeats }} seats available</small>
                            </div>

                            <!-- Pickup Stop -->
                            <div class="mb-3" v-if="trip.stops && trip.stops.length > 0">
                                <label for="pickup_stop_id" class="form-label fw-semibold">Pickup Stop</label>
                                <select
                                    id="pickup_stop_id"
                                    v-model="form.pickup_stop_id"
                                    class="form-select"
                                    :class="{ 'is-invalid': form.errors.pickup_stop_id }"
                                >
                                    <option value="">Select pickup point (optional)</option>
                                    <option v-for="stop in trip.stops" :key="stop.id" :value="stop.id">
                                        {{ stop.name }}
                                    </option>
                                </select>
                                <div class="invalid-feedback">{{ form.errors.pickup_stop_id }}</div>
                            </div>

                            <!-- Drop Stop -->
                            <div class="mb-3" v-if="trip.stops && trip.stops.length > 0">
                                <label for="drop_stop_id" class="form-label fw-semibold">Drop-off Stop</label>
                                <select
                                    id="drop_stop_id"
                                    v-model="form.drop_stop_id"
                                    class="form-select"
                                    :class="{ 'is-invalid': form.errors.drop_stop_id }"
                                >
                                    <option value="">Select drop-off point (optional)</option>
                                    <option v-for="stop in trip.stops" :key="stop.id" :value="stop.id">
                                        {{ stop.name }}
                                    </option>
                                </select>
                                <div class="invalid-feedback">{{ form.errors.drop_stop_id }}</div>
                            </div>

                            <!-- Notes -->
                            <div class="mb-3">
                                <label for="notes" class="form-label fw-semibold">Notes (Optional)</label>
                                <textarea
                                    id="notes"
                                    v-model="form.notes"
                                    class="form-control"
                                    :class="{ 'is-invalid': form.errors.notes }"
                                    rows="3"
                                    placeholder="Any special requests or information for the host..."
                                ></textarea>
                                <div class="invalid-feedback">{{ form.errors.notes }}</div>
                            </div>

                            <!-- Actions -->
                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <Link :href="`/portal/trips/${trip.id}`" class="btn btn-outline-secondary">
                                    Cancel
                                </Link>
                                <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                    <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                                    <i v-else class="ri-bookmark-line me-1"></i>
                                    Book Now
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Trip Summary Sidebar -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-dark mb-3">Trip Summary</h6>
                        <div class="mb-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="ri-map-pin-line text-primary"></i>
                                <span class="fw-semibold">{{ trip.origin_name }}</span>
                            </div>
                            <div class="ms-4" style="border-left: 2px dashed #dee2e6; height: 20px;"></div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="ri-map-pin-2-line text-danger"></i>
                                <span class="fw-semibold">{{ trip.destination_name }}</span>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between mb-2">
                            <small class="text-muted">Departure</small>
                            <small class="fw-medium">{{ formatDate(trip.departure_at) }}</small>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <small class="text-muted">Available Seats</small>
                            <small class="fw-medium">{{ trip.available_seats }}/{{ trip.total_seats }}</small>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <small class="text-muted">Booking Mode</small>
                            <small class="fw-medium text-capitalize">{{ trip.booking_mode }}</small>
                        </div>
                        <div class="d-flex justify-content-between" v-if="trip.host">
                            <small class="text-muted">Host</small>
                            <small class="fw-medium">{{ trip.host.name }}</small>
                        </div>

                        <div v-if="trip.notes" class="mt-3 p-2 bg-light rounded">
                            <small class="text-muted d-block mb-1">Host Notes:</small>
                            <small>{{ trip.notes }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>
