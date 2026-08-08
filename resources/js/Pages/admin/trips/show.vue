<script>
import { Link, Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/admin.vue';

export default {
    components: { Link, Head, AdminLayout },
    layout: AdminLayout,
    props: {
        trip: Object,
    },
    methods: {
        statusBadgeClass(status) {
            switch (status) {
                case 'draft': return 'bg-secondary-subtle text-secondary';
                case 'published': return 'bg-success-subtle text-success';
                case 'in_progress': return 'bg-info-subtle text-info';
                case 'completed': return 'bg-primary-subtle text-primary';
                case 'cancelled': return 'bg-danger-subtle text-danger';
                default: return 'bg-secondary-subtle text-secondary';
            }
        },
        bookingStatusBadge(status) {
            switch (status) {
                case 'confirmed': return 'bg-success-subtle text-success';
                case 'pending': return 'bg-warning-subtle text-warning';
                case 'cancelled': return 'bg-danger-subtle text-danger';
                case 'completed': return 'bg-primary-subtle text-primary';
                default: return 'bg-secondary-subtle text-secondary';
            }
        },
        formatDate(dateStr) {
            if (!dateStr) return '—';
            return new Date(dateStr).toLocaleDateString('en-IN', {
                day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit',
            });
        },
        formatCurrency(amount) {
            return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(amount || 0);
        },
    },
};
</script>

<template>
    <Head :title="`Trip #${trip.id}`" />

    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Trip #{{ trip.id }}</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><Link href="/admin/trips">Trips</Link></li>
                        <li class="breadcrumb-item active">Trip #{{ trip.id }}</li>
                    </ol>
                </nav>
            </div>
            <span class="badge fs-6" :class="statusBadgeClass(trip.status)">{{ trip.status }}</span>
        </div>

        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-8">
                <!-- Trip Details -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Trip Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label text-muted small">Origin</label>
                                <div class="fw-semibold"><i class="ri-map-pin-line text-success me-1"></i> {{ trip.origin }}</div>
                            </div>
                            <div class="col-md-2 d-flex align-items-end justify-content-center">
                                <i class="ri-arrow-right-line fs-4 text-muted"></i>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label text-muted small">Destination</label>
                                <div class="fw-semibold"><i class="ri-map-pin-2-line text-danger me-1"></i> {{ trip.destination }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small">Departure</label>
                                <div>{{ formatDate(trip.departure_time) }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small">Seats Available</label>
                                <div>{{ trip.seats_available }} / {{ trip.total_seats }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small">Booking Mode</label>
                                <div class="text-capitalize">{{ trip.booking_mode || '—' }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small">Price Per Seat</label>
                                <div class="fw-semibold">{{ formatCurrency(trip.price_per_seat) }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small">Total Bookings</label>
                                <div>{{ trip.bookings_count || 0 }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small">Created</label>
                                <div>{{ formatDate(trip.created_at) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stops -->
                <div v-if="trip.stops?.length" class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Stops ({{ trip.stops.length }})</h5>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Location</th>
                                    <th>Arrival</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(stop, index) in trip.stops" :key="stop.id">
                                    <td>{{ index + 1 }}</td>
                                    <td>{{ stop.location }}</td>
                                    <td>{{ formatDate(stop.arrival_time) }}</td>
                                    <td>{{ stop.notes || '—' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Bookings Table -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Bookings ({{ trip.bookings?.length || 0 }})</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Traveler</th>
                                        <th>Seats</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Booked</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="booking in trip.bookings" :key="booking.id">
                                        <td>
                                            <Link :href="`/admin/bookings/${booking.id}`" class="text-decoration-none">
                                                {{ booking.traveler?.name || 'N/A' }}
                                            </Link>
                                        </td>
                                        <td>{{ booking.seats }}</td>
                                        <td>{{ formatCurrency(booking.total_amount) }}</td>
                                        <td>
                                            <span class="badge" :class="bookingStatusBadge(booking.status)">
                                                {{ booking.status }}
                                            </span>
                                        </td>
                                        <td>{{ formatDate(booking.created_at) }}</td>
                                    </tr>
                                    <tr v-if="!trip.bookings?.length">
                                        <td colspan="5" class="text-center text-muted py-4">No bookings</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div v-if="trip.notes" class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Notes</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ trip.notes }}</p>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-lg-4">
                <!-- Host Info -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Host Info</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="avatar-lg mx-auto mb-2 bg-success-subtle rounded-circle d-flex align-items-center justify-content-center">
                                <i class="ri-user-line text-success fs-2"></i>
                            </div>
                            <h6 class="mb-0">{{ trip.host?.name }}</h6>
                        </div>
                        <div class="border-top pt-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Phone</span>
                                <span>{{ trip.host?.phone || '—' }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Email</span>
                                <span>{{ trip.host?.email || '—' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vehicle Info -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Vehicle Info</h5>
                    </div>
                    <div class="card-body">
                        <div class="border-top pt-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Make/Model</span>
                                <span>{{ trip.vehicle?.make }} {{ trip.vehicle?.model }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Category</span>
                                <span>{{ trip.vehicle?.category?.name || '—' }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Color</span>
                                <span>{{ trip.vehicle?.color || '—' }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Registration</span>
                                <span>{{ trip.vehicle?.registration_number || '—' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status History -->
                <div v-if="trip.status_history?.length" class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Status History</h5>
                    </div>
                    <div class="card-body">
                        <div v-for="(entry, index) in trip.status_history" :key="index"
                            class="mb-3 pb-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <span class="badge" :class="statusBadgeClass(entry.status)">
                                        {{ entry.status }}
                                    </span>
                                    <div v-if="entry.changer" class="text-muted small mt-1">
                                        by {{ entry.changer.name }}
                                    </div>
                                </div>
                                <small class="text-muted">{{ formatDate(entry.created_at) }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
