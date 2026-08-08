<script>
import { Link, Head, useForm } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/portal-layout.vue';

export default {
    components: { Link, Head, PortalLayout },
    props: {
        user: Object,
        vehicles: Array,
    },
    data() {
        return {
            form: useForm({
                vehicle_id: '',
                origin_name: '',
                destination_name: '',
                departure_at: '',
                total_seats: 4,
                booking_mode: 'instant',
                notes: '',
            }),
        };
    },
    methods: {
        submit() {
            this.form.post('/portal/trips', {
                onSuccess: () => {
                    this.$inertia.visit('/portal/trips');
                },
            });
        },
    },
};
</script>

<template>
    <Head title="Create Trip - orezone" />

    <PortalLayout>
        <div class="mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><Link href="/portal/trips">My Trips</Link></li>
                    <li class="breadcrumb-item active">Create Trip</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-1">Create New Trip</h4>
            <p class="text-muted mb-0">Offer a ride to your destination</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <form @submit.prevent="submit">
                            <!-- Vehicle Selection -->
                            <div class="mb-3">
                                <label for="vehicle_id" class="form-label fw-semibold">Select Vehicle</label>
                                <select
                                    id="vehicle_id"
                                    v-model="form.vehicle_id"
                                    class="form-select"
                                    :class="{ 'is-invalid': form.errors.vehicle_id }"
                                    required
                                >
                                    <option value="">Choose a vehicle</option>
                                    <option v-for="v in vehicles" :key="v.id" :value="v.id">
                                        {{ v.brand }} {{ v.model }} ({{ v.registration_number }}) — {{ v.seating_capacity }} seats
                                    </option>
                                </select>
                                <div class="invalid-feedback">{{ form.errors.vehicle_id }}</div>
                                <small v-if="!vehicles || vehicles.length === 0" class="text-warning">
                                    <i class="ri-alert-line me-1"></i>
                                    You need to add and verify a vehicle first.
                                    <Link href="/portal/vehicles/create" class="text-decoration-underline">Add Vehicle</Link>
                                </small>
                            </div>

                            <!-- Route -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="origin_name" class="form-label fw-semibold">Origin</label>
                                    <input
                                        id="origin_name"
                                        v-model="form.origin_name"
                                        type="text"
                                        class="form-control"
                                        :class="{ 'is-invalid': form.errors.origin_name }"
                                        placeholder="e.g. Mumbai, Maharashtra"
                                        required
                                    />
                                    <div class="invalid-feedback">{{ form.errors.origin_name }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="destination_name" class="form-label fw-semibold">Destination</label>
                                    <input
                                        id="destination_name"
                                        v-model="form.destination_name"
                                        type="text"
                                        class="form-control"
                                        :class="{ 'is-invalid': form.errors.destination_name }"
                                        placeholder="e.g. Pune, Maharashtra"
                                        required
                                    />
                                    <div class="invalid-feedback">{{ form.errors.destination_name }}</div>
                                </div>
                            </div>

                            <!-- Departure & Seats -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="departure_at" class="form-label fw-semibold">Departure Date & Time</label>
                                    <input
                                        id="departure_at"
                                        v-model="form.departure_at"
                                        type="datetime-local"
                                        class="form-control"
                                        :class="{ 'is-invalid': form.errors.departure_at }"
                                        required
                                    />
                                    <div class="invalid-feedback">{{ form.errors.departure_at }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="total_seats" class="form-label fw-semibold">Total Seats</label>
                                    <input
                                        id="total_seats"
                                        v-model="form.total_seats"
                                        type="number"
                                        class="form-control"
                                        :class="{ 'is-invalid': form.errors.total_seats }"
                                        min="1"
                                        max="20"
                                        required
                                    />
                                    <div class="invalid-feedback">{{ form.errors.total_seats }}</div>
                                </div>
                            </div>

                            <!-- Booking Mode -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Booking Mode</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input
                                            class="form-check-input"
                                            type="radio"
                                            v-model="form.booking_mode"
                                            value="instant"
                                            id="modeInstant"
                                        />
                                        <label class="form-check-label" for="modeInstant">
                                            <strong>Instant</strong>
                                            <small class="d-block text-muted">Bookings confirmed automatically</small>
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input
                                            class="form-check-input"
                                            type="radio"
                                            v-model="form.booking_mode"
                                            value="request"
                                            id="modeRequest"
                                        />
                                        <label class="form-check-label" for="modeRequest">
                                            <strong>Request</strong>
                                            <small class="d-block text-muted">You approve each booking</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="invalid-feedback d-block">{{ form.errors.booking_mode }}</div>
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
                                    placeholder="Any additional information about the trip..."
                                ></textarea>
                                <div class="invalid-feedback">{{ form.errors.notes }}</div>
                            </div>

                            <!-- Actions -->
                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <Link href="/portal/trips" class="btn btn-outline-secondary">
                                    Cancel
                                </Link>
                                <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                    <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                                    <i v-else class="ri-add-line me-1"></i>
                                    Create Trip
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>
