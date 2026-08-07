<script>
import { Link, Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/admin.vue';

export default {
    components: { Link, Head, AdminLayout },
    layout: AdminLayout,
    props: {
        wallets: Object,
    },
    data() {
        return {
            statusFilter: this.$page.props.wallets?.query?.status || '',
        };
    },
    methods: {
        applyFilters() {
            this.$inertia.get('/admin/wallets', {
                status: this.statusFilter || undefined,
            }, { preserveState: true });
        },
        clearFilters() {
            this.statusFilter = '';
            this.$inertia.get('/admin/wallets');
        },
        statusBadgeClass(isActive) {
            return isActive ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary';
        },
        formatCurrency(amount, currency) {
            return new Intl.NumberFormat('en-IN', {
                style: 'currency',
                currency: currency || 'INR',
            }).format(amount);
        },
    },
};
</script>

<template>
    <Head title="Manage Wallets" />

    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-1 fw-bold">Wallets Management</h4>
                <p class="text-muted mb-0">View and manage user wallets</p>
            </div>
            <span class="badge bg-primary fs-6">
                {{ wallets.total }} Total Wallets
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
                            <option value="inactive">Inactive</option>
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

        <!-- Wallets Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" style="width: 60px;">ID</th>
                                <th>User</th>
                                <th>Balance</th>
                                <th>Currency</th>
                                <th>Status</th>
                                <th class="text-end pe-3" style="width: 100px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="wallets.data.length === 0">
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="ri-wallet-3-line fs-1 d-block mb-2"></i>
                                    No wallets found matching your criteria.
                                </td>
                            </tr>
                            <tr v-for="wallet in wallets.data" :key="wallet.id">
                                <td class="ps-3">
                                    <span class="text-muted">#{{ wallet.id }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-sm rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center">
                                            <span class="text-primary fw-semibold small">
                                                {{ wallet.user?.name ? wallet.user.name.charAt(0).toUpperCase() : '?' }}
                                            </span>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ wallet.user?.name || 'Unknown' }}</div>
                                            <small class="text-muted">{{ wallet.user?.email || '—' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-semibold text-success">
                                        {{ formatCurrency(wallet.balance, wallet.currency) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted">{{ wallet.currency || 'INR' }}</span>
                                </td>
                                <td>
                                    <span class="badge rounded-pill" :class="statusBadgeClass(wallet.is_active)">
                                        {{ wallet.is_active ? 'Active' : 'Inactive' }}
                                    </span>
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
        <div class="d-flex align-items-center justify-content-between mt-4" v-if="wallets.last_page > 1">
            <div class="text-muted small">
                Showing {{ wallets.from }} to {{ wallets.to }} of {{ wallets.total }} wallets
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item" :class="{ disabled: !wallets.prev_page_url }">
                        <Link
                            :href="wallets.prev_page_url || '#'"
                            class="page-link"
                            preserve-state
                        >
                            <i class="ri-arrow-left-s-line"></i>
                        </Link>
                    </li>
                    <li
                        v-for="page in wallets.last_page"
                        :key="page"
                        class="page-item"
                        :class="{ active: page === wallets.current_page }"
                    >
                        <Link
                            :href="'/admin/wallets?page=' + page + (statusFilter ? '&status=' + statusFilter : '')"
                            class="page-link"
                            preserve-state
                        >
                            {{ page }}
                        </Link>
                    </li>
                    <li class="page-item" :class="{ disabled: !wallets.next_page_url }">
                        <Link
                            :href="wallets.next_page_url || '#'"
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
