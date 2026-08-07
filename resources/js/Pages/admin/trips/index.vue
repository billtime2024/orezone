<script>
import { Link, Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/admin.vue';

export default {
    components: { Link, Head, AdminLayout },
    layout: AdminLayout,
    props: {
        trips: Object,
    },
    data() {
        return {
            searchFilter: this.$page.props.trips?.query?.search || '',
            statusFilter: this.$page.props.trips?.query?.status || '',
        };
    },
    methods: {
        applyFilters() {
            this.$inertia.get('/admin/trips', {
                search: this.searchFilter || undefined,
                status: this.statusFilter || undefined,
            }, { preserveState: true });
        },
        clearFilters() {
            this.searchFilter = '';
            this.statusFilter = '';
            this.$inertia.get('/admin/trips');
        },
        statusBadgeClass(status) {
            switch (status) {
                case 'pending': return 'bg-warning-subtle text-warning';
                case 'active': return 'bg-success-subtle text-success';
                case 'in_progress': return 'bg-info-subtle text-info';
                case 'completed': return 'bg-secondary-subtle text-secondary';
                case 'cancelled': return 'bg-danger-subtle text-danger';
                default: return 'bg-secondary-subtle text-secondary';
            }
        },
        statusLabel(status) {
            switch (status) {
                case 'pending': return 'Pending';
                case 'active': return 'Active';
                case 'in_progress': return 'In Progress';
                case 'completed': return 'Completed';
                case 'cancelled': return 'Cancelled';
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
    <Head title="Manage Trips" />

    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-1 fw-bold">Trips Management</h4>
                <p class="text-muted mb-0">View and manage all trips on the platform</p>
            </div>
            <span class="badge bg-primary fs-6">
                {{ trips.total }} Total Trips
            </span>
        </div>

        <!-- Filters -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small text-muted">Search (Origin / Destination)</label>
                        <input
                            v-model="searchFilter"
                            type="text"
                            class="form-control"
                            placeholder="Search by city..."
                            @keyup.enter="applyFilters"
                        />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Status</label>
                        <select v-model="statusFilter" class="form-select" @change="applyFilters">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="active">Active</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-5 d-flex gap-2">
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

        <!-- Trips Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" style="width: 60px;">ID</th>
                                <th>Host</th>
                                <th>Route</th>
                                <th>Departure</th>
                                <th>Seats</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th class="text-end pe-3" style="width: 80px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="trips.data.length === 0">
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="ri-route-line fs-1 d-block mb-2"></i>
                                    No trips found matching your criteria.
                                </td>
                            </tr>
                            <tr v-for="trip in trips.data" :key="trip.id">
                                <td class="ps-3">
                                    <span class="text-muted">#{{ trip.id }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-sm rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center">
                                            <span class="text-primary fw-semibold small">
                                                {{ trip.host?.name?.charAt(0)?.toUpperCase() || '?' }}
                                            </span>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ trip.host?.name || 'Unknown' }}</div>
                                            <small class="text-muted">{{ trip.host?.phone || '' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="fw-semibold">{{ trip.origin }}</span>
                                        <i class="ri-arrow-right-line text-muted"></i>
                                        <span class="fw-semibold">{{ trip.destination }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="small">{{ formatDateTime(trip.departure_at) }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        <i class="ri-user-line me-1"></i>{{ trip.available_seats }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-success">{{ formatCurrency(trip.price_per_seat) }}</span>
                                    <small class="d-block text-muted">/seat</small>
                                </td>
                                <td>
                                    <span class="badge rounded-pill" :class="statusBadgeClass(trip.status)">
                                        {{ statusLabel(trip.status) }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <button class="btn btn-sm btn-light" title="View Details">
                                        <i class="ri-eye-line"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="d-flex align-items-center justify-content-between mt-4" v-if="trips.last_page > 1">
            <div class="text-muted small">
                Showing {{ trips.from }} to {{ trips.to }} of {{ trips.total }} trips
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item" :class="{ disabled: !trips.prev_page_url }">
                        <Link
                            :href="trips.prev_page_url || '#'"
                            class="page-link"
                            preserve-state
                        >
                            <i class="ri-arrow-left-s-line"></i>
                        </Link>
                    </li>
                    <li
                        v-for="page in trips.last_page"
                        :key="page"
                        class="page-item"
                        :class="{ active: page === trips.current_page }"
                    >
                        <Link
                            :href="'/admin/trips?page=' + page + (searchFilter ? '&search=' + searchFilter : '') + (statusFilter ? '&status=' + statusFilter : '')"
                            class="page-link"
                            preserve-state
                        >
                            {{ page }}
                        </Link>
                    </li>
                    <li class="page-item" :class="{ disabled: !trips.next_page_url }">
                        <Link
                            :href="trips.next_page_url || '#'"
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
