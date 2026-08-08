<script>
import { Link, Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/admin.vue';

export default {
    components: { Link, Head, AdminLayout },
    layout: AdminLayout,
    props: {
        bookings: Object,
    },
    data() {
        return {
            searchFilter: this.$page.props.bookings?.query?.search || '',
            statusFilter: this.$page.props.bookings?.query?.status || '',
            typeFilter: this.$page.props.bookings?.query?.rental_type || '',
        };
    },
    methods: {
        applyFilters() {
            this.$inertia.get('/admin/rentals-bookings', {
                search: this.searchFilter || undefined,
                status: this.statusFilter || undefined,
                rental_type: this.typeFilter || undefined,
            }, { preserveState: true });
        },
        clearFilters() {
            this.searchFilter = '';
            this.statusFilter = '';
            this.typeFilter = '';
            this.$inertia.get('/admin/rentals-bookings');
        },
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
                pending: 'Pending', confirmed: 'Confirmed', active: 'Active', completed: 'Completed',
                cancelled_by_guest: 'Cancelled (Guest)', cancelled_by_host: 'Cancelled (Host)',
                rejected: 'Rejected', expired: 'Expired', disputed: 'Disputed',
            };
            return labels[status] || status;
        },
        typeLabel(type) {
            const labels = { house: '🏠', car: '🚗', commercial: '🏢', room: '🛏️' };
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
    },
};
</script>

<template>
    <Head title="Rental Bookings" />

    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-1 fw-bold">Rental Bookings</h4>
                <p class="text-muted mb-0">View and manage all rental bookings</p>
            </div>
            <span class="badge bg-primary fs-6">
                {{ bookings.total }} Total Bookings
            </span>
        </div>

        <!-- Filters -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Search</label>
                        <input
                            v-model="searchFilter"
                            type="text"
                            class="form-control"
                            placeholder="Booking ID, guest name, listing..."
                            @keyup.enter="applyFilters"
                        />
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Type</label>
                        <select v-model="typeFilter" class="form-select">
                            <option value="">All Types</option>
                            <option value="house">🏠 House</option>
                            <option value="car">🚗 Car</option>
                            <option value="commercial">🏢 Commercial</option>
                            <option value="room">🛏️ Room</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Status</label>
                        <select v-model="statusFilter" class="form-select">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="active">Active</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled_by_guest">Cancelled (Guest)</option>
                            <option value="cancelled_by_host">Cancelled (Host)</option>
                            <option value="rejected">Rejected</option>
                            <option value="expired">Expired</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary me-2" @click="applyFilters">
                            <i class="ri-search-line me-1"></i> Filter
                        </button>
                        <button class="btn btn-outline-secondary" @click="clearFilters">Clear</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bookings Table -->
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="fw-semibold">ID</th>
                            <th class="fw-semibold">Listing</th>
                            <th class="fw-semibold">Guest</th>
                            <th class="fw-semibold">Host</th>
                            <th class="fw-semibold">Dates</th>
                            <th class="fw-semibold">Amount</th>
                            <th class="fw-semibold">Status</th>
                            <th class="fw-semibold">Booked</th>
                            <th class="fw-semibold text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="booking in bookings.data" :key="booking.id">
                            <td class="fw-semibold">#{{ booking.id }}</td>
                            <td>
                                <span class="me-1">{{ typeLabel(booking.listing?.rental_type) }}</span>
                                {{ booking.listing?.title }}
                            </td>
                            <td>
                                <div>{{ booking.guest?.name }}</div>
                                <small class="text-muted">{{ booking.guest?.email }}</small>
                            </td>
                            <td>
                                <div>{{ booking.owner?.name }}</div>
                                <small class="text-muted">{{ booking.owner?.email }}</small>
                            </td>
                            <td>
                                <div><small>{{ formatDate(booking.check_in) }}</small></div>
                                <div><small>→ {{ formatDate(booking.check_out) }}</small></div>
                                <small class="text-muted">{{ booking.nights }} night(s)</small>
                            </td>
                            <td class="fw-semibold">{{ formatCurrency(booking.total_amount) }}</td>
                            <td><span class="badge" :class="statusBadgeClass(booking.status)">{{ statusLabel(booking.status) }}</span></td>
                            <td><small>{{ formatDate(booking.created_at) }}</small></td>
                            <td class="text-end">
                                <Link :href="`/admin/rentals-bookings/${booking.id}`" class="btn btn-sm btn-outline-primary">
                                    <i class="ri-eye-line"></i>
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="bookings.data.length === 0">
                            <td colspan="9" class="text-center text-muted py-4">No bookings found</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center" v-if="bookings.last_page > 1">
                <small class="text-muted">
                    Showing {{ bookings.from }} to {{ bookings.to }} of {{ bookings.total }} bookings
                </small>
                <div class="d-flex gap-1">
                    <Link
                        v-for="page in bookings.last_page"
                        :key="page"
                        :href="`/admin/rentals-bookings?page=${page}`"
                        class="btn btn-sm"
                        :class="page === bookings.current_page ? 'btn-primary' : 'btn-outline-secondary'"
                        preserve-state
                    >
                        {{ page }}
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
