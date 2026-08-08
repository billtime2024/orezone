<script>
import { Link, Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/admin.vue';

export default {
    components: { Link, Head, AdminLayout },
    layout: AdminLayout,
    props: {
        requests: Object,
        filters: Object,
    },
    data() {
        return {
            search: this.filters?.search || '',
            statusFilter: this.filters?.status || '',
            eventType: this.filters?.event_type || '',
            dateFrom: this.filters?.date_from || '',
            dateTo: this.filters?.date_to || '',
        };
    },
    methods: {
        applyFilters() {
            this.$inertia.get('/admin/food/catering', {
                search: this.search || undefined,
                status: this.statusFilter || undefined,
                event_type: this.eventType || undefined,
                date_from: this.dateFrom || undefined,
                date_to: this.dateTo || undefined,
            }, { preserveState: true });
        },
        clearFilters() {
            this.search = '';
            this.statusFilter = '';
            this.eventType = '';
            this.dateFrom = '';
            this.dateTo = '';
            this.$inertia.get('/admin/food/catering');
        },
        statusBadgeClass(status) {
            switch (status) {
                case 'pending': return 'bg-warning-subtle text-warning';
                case 'quoted': return 'bg-info-subtle text-info';
                case 'accepted': return 'bg-success-subtle text-success';
                case 'confirmed': return 'bg-success-subtle text-success';
                case 'completed': return 'bg-secondary-subtle text-secondary';
                case 'cancelled': return 'bg-danger-subtle text-danger';
                case 'expired': return 'bg-secondary-subtle text-secondary';
                default: return 'bg-secondary-subtle text-secondary';
            }
        },
        formatCurrency(amount) {
            if (!amount) return '₹0';
            return '₹' + Number(amount).toLocaleString('en-IN');
        },
        formatDate(dateStr) {
            if (!dateStr) return '—';
            return new Date(dateStr).toLocaleDateString('en-IN', {
                day: '2-digit', month: 'short', year: 'numeric',
            });
        },
        paginationUrl(page) {
            const params = new URLSearchParams();
            if (this.search) params.set('search', this.search);
            if (this.statusFilter) params.set('status', this.statusFilter);
            if (this.eventType) params.set('event_type', this.eventType);
            if (this.dateFrom) params.set('date_from', this.dateFrom);
            if (this.dateTo) params.set('date_to', this.dateTo);
            params.set('page', page);
            return '/admin/food/catering?' + params.toString();
        },
    },
};
</script>

<template>
    <Head title="Catering Requests" />

    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-1 fw-bold">Catering Requests</h4>
                <p class="text-muted mb-0">Manage catering service requests and quotations</p>
            </div>
            <span class="badge bg-primary fs-6">
                {{ requests.total }} Total Requests
            </span>
        </div>

        <!-- Filters -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Search</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="ri-search-line text-muted"></i>
                            </span>
                            <input
                                v-model="search"
                                type="text"
                                class="form-control"
                                placeholder="Request #, customer name..."
                                @keyup.enter="applyFilters"
                            />
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Status</label>
                        <select v-model="statusFilter" class="form-select" @change="applyFilters">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="quoted">Quoted</option>
                            <option value="accepted">Accepted</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Event Type</label>
                        <select v-model="eventType" class="form-select" @change="applyFilters">
                            <option value="">All Types</option>
                            <option value="wedding">Wedding</option>
                            <option value="corporate">Corporate</option>
                            <option value="birthday">Birthday</option>
                            <option value="festival">Festival</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Date From</label>
                        <input v-model="dateFrom" type="date" class="form-control" @change="applyFilters" />
                    </div>
                    <div class="col-md-1">
                        <label class="form-label small text-muted">Date To</label>
                        <input v-model="dateTo" type="date" class="form-control" @change="applyFilters" />
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button class="btn btn-primary" @click="applyFilters">
                            <i class="ri-search-line me-1"></i> Search
                        </button>
                        <button class="btn btn-outline-secondary" @click="clearFilters">
                            <i class="ri-close-line me-1"></i> Clear
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Requests Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Request #</th>
                                <th>Customer</th>
                                <th>Event</th>
                                <th>Date</th>
                                <th>Guests</th>
                                <th>Budget</th>
                                <th>Status</th>
                                <th class="text-end pe-3" style="width: 80px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="(requests?.data || []).length === 0">
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="ri-calendar-event-line fs-1 d-block mb-2"></i>
                                    No catering requests found matching your criteria.
                                </td>
                            </tr>
                            <tr v-for="req in (requests?.data || [])" :key="req.id">
                                <td class="ps-3">
                                    <Link :href="`/admin/food/catering/${req.id}`" class="text-decoration-none fw-semibold">
                                        #{{ req.request_number || req.id }}
                                    </Link>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-sm rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center">
                                            <span class="text-info fw-semibold small">
                                                {{ req.user?.name?.charAt(0)?.toUpperCase() || '?' }}
                                            </span>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ req.user?.name || 'Unknown' }}</div>
                                            <small class="text-muted">{{ req.user?.phone || '' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ req.event_name || '—' }}</div>
                                    <small class="text-muted text-capitalize">{{ req.event_type || '' }}</small>
                                </td>
                                <td>
                                    <span class="small text-muted">{{ formatDate(req.event_date) }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        <i class="ri-user-line me-1"></i>{{ req.guests_count || '—' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-semibold">{{ formatCurrency(req.budget_min) }} - {{ formatCurrency(req.budget_max) }}</span>
                                </td>
                                <td>
                                    <span class="badge rounded-pill" :class="statusBadgeClass(req.status)">
                                        {{ req.status || 'unknown' }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <Link :href="`/admin/food/catering/${req.id}`" class="btn btn-sm btn-outline-primary">
                                        <i class="ri-eye-line"></i>
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="d-flex align-items-center justify-content-between mt-4" v-if="requests.last_page > 1">
            <div class="text-muted small">
                Showing {{ requests.from }} to {{ requests.to }} of {{ requests.total }} requests
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item" :class="{ disabled: !requests.prev_page_url }">
                        <Link
                            :href="requests.prev_page_url || '#'"
                            class="page-link"
                            preserve-state
                        >
                            <i class="ri-arrow-left-s-line"></i>
                        </Link>
                    </li>
                    <li
                        v-for="page in requests.last_page"
                        :key="page"
                        class="page-item"
                        :class="{ active: page === requests.current_page }"
                    >
                        <Link
                            :href="paginationUrl(page)"
                            class="page-link"
                            preserve-state
                        >
                            {{ page }}
                        </Link>
                    </li>
                    <li class="page-item" :class="{ disabled: !requests.next_page_url }">
                        <Link
                            :href="requests.next_page_url || '#'"
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
