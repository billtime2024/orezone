<script>
import { Link, Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/admin.vue';

export default {
    components: { Link, Head, AdminLayout },
    layout: AdminLayout,
    props: {
        alerts: Object,
    },
    data() {
        return {
            statusFilter: this.$page.props.alerts?.query?.status || '',
        };
    },
    methods: {
        applyFilters() {
            this.$inertia.get('/admin/sos', {
                status: this.statusFilter || undefined,
            }, { preserveState: true });
        },
        clearFilters() {
            this.statusFilter = '';
            this.$inertia.get('/admin/sos');
        },
        statusBadgeClass(status) {
            switch (status) {
                case 'active': return 'bg-danger-subtle text-danger fw-bold';
                case 'reviewed': return 'bg-success-subtle text-success';
                case 'resolved': return 'bg-secondary-subtle text-secondary';
                default: return 'bg-warning-subtle text-warning';
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
    <Head title="SOS Alerts" />

    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-1 fw-bold">
                    <i class="ri-alarm-warning-line text-danger me-2"></i>
                    SOS Alerts
                </h4>
                <p class="text-muted mb-0">Emergency alerts triggered by users</p>
            </div>
            <span class="badge bg-danger fs-6">
                {{ alerts.total }} Total Alerts
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
                            <option value="active">Active</option>
                            <option value="reviewed">Reviewed</option>
                            <option value="resolved">Resolved</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button class="btn btn-primary" @click="applyFilters">
                            <i class="ri-search-line me-1"></i> Filter
                        </button>
                        <button class="btn btn-outline-secondary" @click="clearFilters">
                            <i class="ri-close-line me-1"></i> Clear
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- SOS Alerts Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" style="width: 60px;">ID</th>
                                <th>User</th>
                                <th>Trip</th>
                                <th>Location</th>
                                <th>Message</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th class="text-end pe-3" style="width: 100px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="alerts.data.length === 0">
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="ri-alarm-warning-line fs-1 d-block mb-2"></i>
                                    No SOS alerts found matching your criteria.
                                </td>
                            </tr>
                            <tr v-for="alert in alerts.data" :key="alert.id">
                                <td class="ps-3">
                                    <span class="text-muted">#{{ alert.id }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-sm rounded-circle bg-danger bg-opacity-10 d-flex align-items-center justify-content-center">
                                            <span class="text-danger fw-semibold small">
                                                {{ alert.user?.name ? alert.user.name.charAt(0).toUpperCase() : '?' }}
                                            </span>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ alert.user?.name || 'Unknown' }}</div>
                                            <small class="text-muted">{{ alert.user?.phone || '—' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-muted small">Trip #{{ alert.trip_id }}</span>
                                </td>
                                <td>
                                    <span class="text-muted small">
                                        <i class="ri-map-pin-line me-1"></i>
                                        {{ alert.latitude ? alert.latitude + ', ' + alert.longitude : '—' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted small text-truncate d-inline-block" style="max-width: 200px;" :title="alert.message">
                                        {{ alert.message || '—' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge rounded-pill" :class="statusBadgeClass(alert.status)">
                                        <i v-if="alert.status === 'active'" class="ri-alarm-warning-line me-1"></i>
                                        {{ alert.status || 'unknown' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted small">{{ formatDate(alert.created_at) }}</span>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                            <i class="ri-more-2-fill"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="#">
                                                    <i class="ri-eye-line me-2"></i> View Details
                                                </a>
                                            </li>
                                            <li v-if="alert.status === 'active'">
                                                <a class="dropdown-item text-success" href="#">
                                                    <i class="ri-check-line me-2"></i> Mark Reviewed
                                                </a>
                                            </li>
                                            <li v-if="alert.status !== 'resolved'">
                                                <a class="dropdown-item text-secondary" href="#">
                                                    <i class="ri-check-double-line me-2"></i> Mark Resolved
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="d-flex align-items-center justify-content-between mt-4" v-if="alerts.last_page > 1">
            <div class="text-muted small">
                Showing {{ alerts.from }} to {{ alerts.to }} of {{ alerts.total }} alerts
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item" :class="{ disabled: !alerts.prev_page_url }">
                        <Link
                            :href="alerts.prev_page_url || '#'"
                            class="page-link"
                            preserve-state
                        >
                            <i class="ri-arrow-left-s-line"></i>
                        </Link>
                    </li>
                    <li
                        v-for="page in alerts.last_page"
                        :key="page"
                        class="page-item"
                        :class="{ active: page === alerts.current_page }"
                    >
                        <Link
                            :href="'/admin/sos?page=' + page + (statusFilter ? '&status=' + statusFilter : '')"
                            class="page-link"
                            preserve-state
                        >
                            {{ page }}
                        </Link>
                    </li>
                    <li class="page-item" :class="{ disabled: !alerts.next_page_url }">
                        <Link
                            :href="alerts.next_page_url || '#'"
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
