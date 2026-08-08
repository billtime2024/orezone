<script>
import { Link, Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/admin.vue';

export default {
    components: { Link, Head, AdminLayout },
    layout: AdminLayout,
    props: {
        commissions: Object,
        summary: Object,
    },
    methods: {
        statusBadgeClass(status) {
            switch (status) {
                case 'pending': return 'bg-warning-subtle text-warning';
                case 'approved': return 'bg-info-subtle text-info';
                case 'paid': return 'bg-success-subtle text-success';
                case 'rejected': return 'bg-danger-subtle text-danger';
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
            params.set('page', page);
            return '/admin/food/commissions?' + params.toString();
        },
    },
};
</script>

<template>
    <Head title="Commission Reports" />

    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-1 fw-bold">Commission Reports</h4>
                <p class="text-muted mb-0">Track provider commissions and payout status</p>
            </div>
            <button class="btn btn-outline-primary btn-sm">
                <i class="ri-download-line me-1"></i> Export
            </button>
        </div>

        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm text-center p-3">
                    <div class="fw-bold text-primary fs-4">{{ formatCurrency(summary.total_commission) }}</div>
                    <small class="text-muted">Total Commission</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm text-center p-3">
                    <div class="fw-bold text-warning fs-4">{{ formatCurrency(summary.pending_payouts) }}</div>
                    <small class="text-muted">Pending Payouts</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm text-center p-3">
                    <div class="fw-bold text-success fs-4">{{ formatCurrency(summary.paid_out) }}</div>
                    <small class="text-muted">Paid Out</small>
                </div>
            </div>
        </div>

        <!-- Commissions Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Provider</th>
                                <th class="text-center">Orders</th>
                                <th class="text-end">Revenue</th>
                                <th class="text-center">Rate</th>
                                <th class="text-end">Commission</th>
                                <th>Status</th>
                                <th class="text-end pe-3" style="width: 100px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="(commissions?.data || []).length === 0">
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="ri-wallet-line fs-1 d-block mb-2"></i>
                                    No commission records found.
                                </td>
                            </tr>
                            <tr v-for="item in (commissions?.data || [])" :key="item.id">
                                <td class="ps-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-sm rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center">
                                            <span class="text-success fw-semibold small">
                                                {{ item.provider?.business_name?.charAt(0)?.toUpperCase() || '?' }}
                                            </span>
                                        </div>
                                        <div>
                                            <Link :href="`/admin/food/providers/${item.provider?.id}`" class="text-decoration-none fw-semibold">
                                                {{ item.provider?.business_name || '—' }}
                                            </Link>
                                            <div><small class="text-muted">{{ item.user?.name || '' }}</small></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">{{ item.total_orders || 0 }}</td>
                                <td class="text-end">{{ formatCurrency(item.total_revenue) }}</td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark">{{ item.commission_rate || 10 }}%</span>
                                </td>
                                <td class="text-end fw-semibold text-success">{{ formatCurrency(item.commission_amount) }}</td>
                                <td>
                                    <span class="badge rounded-pill" :class="statusBadgeClass(item.status)">
                                        {{ item.status || 'pending' }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                            <i class="ri-more-2-fill"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <Link :href="`/admin/food/commissions/${item.id}`" class="dropdown-item">
                                                    <i class="ri-eye-line me-2"></i> View
                                                </Link>
                                            </li>
                                            <li v-if="item.status === 'pending'">
                                                <Link :href="`/admin/food/commissions/${item.id}/approve`" method="post" as="button" class="dropdown-item text-success">
                                                    <i class="ri-check-line me-2"></i> Approve
                                                </Link>
                                            </li>
                                            <li v-if="item.status === 'approved'">
                                                <Link :href="`/admin/food/commissions/${item.id}/mark-paid`" method="post" as="button" class="dropdown-item text-success">
                                                    <i class="ri-bank-card-line me-2"></i> Mark Paid
                                                </Link>
                                            </li>
                                            <li v-if="item.status === 'pending'">
                                                <Link :href="`/admin/food/commissions/${item.id}/reject`" method="post" as="button" class="dropdown-item text-danger">
                                                    <i class="ri-close-line me-2"></i> Reject
                                                </Link>
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
        <div class="d-flex align-items-center justify-content-between mt-4" v-if="(commissions?.last_page ?? 1) > 1">
            <div class="text-muted small">
                Showing {{ commissions?.from ?? 0 }} to {{ commissions?.to ?? 0 }} of {{ commissions?.total ?? 0 }} records
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item" :class="{ disabled: !commissions?.prev_page_url }">
                        <Link
                            :href="commissions?.prev_page_url || '#'"
                            class="page-link"
                            preserve-state
                        >
                            <i class="ri-arrow-left-s-line"></i>
                        </Link>
                    </li>
                    <li
                        v-for="page in (commissions?.last_page ?? 1)"
                        :key="page"
                        class="page-item"
                        :class="{ active: page === commissions?.current_page ?? 1 }"
                    >
                        <Link
                            :href="paginationUrl(page)"
                            class="page-link"
                            preserve-state
                        >
                            {{ page }}
                        </Link>
                    </li>
                    <li class="page-item" :class="{ disabled: !commissions?.next_page_url }">
                        <Link
                            :href="commissions?.next_page_url || '#'"
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
