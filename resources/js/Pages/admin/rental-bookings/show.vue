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
                case 'pending': return 'bg-warning-subtle text-warning';
                case 'confirmed': return 'bg-info-subtle text-info';
                case 'active': return 'bg-success-subtle text-success';
                case 'completed': return 'bg-secondary-subtle text-secondary';
                case 'cancelled_by_guest': case 'cancelled_by_host': case 'rejected': return 'bg-danger-subtle text-danger';
                case 'expired': return 'bg-secondary-subtle text-secondary';
                case 'disputed': return 'bg-danger-subtle text-danger';
                default: return 'bg-secondary-subtle text-secondary';
            }
        },
        statusLabel(status) {
            const labels = {
                pending: 'Pending Confirmation', confirmed: 'Confirmed', active: 'Checked In',
                completed: 'Completed', cancelled_by_guest: 'Cancelled by Guest',
                cancelled_by_host: 'Cancelled by Host', rejected: 'Rejected by Host',
                expired: 'Expired', disputed: 'Disputed',
            };
            return labels[status] || status;
        },
        typeLabel(type) {
            const labels = { house: '🏠 House', car: '🚗 Car', commercial: '🏢 Commercial', room: '🛏️ Room' };
            return labels[type] || type;
        },
        formatCurrency(amount) {
            if (!amount) return '—';
            return '₹' + Number(amount).toLocaleString('en-IN');
        },
        formatDate(dateStr) {
            if (!dateStr) return '—';
            return new Date(dateStr).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
        },
        formatDateTime(dateStr) {
            if (!dateStr) return '—';
            return new Date(dateStr).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
        },
    },
};
</script>

<template>
    <Head :title="`Booking #${booking.id}`" />

    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <Link href="/admin/rentals-bookings" class="text-decoration-none text-muted">
                        <i class="ri-arrow-left-line"></i> Bookings
                    </Link>
                    <span class="text-muted">/</span>
                    <span class="fw-bold">Booking #{{ booking.id }}</span>
                </div>
                <span class="badge" :class="statusBadgeClass(booking.status)">{{ statusLabel(booking.status) }}</span>
                <span class="badge bg-light text-dark ms-1">{{ typeLabel(booking.listing?.rental_type) }}</span>
            </div>
        </div>

        <div class="row g-4">
            <!-- Main Info -->
            <div class="col-lg-8">
                <!-- Booking Details -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-bold">Booking Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small text-muted">Listing</label>
                                <div class="fw-semibold">{{ booking.listing?.title }}</div>
                                <small class="text-muted">{{ booking.listing?.address_line1 }}, {{ booking.listing?.city }}</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted">Booking Type</label>
                                <div class="text-capitalize">{{ booking.booking_type }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted">Check-in</label>
                                <div class="fw-semibold">{{ formatDate(booking.check_in) }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted">Check-out</label>
                                <div class="fw-semibold">{{ formatDate(booking.check_out) }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted">Duration</label>
                                <div class="fw-semibold">{{ booking.nights }} night(s)</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted">Guests</label>
                                <div>{{ booking.gests_count }}</div>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label small text-muted">Guest Message</label>
                                <div>{{ booking.guest_message || '—' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pricing Breakdown -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-bold">Pricing Breakdown</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ formatCurrency(booking.price_per_unit) }} × {{ booking.nights }} night(s)</span>
                            <span class="fw-semibold">{{ formatCurrency(booking.subtotal) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2" v-if="booking.cleaning_fee > 0">
                            <span>Cleaning Fee</span>
                            <span>{{ formatCurrency(booking.cleaning_fee) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2" v-if="booking.security_deposit > 0">
                            <span>Security Deposit</span>
                            <span>{{ formatCurrency(booking.security_deposit) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Service Fee (5%)</span>
                            <span>{{ formatCurrency(booking.service_fee) }}</span>
                        </div>
                        <hr />
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold fs-5">Total</span>
                            <span class="fw-bold fs-5 text-primary">{{ formatCurrency(booking.total_amount) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Status Timeline -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-bold">Status Timeline</h6>
                    </div>
                    <div class="card-body">
                        <div v-for="(history, idx) in booking.status_history" :key="history.id" class="d-flex gap-3 mb-3">
                            <div class="d-flex flex-column align-items-center">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 12px;">
                                    {{ idx + 1 }}
                                </div>
                                <div v-if="idx < booking.status_history.length - 1" class="flex-grow-1" style="width: 2px; background: #e0e0e0;"></div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between">
                                    <span class="fw-semibold text-capitalize">{{ history.to_status?.replace(/_/g, ' ') }}</span>
                                    <small class="text-muted">{{ formatDateTime(history.created_at) }}</small>
                                </div>
                                <div v-if="history.from_status" class="text-muted small">
                                    From: {{ history.from_status?.replace(/_/g, ' ') }}
                                </div>
                                <div v-if="history.actor_type" class="text-muted small">
                                    By: {{ history.actor_type }} <span v-if="history.changed_by_user">({{ history.changed_by_user?.name }})</span>
                                </div>
                                <div v-if="history.note" class="text-muted small mt-1 fst-italic">
                                    "{{ history.note }}"
                                </div>
                            </div>
                        </div>
                        <div v-if="!booking.status_history?.length" class="text-center text-muted py-3">No status history</div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Guest Info -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-bold">Guest</h6>
                    </div>
                    <div class="card-body">
                        <div class="fw-semibold">{{ booking.guest?.name }}</div>
                        <div class="text-muted small">{{ booking.guest?.email }}</div>
                        <div class="text-muted small" v-if="booking.guest?.phone">{{ booking.guest?.phone }}</div>
                    </div>
                </div>

                <!-- Host Info -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-bold">Host</h6>
                    </div>
                    <div class="card-body">
                        <div class="fw-semibold">{{ booking.owner?.name }}</div>
                        <div class="text-muted small">{{ booking.owner?.email }}</div>
                        <div class="text-muted small" v-if="booking.owner?.phone">{{ booking.owner?.phone }}</div>
                    </div>
                </div>

                <!-- Cancellation Info -->
                <div class="card border-0 shadow-sm mb-4" v-if="booking.cancellation_reason">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-bold text-danger">Cancellation</h6>
                    </div>
                    <div class="card-body">
                        <div class="text-muted small">Cancelled by: {{ booking.cancelled_by }}</div>
                        <div class="text-muted small">Date: {{ formatDateTime(booking.cancelled_at) }}</div>
                        <div class="mt-2 small">{{ booking.cancellation_reason }}</div>
                    </div>
                </div>

                <!-- Payment Info -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-bold">Payment</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Status</span>
                            <span class="badge bg-light text-dark">{{ booking.payment_status }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1" v-if="booking.payment_method">
                            <span class="text-muted">Method</span>
                            <span>{{ booking.payment_method }}</span>
                        </div>
                        <div class="d-flex justify-content-between" v-if="booking.payment_reference">
                            <span class="text-muted">Reference</span>
                            <small>{{ booking.payment_reference }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
