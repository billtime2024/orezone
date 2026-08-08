<script>
import { Link, Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/admin.vue';

export default {
    components: { Link, Head, AdminLayout },
    layout: AdminLayout,
    props: {
        listing: Object,
        stats: Object,
    },
    methods: {
        typeLabel(type) {
            const labels = { house: '🏠 House', car: '🚗 Car', commercial: '🏢 Commercial', room: '🛏️ Room' };
            return labels[type] || type;
        },
        statusBadgeClass(status) {
            switch (status) {
                case 'active': return 'bg-success-subtle text-success';
                case 'draft': return 'bg-secondary-subtle text-secondary';
                case 'paused': return 'bg-warning-subtle text-warning';
                case 'closed': return 'bg-danger-subtle text-danger';
                default: return 'bg-secondary-subtle text-secondary';
            }
        },
        bookingStatusClass(status) {
            switch (status) {
                case 'pending': return 'bg-warning-subtle text-warning';
                case 'confirmed': return 'bg-info-subtle text-info';
                case 'active': return 'bg-success-subtle text-success';
                case 'completed': return 'bg-secondary-subtle text-secondary';
                case 'cancelled_by_guest': case 'cancelled_by_host': case 'rejected': return 'bg-danger-subtle text-danger';
                default: return 'bg-secondary-subtle text-secondary';
            }
        },
        formatCurrency(amount) {
            if (!amount) return '—';
            return '₹' + Number(amount).toLocaleString('en-IN');
        },
        formatDate(dateStr) {
            if (!dateStr) return '—';
            return new Date(dateStr).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
        },
    },
};
</script>

<template>
    <Head :title="`Rental: ${listing.title}`" />

    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <Link href="/admin/rentals" class="text-decoration-none text-muted">
                        <i class="ri-arrow-left-line"></i> Listings
                    </Link>
                    <span class="text-muted">/</span>
                    <span class="fw-bold">{{ listing.title }}</span>
                </div>
                <span class="badge" :class="statusBadgeClass(listing.status)">{{ listing.status }}</span>
                <span class="badge bg-light text-dark ms-1">{{ typeLabel(listing.rental_type) }}</span>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-2">
                <div class="card border-0 shadow-sm text-center p-3">
                    <div class="fw-bold text-primary fs-4">{{ stats.total_bookings }}</div>
                    <small class="text-muted">Total Bookings</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-0 shadow-sm text-center p-3">
                    <div class="fw-bold text-success fs-4">{{ stats.active_bookings }}</div>
                    <small class="text-muted">Active</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-0 shadow-sm text-center p-3">
                    <div class="fw-bold text-info fs-4">{{ formatCurrency(stats.total_revenue) }}</div>
                    <small class="text-muted">Revenue</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-0 shadow-sm text-center p-3">
                    <div class="fw-bold text-warning fs-4">★ {{ stats.avg_rating || '0' }}</div>
                    <small class="text-muted">Rating</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-0 shadow-sm text-center p-3">
                    <div class="fw-bold fs-4">{{ stats.review_count }}</div>
                    <small class="text-muted">Reviews</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-0 shadow-sm text-center p-3">
                    <div class="fw-bold text-primary fs-4">{{ formatCurrency(listing.price_per_unit) }}</div>
                    <small class="text-muted">/{{ listing.price_unit }}</small>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Listing Details -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-bold">Listing Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small text-muted">Title</label>
                                <div class="fw-semibold">{{ listing.title }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted">Owner</label>
                                <div>{{ listing.owner?.name }} ({{ listing.owner?.email }})</div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small text-muted">Description</label>
                                <div>{{ listing.description || '—' }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted">Address</label>
                                <div>{{ listing.address_line1 }}</div>
                                <div v-if="listing.address_line2" class="text-muted small">{{ listing.address_line2 }}</div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted">City</label>
                                <div>{{ listing.city }}</div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small text-muted">State</label>
                                <div>{{ listing.state }}</div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted">Pincode</label>
                                <div>{{ listing.pincode }}</div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted">Security Deposit</label>
                                <div>{{ formatCurrency(listing.security_deposit) }}</div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted">Cleaning Fee</label>
                                <div>{{ formatCurrency(listing.cleaning_fee) }}</div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted">Instant Booking</label>
                                <div>{{ listing.instant_booking ? '✅ Yes' : '❌ No' }}</div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted">Photos</label>
                                <div>{{ listing.photos?.length || 0 }} photos</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Bookings -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between">
                        <h6 class="mb-0 fw-bold">Recent Bookings</h6>
                        <Link :href="`/admin/rentals-bookings?rental_type=${listing.rental_type}`" class="text-primary small text-decoration-none">View All</Link>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Guest</th>
                                    <th>Dates</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="booking in listing.bookings" :key="booking.id">
                                    <td>#{{ booking.id }}</td>
                                    <td>{{ booking.guest?.name }}</td>
                                    <td><small>{{ formatDate(booking.check_in) }} → {{ formatDate(booking.check_out) }}</small></td>
                                    <td class="fw-semibold">{{ formatCurrency(booking.total_amount) }}</td>
                                    <td><span class="badge" :class="bookingStatusClass(booking.status)">{{ booking.status }}</span></td>
                                    <td>
                                        <Link :href="`/admin/rentals-bookings/${booking.id}`" class="btn btn-sm btn-outline-primary">View</Link>
                                    </td>
                                </tr>
                                <tr v-if="!listing.bookings?.length">
                                    <td colspan="6" class="text-center text-muted py-3">No bookings yet</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Photos -->
                <div class="card border-0 shadow-sm mb-4" v-if="listing.photos?.length">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-bold">Photos</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div v-for="(photo, idx) in listing.photos.slice(0, 6)" :key="idx" class="col-4">
                                <img :src="photo" class="rounded w-100" style="height: 80px; object-fit: cover;" />
                            </div>
                        </div>
                        <div v-if="listing.photos.length > 6" class="text-center mt-2">
                            <small class="text-muted">+{{ listing.photos.length - 6 }} more photos</small>
                        </div>
                    </div>
                </div>

                <!-- Reviews -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-bold">Reviews</h6>
                    </div>
                    <div class="card-body">
                        <div v-for="review in listing.reviews" :key="review.id" class="mb-3 pb-3 border-bottom">
                            <div class="d-flex justify-content-between">
                                <div class="fw-semibold">{{ review.user?.name }}</div>
                                <span class="text-warning">★ {{ review.rating }}</span>
                            </div>
                            <p class="text-muted small mb-0 mt-1">{{ review.comment || 'No comment' }}</p>
                        </div>
                        <div v-if="!listing.reviews?.length" class="text-center text-muted py-3">No reviews yet</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
