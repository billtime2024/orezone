<script>
import { Link, Head, router } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/portal-layout.vue';

export default {
    components: { Link, Head, PortalLayout },
    props: {
        user: Object,
        trip: Object,
        isHost: Boolean,
    },
    methods: {
        publishTrip() {
            if (confirm('Publish this trip? It will be visible to travelers.')) {
                router.post(`/portal/trips/${this.trip.id}/publish`);
            }
        },
        cancelTrip() {
            if (confirm('Cancel this trip? All pending and confirmed bookings will be cancelled.')) {
                router.post(`/portal/trips/${this.trip.id}/cancel`);
            }
        },
        startTrip() {
            if (confirm('Start this trip? This should be done when you begin the journey.')) {
                router.post(`/portal/trips/${this.trip.id}/start`);
            }
        },
        completeTrip() {
            if (confirm('Mark this trip as completed?')) {
                router.post(`/portal/trips/${this.trip.id}/complete`);
            }
        },
        statusBadge(status) {
            const map = {
                draft: 'bg-secondary',
                published: 'bg-primary',
                active: 'bg-info',
                in_progress: 'bg-warning text-dark',
                completed: 'bg-success',
                cancelled: 'bg-danger',
            };
            return map[status] || 'bg-secondary';
        },
        bookingStatusBadge(status) {
            const map = {
                requested: 'bg-warning text-dark',
                accepted: 'bg-info',
                confirmed: 'bg-primary',
                active: 'bg-success',
                completed: 'bg-secondary',
                cancelled: 'bg-danger',
                rejected: 'bg-danger',
            };
            return map[status] || 'bg-secondary';
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
    <Head :title="`${trip.origin_name} → ${trip.destination_name} - orezone`" />

    <PortalLayout>
        <div class="mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><Link href="/portal/trips">My Trips</Link></li>
                    <li class="breadcrumb-item active">{{ trip.origin_name }} → {{ trip.destination_name }}</li>
                </ol>
            </nav>
        </div>

        <div class="row g-4">
            <!-- Trip Info -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div>
                                <h4 class="fw-bold text-dark mb-1">
                                    {{ trip.origin_name }}
                                    <i class="ri-arrow-right-line mx-2 text-muted"></i>
                                    {{ trip.destination_name }}
                                </h4>
                                <p class="text-muted mb-0">Trip #{{ trip.id }}</p>
                            </div>
                            <span class="badge fs-6" :class="statusBadge(trip.status)">
                                {{ trip.status?.replace('_', ' ') }}
                            </span>
                        </div>

                        <!-- Trip Details Grid -->
                        <div class="row g-3 mb-4">
                            <div class="col-sm-6 col-md-3">
                                <div class="bg-light rounded-3 p-3 text-center">
                                    <i class="ri-calendar-line text-primary fs-4"></i>
                                    <small class="text-muted d-block mt-1">Departure</small>
                                    <strong class="small">{{ formatDate(trip.departure_at) }}</strong>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <div class="bg-light rounded-3 p-3 text-center">
                                    <i class="ri-seat-line text-success fs-4"></i>
                                    <small class="text-muted d-block mt-1">Seats</small>
                                    <strong class="small">{{ trip.available_seats }}/{{ trip.total_seats }} available</strong>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <div class="bg-light rounded-3 p-3 text-center">
                                    <i class="ri-flashlight-line text-warning fs-4"></i>
                                    <small class="text-muted d-block mt-1">Booking Mode</small>
                                    <strong class="small text-capitalize">{{ trip.booking_mode }}</strong>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <div class="bg-light rounded-3 p-3 text-center">
                                    <i class="ri-car-line text-info fs-4"></i>
                                    <small class="text-muted d-block mt-1">Vehicle</small>
                                    <strong class="small">{{ trip.vehicle?.brand }} {{ trip.vehicle?.model }}</strong>
                                </div>
                            </div>
                        </div>

                        <!-- Host Info -->
                        <div v-if="trip.host" class="mb-4 p-3 bg-light rounded-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    {{ trip.host.name?.charAt(0) }}
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ trip.host.name }}</div>
                                    <small class="text-muted">Host</small>
                                </div>
                            </div>
                        </div>

                        <!-- Stops -->
                        <div v-if="trip.stops && trip.stops.length > 0" class="mb-4">
                            <h6 class="fw-bold text-dark mb-3">Stops</h6>
                            <div class="list-group">
                                <div class="list-group-item" v-for="(stop, index) in trip.stops" :key="stop.id">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 12px;">
                                            {{ index + 1 }}
                                        </div>
                                        <div class="flex-grow-1">
                                            <strong>{{ stop.name }}</strong>
                                            <small v-if="stop.estimated_arrival" class="d-block text-muted">
                                                ETA: {{ formatDate(stop.estimated_arrival) }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div v-if="trip.notes" class="mb-4">
                            <h6 class="fw-bold text-dark mb-2">Notes</h6>
                            <p class="text-muted mb-0">{{ trip.notes }}</p>
                        </div>
                    </div>
                </div>

                <!-- Bookings List (Host only) -->
                <div v-if="isHost && trip.bookings && trip.bookings.length > 0" class="card border-0 shadow-sm mt-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold text-dark mb-3">Bookings ({{ trip.bookings.length }})</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Traveler</th>
                                        <th>Seats</th>
                                        <th>Pickup</th>
                                        <th>Drop</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="booking in trip.bookings" :key="booking.id">
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 11px;">
                                                    {{ booking.traveler?.name?.charAt(0) || '?' }}
                                                </div>
                                                <span>{{ booking.traveler?.name || 'N/A' }}</span>
                                            </div>
                                        </td>
                                        <td>{{ booking.seat_count }}</td>
                                        <td>{{ booking.pickup_stop?.name || '—' }}</td>
                                        <td>{{ booking.drop_stop?.name || '—' }}</td>
                                        <td>
                                            <span class="badge" :class="bookingStatusBadge(booking.status)">
                                                {{ booking.status }}
                                            </span>
                                        </td>
                                        <td>
                                            <Link :href="`/portal/bookings/${booking.id}`" class="btn btn-sm btn-outline-primary">
                                                View
                                            </Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Actions -->
            <div class="col-lg-4">
                <!-- Host Actions -->
                <div v-if="isHost" class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-dark mb-3">Host Actions</h6>
                        <div class="d-grid gap-2">
                            <button
                                v-if="trip.status === 'draft'"
                                class="btn btn-success"
                                @click="publishTrip"
                            >
                                <i class="ri-send-plane-line me-1"></i> Publish Trip
                            </button>
                            <Link
                                v-if="trip.status === 'draft'"
                                :href="`/portal/trips/${trip.id}/edit`"
                                class="btn btn-outline-primary"
                            >
                                <i class="ri-edit-line me-1"></i> Edit Trip
                            </Link>
                            <button
                                v-if="trip.status === 'draft' || trip.status === 'published'"
                                class="btn btn-outline-danger"
                                @click="cancelTrip"
                            >
                                <i class="ri-close-circle-line me-1"></i> Cancel Trip
                            </button>
                            <button
                                v-if="trip.status === 'published' && trip.available_seats === 0"
                                class="btn btn-warning"
                                @click="startTrip"
                            >
                                <i class="ri-play-circle-line me-1"></i> Start Trip
                            </button>
                            <button
                                v-if="trip.status === 'in_progress'"
                                class="btn btn-success"
                                @click="completeTrip"
                            >
                                <i class="ri-check-double-line me-1"></i> Complete Trip
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Traveler Actions -->
                <div v-if="!isHost && trip.status === 'published' && trip.available_seats > 0" class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-dark mb-3">Book This Trip</h6>
                        <div class="d-grid gap-2">
                            <Link :href="`/portal/trips/${trip.id}/book`" class="btn btn-primary">
                                <i class="ri-bookmark-line me-1"></i> Book Now
                            </Link>
                        </div>
                        <small class="text-muted d-block text-center mt-2">
                            {{ trip.available_seats }} seats available
                        </small>
                    </div>
                </div>

                <!-- Trip Info Summary -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-dark mb-3">Trip Summary</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <small class="text-muted">Status</small>
                            <span class="badge" :class="statusBadge(trip.status)">{{ trip.status }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <small class="text-muted">Created</small>
                            <small>{{ formatDate(trip.created_at) }}</small>
                        </div>
                        <div class="d-flex justify-content-between" v-if="trip.arrival_at">
                            <small class="text-muted">Arrived</small>
                            <small>{{ formatDate(trip.arrival_at) }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>
