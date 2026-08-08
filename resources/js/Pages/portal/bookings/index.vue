<script>
import { Link, Head } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/portal-layout.vue';

export default {
    components: { Link, Head, PortalLayout },
    props: {
        user: Object,
        bookings: Array,
    },
    methods: {
        statusBadge(status) {
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
    <Head title="My Bookings - orezone" />

    <PortalLayout>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">My Bookings</h4>
                <p class="text-muted mb-0">Check your ride bookings and reservation status</p>
            </div>
            <Link href="/portal/trips/search" class="btn btn-primary">
                <i class="ri-search-line me-1"></i> Find Trips
            </Link>
        </div>

        <!-- Empty State -->
        <div v-if="!bookings || bookings.length === 0" class="text-center py-5">
            <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                <span class="display-5">📋</span>
            </div>
            <h5 class="fw-bold text-dark">No Bookings Yet</h5>
            <p class="text-muted">Search for trips and book your first ride</p>
            <Link href="/portal/trips/search" class="btn btn-primary">
                <i class="ri-search-line me-1"></i> Search Trips
            </Link>
        </div>

        <!-- Booking Cards -->
        <div v-else class="row g-4">
            <div class="col-md-6 col-lg-4" v-for="booking in bookings" :key="booking.id">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="fw-bold text-dark mb-1" v-if="booking.trip">
                                    {{ booking.trip.origin_name }}
                                    <i class="ri-arrow-right-line mx-1 text-muted" style="font-size: 0.8rem;"></i>
                                    {{ booking.trip.destination_name }}
                                </h5>
                                <h5 class="fw-bold text-dark mb-1" v-else>Trip #{{ booking.trip?.id || 'N/A' }}</h5>
                            </div>
                            <span class="badge" :class="statusBadge(booking.status)">
                                {{ booking.status }}
                            </span>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="ri-calendar-line text-muted small"></i>
                                <small v-if="booking.trip">{{ formatDate(booking.trip.departure_at) }}</small>
                                <small v-else>—</small>
                            </div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="ri-seat-line text-muted small"></i>
                                <small>{{ booking.seat_count }} seat(s)</small>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="ri-time-line text-muted small"></i>
                                <small>Booked {{ formatDate(booking.created_at) }}</small>
                            </div>
                        </div>

                        <!-- Trip Status -->
                        <div v-if="booking.trip" class="mb-2">
                            <small class="text-muted">Trip Status: </small>
                            <span class="badge bg-light text-dark">{{ booking.trip.status }}</span>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-top">
                        <Link :href="`/portal/bookings/${booking.id}`" class="btn btn-primary w-100">
                            <i class="ri-eye-line me-1"></i> View Details
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>
