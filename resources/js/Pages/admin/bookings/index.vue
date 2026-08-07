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
            statusFilter: this.$page.props.bookings?.query?.status || '',
        };
    },
    methods: {
        applyFilters() {
            this.$inertia.get('/admin/bookings', {
                status: this.statusFilter || undefined,
            }, { preserveState: true });
        },
        clearFilters() {
            this.statusFilter = '';
            this.$inertia.get('/admin/bookings');
        },
        statusBadgeClass(status) {
            switch (status) {
                case 'pending': return 'bg-warning-subtle text-warning';
                case 'confirmed': return 'bg-success-subtle text-success';
                case 'cancelled': return 'bg-danger-subtle text-danger';
                case 'completed': return 'bg-secondary-subtle text-secondary';
                default: return 'bg-secondary-subtle text-secondary';
            }
        },
        statusLabel(status) {
            switch (status) {
                case 'pending': return 'Pending';
                case 'confirmed': return 'Confirmed';
                case 'cancelled': return 'Cancelled';
                case 'completed': return 'Completed';
                default: return status;
            }
        },
        formatDateTime(dateStr) {
            if (!dateStr) return '—';
            return new Date(dateStr).toLocaleDateString('en-IN', {
                day: '2-digit', month: 'short', year: 'numeric',
                hour: '2-digit', minute: '2-digit',
            });
        },
        formatCurrency(amount) {
            if (!amount) return '—';
            return '₹' + Number(amount).toLocaleString('en-IN');
        },
    },
};
</script>

<template>
    <Head title="Manage Bookings" />

    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-1 fw-bold">Bookings Management</h4>
                <p class="text-muted mb-0">View and manage all trip bookings on the platform</p>
            </div>
            <span class="badge bg-primary fs-6">
                {{ bookings.total }} Total Bookings
            </span>
        </div>

        <!-- Filters -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small text-muted">Status</label>
                        <select v-model="statusFilter" class="form-select" @change="applyFilters">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                    <div class="col-md-8 d-flex gap-2">
                        <button class="btn btn-primary" @click="applyFilters">
                            <i class="ri-filter-3-line me-1"></i> Filter
                        </button>
                        <button class="btn btn-outline-secondary" @click="clearFilters">
                            <i class="ri-close-line me-1"></i> Clear
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bookings Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" style="width: 60px;">ID</th>
                                <th>Traveler</th>
                                <th>Host</th>
                                <th>Trip</th>
                                <th>Seats</th>
                                <th>Fee</th>
                                <th>Status</th>
                                <th class="text-end pe-3" style="width: 100px;">Booked At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="bookings.data.length === 0">
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="ri-bookmark-line fs-1 d-block mb-2"></i>
                                    No bookings found matching your criteria.
                                </td>
                            </tr>
                            <tr v-for="booking in bookings.data" :key="booking.id">
                                <td class="ps-3">
                                    <span class="text-muted">#{{ booking.id }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-sm rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center">
                                            <span class="text-info fw-semibold small">
                                                {{ booking.traveler?.name?.charAt(0)?.toUpperCase() || '?' }}
                                            </span>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ booking.traveler?.name || 'Unknown' }}</div>
                                            <small class="text-muted">{{ booking.traveler?.phone || '' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-semibold">{{ booking.trip?.host?.name || 'Unknown' }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <span>{{ booking.trip?.origin || '—' }}</span>
                                        <i class="ri-arrow-right-line text-muted small"></i>
                                        <span>{{ booking.trip?.destination || '—' }}</span>
                                    </div>
                                    <small class="text-muted" v-if="booking.trip?.departure_at">
                                        Departs: {{ formatDateTime(booking.trip.departure_at) }}
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        <i class="ri-user-line me-1"></i>{{ booking.seats_booked }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-success">{{ formatCurrency(booking.total_fee) }}</span>
                                </td>
                                <td>
                                    <span class="badge rounded-pill" :class="statusBadgeClass(booking.status)">
                                        {{ statusLabel(booking.status) }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <span class="small text-muted">{{ formatDateTime(booking.created_at) }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="d-flex align-items-center justify-content-between mt-4" v-if="bookings.last_page > 1">
            <div class="text-muted small">
                Showing {{ bookings.from }} to {{ bookings.to }} of {{ bookings.total }} bookings
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item" :class="{ disabled: !bookings.prev_page_url }">
                        <Link
                            :href="bookings.prev_page_url || '#'"
                            class="page-link"
                            preserve-state
                        >
                            <i class="ri-arrow-left-s-line"></i>
                        </Link>
                    </li>
                    <li
                        v-for="page in bookings.last_page"
                        :key="page"
                        class="page-item"
                        :class="{ active: page === bookings.current_page }"
                    >
                        <Link
                            :href="'/admin/bookings?page=' + page + (statusFilter ? '&status=' + statusFilter : '')"
                            class="page-link"
                            preserve-state
                        >
                            {{ page }}
                        </Link>
                    </li>
                    <li class="page-item" :class="{ disabled: !bookings.next_page_url }">
                        <Link
                            :href="bookings.next_page_url || '#'"
                            class="page-link"
                            preserve-state
                        >
                            <i class="ri-arrow-right-s-line"></i>
                        </Link>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</template>

<style scoped>
.avatar-sm {
    width: 36px;
    height: 36px;
    font-size: 14px;
}
</style>
