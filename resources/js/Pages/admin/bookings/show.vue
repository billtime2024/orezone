<script>
import { Link, Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/admin.vue';

export default {
    components: { Link, Head, AdminLayout },
    layout: AdminLayout,
    props: {
        booking: Object,
    },
    methods: {
        statusBadgeClass(status) {
            switch (status) {
                case 'confirmed': return 'bg-success-subtle text-success';
                case 'pending': return 'bg-warning-subtle text-warning';
                case 'cancelled': return 'bg-danger-subtle text-danger';
                case 'completed': return 'bg-primary-subtle text-primary';
                case 'no_show': return 'bg-secondary-subtle text-secondary';
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
    <Head :title="`Booking #${booking.id}`" />

    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Booking #{{ booking.id }}</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><Link href="/admin/bookings">Bookings</Link></li>
                        <li class="breadcrumb-item active">Booking #{{ booking.id }}</li>
                    </ol>
                </nav>
            </div>
            <span class="badge fs-6" :class="statusBadgeClass(booking.status)">{{ booking.status }}</span>
        </div>

        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-8">
                <!-- Booking Details -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Booking Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Booking ID</label>
                                <div class="fw-semibold">#{{ booking.id }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Status</label>
                                <div>
                                    <span class="badge" :class="statusBadgeClass(booking.status)">
                                        {{ booking.status }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small">Seats Booked</label>
                                <div class="fw-semibold">{{ booking.seats }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small">Pickup Stop</label>
                                <div>{{ booking.pickup_stop || 'Origin' }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small">Drop Stop</label>
                                <div>{{ booking.drop_stop || 'Destination' }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Booked At</label>
                                <div>{{ formatDate(booking.created_at) }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Updated At</label>
                                <div>{{ formatDate(booking.updated_at) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fee Breakdown -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Fee Breakdown</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Base Amount</label>
                                <div class="fw-semibold">{{ formatCurrency(booking.base_amount) }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Platform Fee</label>
                                <div class="fw-semibold">{{ formatCurrency(booking.platform_fee) }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Tax</label>
                                <div>{{ formatCurrency(booking.tax_amount) }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Total Amount</label>
                                <div class="fw-bold text-primary fs-5">{{ formatCurrency(booking.total_amount) }}</div>
                            </div>
                        </div>
                        <!-- Fee Snapshot -->
                        <div v-if="booking.fee_snapshot" class="mt-3 pt-3 border-top">
                            <label class="form-label text-muted small">Fee Snapshot</label>
                            <pre class="bg-light p-2 rounded mb-0 small">{{ JSON.stringify(booking.fee_snapshot, null, 2) }}</pre>
                        </div>
                    </div>
                </div>

                <!-- Status Timeline -->
                <div v-if="booking.status_history?.length" class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Status Timeline</h5>
                    </div>
                    <div class="card-body">
                        <div v-for="(entry, index) in booking.status_history" :key="index"
                            class="mb-3 pb-3" :class="{ 'border-bottom': index < booking.status_history.length - 1 }">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <span class="badge" :class="statusBadgeClass(entry.status)">
                                        {{ entry.status }}
                                    </span>
                                    <div v-if="entry.reason" class="text-muted small mt-1">{{ entry.reason }}</div>
                                    <div v-if="entry.changer" class="text-muted small">
                                        by {{ entry.changer.name }}
                                    </div>
                                </div>
                                <small class="text-muted">{{ formatDate(entry.created_at) }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-lg-4">
                <!-- Trip Info -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Trip Info</h5>
                        <Link v-if="booking.trip" :href="`/admin/trips/${booking.trip.id}`" class="badge bg-primary-subtle text-primary text-decoration-none">
                            View Trip
                        </Link>
                    </div>
                    <div class="card-body">
                        <div v-if="booking.trip">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Route</span>
                                <span>{{ booking.trip.origin }} → {{ booking.trip.destination }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Departure</span>
                                <span>{{ formatDate(booking.trip.departure_time) }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Host</span>
                                <span>{{ booking.trip.host?.name || booking.host?.name || '—' }}</span>
                            </div>
                        </div>
                        <div v-else class="text-muted text-center py-3">Trip info not available</div>
                    </div>
                </div>

                <!-- Traveler Info -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Traveler Info</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="avatar-lg mx-auto mb-2 bg-info-subtle rounded-circle d-flex align-items-center justify-content-center">
                                <i class="ri-user-line text-info fs-2"></i>
                            </div>
                            <h6 class="mb-0">{{ booking.traveler?.name }}</h6>
                            <span class="text-muted small">{{ booking.traveler?.email }}</span>
                        </div>
                        <div class="border-top pt-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Phone</span>
                                <span>{{ booking.traveler?.phone || '—' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Host Info -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Host Info</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="avatar-lg mx-auto mb-2 bg-success-subtle rounded-circle d-flex align-items-center justify-content-center">
                                <i class="ri-user-line text-success fs-2"></i>
                            </div>
                            <h6 class="mb-0">{{ booking.host?.name || booking.trip?.host?.name }}</h6>
                        </div>
                        <div class="border-top pt-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Phone</span>
                                <span>{{ booking.host?.phone || booking.trip?.host?.phone || '—' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
