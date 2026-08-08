<script>
import { Link, Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/admin.vue';

export default {
    components: { Link, Head, AdminLayout },
    layout: AdminLayout,
    props: {
        wallet: Object,
        transactions: Object,
    },
    methods: {
        statusBadgeClass(status) {
            if (status === 'active') return 'bg-success-subtle text-success';
            if (status === 'inactive') return 'bg-danger-subtle text-danger';
            return 'bg-secondary-subtle text-secondary';
        },
        directionBadgeClass(direction) {
            if (direction === 'credit') return 'bg-success-subtle text-success';
            if (direction === 'debit') return 'bg-danger-subtle text-danger';
            return 'bg-secondary-subtle text-secondary';
        },
        formatCurrency(amount) {
            return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(amount || 0);
        },
        formatDate(dateStr) {
            if (!dateStr) return '—';
            return new Date(dateStr).toLocaleDateString('en-IN', {
                day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit',
            });
        },
        goToPage(page) {
            this.$inertia.get(`/admin/wallets/${this.wallet.id}`, { page }, { preserveState: true });
        },
    },
};
</script>

<template>
    <Head :title="`Wallet #${wallet.id}`" />

    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Wallet #{{ wallet.id }}</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><Link href="/admin/wallets">Wallets</Link></li>
                        <li class="breadcrumb-item active">Wallet #{{ wallet.id }}</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Wallet Header -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h6 class="text-white-50 mb-1">Balance</h6>
                        <h3 class="mb-0">{{ formatCurrency(wallet.balance) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-1">Currency</h6>
                        <h5 class="mb-0">{{ wallet.currency || 'INR' }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-1">Status</h6>
                        <span class="badge fs-6" :class="statusBadgeClass(wallet.is_active ? 'active' : 'inactive')">
                            {{ wallet.is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-1">User</h6>
                        <h6 class="mb-0">{{ wallet.user?.name || 'N/A' }}</h6>
                        <small class="text-muted">{{ wallet.user?.email }}</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaction History -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Transaction History</h5>
                <span class="badge bg-primary-subtle text-primary">
                    {{ transactions.total || 0 }} transactions
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Direction</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Balance Before</th>
                                <th>Balance After</th>
                                <th>Description</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="tx in transactions.data" :key="tx.id">
                                <td>#{{ tx.id }}</td>
                                <td>
                                    <span class="badge" :class="directionBadgeClass(tx.direction)">
                                        {{ tx.direction }}
                                    </span>
                                </td>
                                <td class="text-capitalize">{{ tx.type?.replace('_', ' ') }}</td>
                                <td :class="tx.direction === 'credit' ? 'text-success fw-semibold' : 'text-danger fw-semibold'">
                                    {{ tx.direction === 'credit' ? '+' : '-' }}{{ formatCurrency(tx.amount) }}
                                </td>
                                <td>{{ formatCurrency(tx.balance_before) }}</td>
                                <td>{{ formatCurrency(tx.balance_after) }}</td>
                                <td>{{ tx.description || '—' }}</td>
                                <td>{{ formatDate(tx.created_at) }}</td>
                            </tr>
                            <tr v-if="!transactions.data?.length">
                                <td colspan="8" class="text-center text-muted py-4">No transactions found</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Pagination -->
            <div v-if="transactions.last_page > 1" class="card-footer">
                <nav>
                    <ul class="pagination pagination-sm justify-content-center mb-0">
                        <li class="page-item" :class="{ disabled: transactions.current_page === 1 }">
                            <button class="page-link" @click="goToPage(transactions.current_page - 1)"
                                :disabled="transactions.current_page === 1">
                                <i class="ri-arrow-left-s-line"></i>
                            </button>
                        </li>
                        <li v-for="page in transactions.last_page" :key="page"
                            class="page-item" :class="{ active: page === transactions.current_page }">
                            <button class="page-link" @click="goToPage(page)">{{ page }}</button>
                        </li>
                        <li class="page-item" :class="{ disabled: transactions.current_page === transactions.last_page }">
                            <button class="page-link" @click="goToPage(transactions.current_page + 1)"
                                :disabled="transactions.current_page === transactions.last_page">
                                <i class="ri-arrow-right-s-line"></i>
                            </button>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</template>
