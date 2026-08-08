<script>
import { Link, Head, router } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/portal-layout.vue';

export default {
    components: { Link, Head, PortalLayout },
    props: {
        user: Object,
        booking: Object,
        isHost: Boolean,
    },
    methods: {
        cancelBooking() {
            if (confirm('Are you sure you want to cancel this booking?')) {
                router.post(`/portal/bookings/${this.booking.id}/cancel`);
            }
        },
        completeBooking() {
            if (confirm('Mark this booking as completed?')) {
                router.post(`/portal/bookings/${this.booking.id}/complete`);
            }
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
                no_show: 'bg-dark',
            };
            return map[status] || 'bg-secondary';
        },
        tripStatusBadge(status) {
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
        formatDate(date) {
            if (!date) return '—';
            return new Date(date).toLocaleDateString('en-US', {
                year: 'numeric', month: 'short', day: 'numeric',
                hour: '2-digit', minute: '2-digit',
            });
        },
        canCancel() {
            return ['requested', 'confirmed'].includes(this.booking.status);
        },
        canComplete() {
            return this.isHost && this.booking.status === 'confirmed' && this.booking.trip?.status === 'in_progress';
        },
    },
};
</script>

<template>
    <Head :title="`Booking #${booking.id} - orezone`" />

    <PortalLayout>
        <div class="mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><Link href="/portal/bookings">My Bookings</Link></li>
                    <li class="breadcrumb-item active">Booking #{{ booking.id }}</li>
                </ol>
            </nav>
        </div>

        <div class="row g-4">
            <!-- Booking Info -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div>
                                <h4 class="fw-bold text-dark mb-1">Booking #{{ booking.id }}</h4>
                                <p class="text-muted mb-0">
                                    Created {{ formatDate(booking.created_at) }}
                                </p>
                            </div>
                            <span class="badge fs-6" :class="bookingStatusBadge(booking.status)">
                                {{ booking.status }}
                            </span>
                        </div>

                        <!-- Trip Info -->
                        <div v-if="booking.trip" class="bg-light rounded-3 p-3 mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold mb-0">Trip Details</h6>
                                <Link :href="`/portal/trips/${booking.trip.id}`" class="btn btn-sm btn-outline-primary">
                                    View Trip <i class="ri-external-link-line"></i>
                                </Link>
                            </div>
                            <div class="row g-2 mt-2">
                                <div class="col-sm-6">
                                    <small class="text-muted">Route</small>
                                    <div class="fw-medium">
                                        {{ booking.trip.origin_name }}
                                        <i class="ri-arrow-right-line mx-1 text-muted"></i>
                                        {{ booking.trip.destination_name }}
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <small class="text-muted">Departure</small>
                                    <div class="fw-medium">{{ formatDate(booking.trip.departure_at) }}</div>
                                </div>
                                <div class="col-sm-6">
                                    <small class="text-muted">Trip Status</small>
                                    <div>
                                        <span class="badge" :class="tripStatusBadge(booking.trip.status)">
                                            {{ booking.trip.status }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <small class="text-muted">Booking Mode</small>
                                    <div class="fw-medium text-capitalize">{{ booking.trip.booking_mode }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Booking Details -->
                        <div class="row g-3 mb-4">
                            <div class="col-sm-6 col-md-3">
                                <div class="bg-light rounded-3 p-3 text-center">
                                    <i class="ri-seat-line text-primary fs-4"></i>
                                    <small class="text-muted d-block mt-1">Seats</small>
                                    <strong>{{ booking.seat_count }}</strong>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <div class="bg-light rounded-3 p-3 text-center">
                                    <i class="ri-map-pin-line text-success fs-4"></i>
                                    <small class="text-muted d-block mt-1">Pickup</small>
                                    <strong class="small">{{ booking.pickup_stop?.name || '—' }}</strong>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <div class="bg-light rounded-3 p-3 text-center">
                                    <i class="ri-map-pin-2-line text-danger fs-4"></i>
                                    <small class="text-muted d-block mt-1">Drop</small>
                                    <strong class="small">{{ booking.drop_stop?.name || '—' }}</strong>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <div class="bg-light rounded-3 p-3 text-center">
                                    <i class="ri-money-rupee-circle-line text-warning fs-4"></i>
                                    <small class="text-muted d-block mt-1">Fee</small>
                                    <strong>₹{{ booking.total_platform_fee || '0.00' }}</strong>
                                </div>
                            </div>
                        </div>

                        <!-- Traveler / Host Info -->
                        <div class="row g-3">
                            <div class="col-sm-6" v-if="booking.traveler">
                                <div class="bg-light rounded-3 p-3">
                                    <small class="text-muted d-block mb-2">Traveler</small>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                            {{ booking.traveler.name?.charAt(0) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold small">{{ booking.traveler.name }}</div>
                                            <small class="text-muted">{{ booking.traveler.email }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6" v-if="booking.host">
                                <div class="bg-light rounded-3 p-3">
                                    <small class="text-muted d-block mb-2">Host</small>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                            {{ booking.host.name?.charAt(0) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold small">{{ booking.host.name }}</div>
                                            <small class="text-muted">{{ booking.host.email }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Status Timeline -->
                        <div class="mt-4 pt-3 border-top">
                            <h6 class="fw-bold text-dark mb-3">Status Timeline</h6>
                            <div class="d-flex flex-column gap-2">
                                <div class="d-flex align-items-center gap-2" v-if="booking.requested_at">
                                    <div class="bg-warning rounded-circle" style="width: 8px; height: 8px;"></div>
                                    <small class="text-muted">Requested: {{ formatDate(booking.requested_at) }}</small>
                                </div>
                                <div class="d-flex align-items-center gap-2" v-if="booking.accepted_at">
                                    <div class="bg-info rounded-circle" style="width: 8px; height: 8px;"></div>
                                    <small class="text-muted">Accepted: {{ formatDate(booking.accepted_at) }}</small>
                                </div>
                                <div class="d-flex align-items-center gap-2" v-if="booking.confirmed_at">
                                    <div class="bg-primary rounded-circle" style="width: 8px; height: 8px;"></div>
                                    <small class="text-muted">Confirmed: {{ formatDate(booking.confirmed_at) }}</small>
                                </div>
                                <div class="d-flex align-items-center gap-2" v-if="booking.cancelled_at">
                                    <div class="bg-danger rounded-circle" style="width: 8px; height: 8px;"></div>
                                    <small class="text-muted">Cancelled: {{ formatDate(booking.cancelled_at) }}</small>
                                </div>
                                <div class="d-flex align-items-center gap-2" v-if="booking.completed_at">
                                    <div class="bg-success rounded-circle" style="width: 8px; height: 8px;"></div>
                                    <small class="text-muted">Completed: {{ formatDate(booking.completed_at) }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Actions -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-dark mb-3">Actions</h6>
                        <div class="d-grid gap-2">
                            <button
                                v-if="canCancel()"
                                class="btn btn-outline-danger"
                                @click="cancelBooking"
                            >
                                <i class="ri-close-circle-line me-1"></i> Cancel Booking
                            </button>
                            <button
                                v-if="canComplete()"
                                class="btn btn-success"
                                @click="completeBooking"
                            >
                                <i class="ri-check-double-line me-1"></i> Mark Complete
                            </button>
                            <Link :href="`/portal/trips/${booking.trip?.id}`" class="btn btn-outline-primary" v-if="booking.trip">
                                <i class="ri-eye-line me-1"></i> View Trip
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Quick Info -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-dark mb-3">Quick Info</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <small class="text-muted">Booking ID</small>
                            <small class="fw-medium">#{{ booking.id }}</small>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <small class="text-muted">Seats</small>
                            <small class="fw-medium">{{ booking.seat_count }}</small>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <small class="text-muted">Status</small>
                            <span class="badge" :class="bookingStatusBadge(booking.status)">{{ booking.status }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <small class="text-muted">Created</small>
                            <small>{{ formatDate(booking.created_at) }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>
