<script>
import { Link, Head, router } from '@inertiajs/vue3';

export default {
    components: { Link, Head },
    props: {
        user: Object,
        wallet: Object,
        transactions: Array,
    },
    methods: {
        logout() {
            router.post('/logout');
        },
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
    },
};
</script>

<template>
    <Head title="Wallet - orezone" />

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <Link href="/" class="navbar-brand fw-bold fs-4 text-white">orezone</Link>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#portalNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="portalNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="bg-white text-primary rounded-circle d-inline-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 14px;">
                                {{ user.name ? user.name.charAt(0).toUpperCase() : 'U' }}
                            </span>
                            <span class="d-none d-lg-inline">{{ user.name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><span class="dropdown-item-text text-muted small">{{ user.email }}</span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><Link href="/portal" class="dropdown-item">Dashboard</Link></li>
                            <li><Link href="/portal/profile" class="dropdown-item">Profile</Link></li>
                            <li><Link href="/portal/trips" class="dropdown-item">My Trips</Link></li>
                            <li><Link href="/portal/bookings" class="dropdown-item">My Bookings</Link></li>
                            <li><Link href="/portal/wallet" class="dropdown-item active">Wallet</Link></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><button @click="logout" class="dropdown-item text-danger">Logout</button></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="bg-light min-vh-100">
        <!-- Header -->
        <div class="bg-primary text-white py-4">
            <div class="container">
                <div class="d-flex align-items-center gap-3">
                    <span style="font-size: 2rem;">💰</span>
                    <div>
                        <h1 class="h4 fw-bold mb-0">Wallet</h1>
                        <p class="mb-0 opacity-75 small">Manage your balance and transactions</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="container py-4">
            <!-- Balance Card -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center py-4">
                            <p class="text-muted small mb-1">Current Balance</p>
                            <h2 class="fw-bold text-primary mb-0">
                                {{ wallet ? wallet.currency || '₹' : '₹' }}{{ wallet ? wallet.balance : '0.00' }}
                            </h2>
                            <p v-if="wallet && wallet.currency" class="text-muted small mt-1 mb-0">{{ wallet.currency }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transactions -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 pt-3">
                    <h5 class="fw-bold mb-0">Transaction History</h5>
                </div>
                <div class="card-body">
                    <!-- Empty State -->
                    <div v-if="!transactions || transactions.length === 0" class="text-center py-4">
                        <p class="text-muted mb-0">No transactions yet.</p>
                    </div>

                    <!-- Transactions Table -->
                    <div v-else class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Type</th>
                                    <th>Direction</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="tx in transactions" :key="tx.id">
                                    <td class="text-capitalize">{{ tx.type || '—' }}</td>
                                    <td>
                                        <span :class="['badge', directionBadge(tx.direction)]">
                                            {{ tx.direction }}
                                        </span>
                                    </td>
                                    <td class="fw-semibold">
                                        {{ tx.direction === 'credit' ? '+' : '-' }}{{ wallet?.currency || '₹' }}{{ tx.amount }}
                                    </td>
                                    <td class="text-muted small">{{ formatDate(tx.created_at) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-dark text-white py-4">
        <div class="container text-center">
            <p class="mb-0 small opacity-75">&copy; 2026 orezone. All rights reserved.</p>
        </div>
    </footer>
</template>
