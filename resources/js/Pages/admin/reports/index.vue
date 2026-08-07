<script>
import { Link, Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/admin.vue';

export default {
    components: { Link, Head, AdminLayout },
    layout: AdminLayout,
    props: {
        reports: Object,
    },
    data() {
        return {
            statusFilter: this.$page.props.reports?.query?.status || '',
        };
    },
    methods: {
        applyFilters() {
            this.$inertia.get('/admin/reports', {
                status: this.statusFilter || undefined,
            }, { preserveState: true });
        },
        clearFilters() {
            this.statusFilter = '';
            this.$inertia.get('/admin/reports');
        },
        statusBadgeClass(status) {
            switch (status) {
                case 'pending': return 'bg-warning-subtle text-warning';
                case 'reviewed': return 'bg-success-subtle text-success';
                case 'dismissed': return 'bg-secondary-subtle text-secondary';
                default: return 'bg-info-subtle text-info';
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
    <Head title="Manage Reports" />

    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-1 fw-bold">Reports Management</h4>
                <p class="text-muted mb-0">View and manage user reports and complaints</p>
            </div>
            <span class="badge bg-primary fs-6">
                {{ reports.total }} Total Reports
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
                            <option value="reviewed">Reviewed</option>
                            <option value="dismissed">Dismissed</option>
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

        <!-- Reports Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" style="width: 60px;">ID</th>
                                <th>Reporter</th>
                                <th>Reported User</th>
                                <th>Trip</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th class="text-end pe-3" style="width: 100px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="reports.data.length === 0">
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="ri-flag-line fs-1 d-block mb-2"></i>
                                    No reports found matching your criteria.
                                </td>
                            </tr>
                            <tr v-for="report in reports.data" :key="report.id">
                                <td class="ps-3">
                                    <span class="text-muted">#{{ report.id }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-sm rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center">
                                            <span class="text-primary fw-semibold small">
                                                {{ report.reporter?.name ? report.reporter.name.charAt(0).toUpperCase() : '?' }}
                                            </span>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ report.reporter?.name || 'Unknown' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-sm rounded-circle bg-danger bg-opacity-10 d-flex align-items-center justify-content-center">
                                            <span class="text-danger fw-semibold small">
                                                {{ report.reported_user?.name ? report.reported_user.name.charAt(0).toUpperCase() : '?' }}
                                            </span>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ report.reported_user?.name || 'Unknown' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-muted small">Trip #{{ report.trip_id || '—' }}</span>
                                </td>
                                <td>
                                    <span class="fw-semibold">{{ report.reason }}</span>
                                    <br v-if="report.description" />
                                    <small v-if="report.description" class="text-muted text-truncate d-inline-block" style="max-width: 200px;" :title="report.description">
                                        {{ report.description }}
                                    </small>
                                </td>
                                <td>
                                    <span class="badge rounded-pill" :class="statusBadgeClass(report.status)">
                                        {{ report.status || 'unknown' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted small">{{ formatDate(report.created_at) }}</span>
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
                                            <li v-if="report.status === 'pending'">
                                                <a class="dropdown-item text-success" href="#">
                                                    <i class="ri-check-line me-2"></i> Mark Reviewed
                                                </a>
                                            </li>
                                            <li v-if="report.status === 'pending'">
                                                <a class="dropdown-item text-danger" href="#">
                                                    <i class="ri-close-line me-2"></i> Dismiss
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
        <div class="d-flex align-items-center justify-content-between mt-4" v-if="reports.last_page > 1">
            <div class="text-muted small">
                Showing {{ reports.from }} to {{ reports.to }} of {{ reports.total }} reports
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item" :class="{ disabled: !reports.prev_page_url }">
                        <Link
                            :href="reports.prev_page_url || '#'"
                            class="page-link"
                            preserve-state
                        >
                            <i class="ri-arrow-left-s-line"></i>
                        </Link>
                    </li>
                    <li
                        v-for="page in reports.last_page"
                        :key="page"
                        class="page-item"
                        :class="{ active: page === reports.current_page }"
                    >
                        <Link
                            :href="'/admin/reports?page=' + page + (statusFilter ? '&status=' + statusFilter : '')"
                            class="page-link"
                            preserve-state
                        >
                            {{ page }}
                        </Link>
                    </li>
                    <li class="page-item" :class="{ disabled: !reports.next_page_url }">
                        <Link
                            :href="reports.next_page_url || '#'"
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
