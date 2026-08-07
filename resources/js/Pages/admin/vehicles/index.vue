<script>
import { Link, Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/admin.vue';

export default {
    components: { Link, Head, AdminLayout },
    layout: AdminLayout,
    props: {
        vehicles: Object,
    },
    data() {
        return {
            categoryFilter: this.$page.props.vehicles?.query?.category || '',
            statusFilter: this.$page.props.vehicles?.query?.status || '',
        };
    },
    methods: {
        applyFilters() {
            this.$inertia.get('/admin/vehicles', {
                category: this.categoryFilter || undefined,
                status: this.statusFilter || undefined,
            }, { preserveState: true });
        },
        clearFilters() {
            this.categoryFilter = '';
            this.statusFilter = '';
            this.$inertia.get('/admin/vehicles');
        },
        statusBadgeClass(status) {
            switch (status) {
                case 'pending': return 'bg-warning-subtle text-warning';
                case 'verified': return 'bg-success-subtle text-success';
                case 'rejected': return 'bg-danger-subtle text-danger';
                default: return 'bg-secondary-subtle text-secondary';
            }
        },
        statusLabel(status) {
            switch (status) {
                case 'pending': return 'Pending';
                case 'verified': return 'Verified';
                case 'rejected': return 'Rejected';
                default: return status;
            }
        },
        formatDate(dateStr) {
            if (!dateStr) return '—';
            return new Date(dateStr).toLocaleDateString('en-IN', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
            });
        },
    },
};
</script>

<template>
    <Head title="Manage Vehicles" />

    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-1 fw-bold">Vehicles Management</h4>
                <p class="text-muted mb-0">Manage all registered vehicles and their verification status</p>
            </div>
            <span class="badge bg-primary fs-6">
                {{ vehicles.total }} Total Vehicles
            </span>
        </div>

        <!-- Filters -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Category</label>
                        <select v-model="categoryFilter" class="form-select" @change="applyFilters">
                            <option value="">All Categories</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Status</label>
                        <select v-model="statusFilter" class="form-select" @change="applyFilters">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="verified">Verified</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-6 d-flex gap-2">
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

        <!-- Vehicles Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" style="width: 60px;">ID</th>
                                <th>Owner</th>
                                <th>Category</th>
                                <th>Reg Number</th>
                                <th>Brand / Model</th>
                                <th>Seats</th>
                                <th>Status</th>
                                <th class="text-end pe-3" style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="vehicles.data.length === 0">
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="ri-roadster-line fs-1 d-block mb-2"></i>
                                    No vehicles found matching your criteria.
                                </td>
                            </tr>
                            <tr v-for="vehicle in vehicles.data" :key="vehicle.id">
                                <td class="ps-3">
                                    <span class="text-muted">#{{ vehicle.id }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-sm rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center">
                                            <span class="text-success fw-semibold small">
                                                {{ vehicle.user?.name?.charAt(0)?.toUpperCase() || '?' }}
                                            </span>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ vehicle.user?.name || 'Unknown' }}</div>
                                            <small class="text-muted">{{ vehicle.user?.phone || '' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        {{ vehicle.category?.name || 'Uncategorized' }}
                                    </span>
                                </td>
                                <td>
                                    <code class="small">{{ vehicle.registration_number }}</code>
                                </td>
                                <td>
                                    <span class="fw-semibold">{{ vehicle.brand }}</span>
                                    <span class="text-muted ms-1">{{ vehicle.model }}</span>
                                    <small class="d-block text-muted" v-if="vehicle.year">{{ vehicle.year }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        <i class="ri-user-line me-1"></i>{{ vehicle.total_seats }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge rounded-pill" :class="statusBadgeClass(vehicle.status)">
                                        {{ statusLabel(vehicle.status) }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="d-flex gap-1 justify-content-end">
                                        <button
                                            v-if="vehicle.status === 'pending'"
                                            class="btn btn-sm btn-success-subtle text-success"
                                            title="Verify"
                                        >
                                            <i class="ri-check-line"></i>
                                        </button>
                                        <button
                                            v-if="vehicle.status === 'pending'"
                                            class="btn btn-sm btn-danger-subtle text-danger"
                                            title="Reject"
                                        >
                                            <i class="ri-close-line"></i>
                                        </button>
                                        <button class="btn btn-sm btn-light" title="View Details">
                                            <i class="ri-eye-line"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="d-flex align-items-center justify-content-between mt-4" v-if="vehicles.last_page > 1">
            <div class="text-muted small">
                Showing {{ vehicles.from }} to {{ vehicles.to }} of {{ vehicles.total }} vehicles
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item" :class="{ disabled: !vehicles.prev_page_url }">
                        <Link
                            :href="vehicles.prev_page_url || '#'"
                            class="page-link"
                            preserve-state
                        >
                            <i class="ri-arrow-left-s-line"></i>
                        </Link>
                    </li>
                    <li
                        v-for="page in vehicles.last_page"
                        :key="page"
                        class="page-item"
                        :class="{ active: page === vehicles.current_page }"
                    >
                        <Link
                            :href="'/admin/vehicles?page=' + page + (categoryFilter ? '&category=' + categoryFilter : '') + (statusFilter ? '&status=' + statusFilter : '')"
                            class="page-link"
                            preserve-state
                        >
                            {{ page }}
                        </Link>
                    </li>
                    <li class="page-item" :class="{ disabled: !vehicles.next_page_url }">
                        <Link
                            :href="vehicles.next_page_url || '#'"
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
