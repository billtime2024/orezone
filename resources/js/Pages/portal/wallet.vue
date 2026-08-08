<script>
import { Link, Head } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/portal-layout.vue';

export default {
    components: { Link, Head, PortalLayout },
    props: {
        user: Object,
        wallet: Object,
        transactions: Array,
    },
    methods: {
        formatDate(date) {
            if (!date) return '—';
            return new Date(date).toLocaleDateString('en-US', {
                year: 'numeric', month: 'short', day: 'numeric',
                hour: '2-digit', minute: '2-digit',
            });
        },
        directionBadge(direction) {
            return direction === 'credit' ? 'bg-success' : 'bg-danger';
        },
        formatAmount(amount) {
            return new Intl.NumberFormat('en-IN', {
                style: 'currency',
                currency: 'INR',
            }).format(amount || 0);
        },
    },
};
</script>

<template>
    <Head title="Wallet - orezone" />

    <PortalLayout>
        <div class="mb-4">
            <h4 class="fw-bold mb-1">Wallet</h4>
            <p class="text-muted mb-0">Manage your wallet balance and transactions</p>
        </div>

        <!-- Wallet Card -->
        <div class="card border-0 shadow-sm mb-4 bg-primary text-white">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1 opacity-75">Available Balance</h6>
                        <h2 class="fw-bold mb-0">{{ formatAmount(wallet?.balance) }}</h2>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-white bg-opacity-25">
                            <i class="ri-wallet-3-line me-1"></i> Wallet
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaction History -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h5 class="fw-bold text-dark mb-3">Transaction History</h5>

                <div v-if="!transactions || transactions.length === 0" class="text-center py-4">
                    <p class="text-muted">No transactions yet</p>
                </div>

                <div v-else class="list-group">
                    <div class="list-group-item d-flex justify-content-between align-items-center" v-for="tx in transactions" :key="tx.id">
                        <div class="d-flex align-items-center gap-3">
                            <div
                                class="rounded-circle d-flex align-items-center justify-content-center"
                                :class="tx.direction === 'credit' ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger'"
                                style="width: 40px; height: 40px;"
                            >
                                <i :class="tx.direction === 'credit' ? 'ri-add-line' : 'ri-subtract-line'"></i>
                            </div>
                            <div>
                                <div class="fw-medium">{{ tx.type || 'Transaction' }}</div>
                                <small class="text-muted">{{ formatDate(tx.created_at) }}</small>
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="fw-bold" :class="tx.direction === 'credit' ? 'text-success' : 'text-danger'">
                                {{ tx.direction === 'credit' ? '+' : '-' }}{{ formatAmount(tx.amount) }}
                            </span>
                            <div>
                                <span class="badge" :class="directionBadge(tx.direction)">
                                    {{ tx.direction }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>
