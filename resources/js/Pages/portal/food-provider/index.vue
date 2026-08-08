<script>
import { Link, Head } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/portal-layout.vue';

export default {
    components: { Link, Head, PortalLayout },
    props: {
        provider: Object,
        stats: Object,
        recentOrders: Array,
    },
    methods: {
        formatCurrency(amount) {
            return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(amount || 0);
        },
        formatDate(date) {
            if (!date) return '—';
            return new Date(date).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
        },
        formatDateTime(date) {
            if (!date) return '—';
            return new Date(date).toLocaleString('en-IN', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
        },
        statusBadge(status) {
            const map = {
                placed: 'bg-warning text-dark',
                confirmed: 'bg-info text-dark',
                preparing: 'bg-primary',
                ready: 'bg-success',
                out_for_delivery: 'bg-info',
                delivered: 'bg-success',
                cancelled: 'bg-danger',
                refunded: 'bg-secondary',
            };
            return map[status] || 'bg-secondary';
        },
    },
};
</script>

<template>
    <Head title="Provider Dashboard - orezone" />
    <PortalLayout>
        <!-- Welcome Banner -->
        <div class="text-white rounded-4 p-4 mb-4" style="background: linear-gradient(135deg, #2E7D5B, #1a5c3d);">
            <div class="d-flex align-items-center gap-3">
                <span class="bg-white rounded-circle d-inline-flex align-items-center justify-content-center fw-bold"
                    style="width: 56px; height: 56px; font-size: 22px; color: #2E7D5B;">
                    🍽️
                </span>
                <div>
                    <h1 class="h4 fw-bold mb-0">{{ provider.business_name }}</h1>
                    <p class="mb-0 opacity-75">Food Provider Dashboard</p>
                </div>
            </div>
            <div class="d-flex gap-4 flex-wrap mt-3">
                <span class="badge bg-white bg-opacity-25 text-white">
                    <i class="ri-verified-badge-fill me-1" v-if="provider.verification_status === 'approved'"></i>
                    {{ provider.verification_status === 'approved' ? 'Verified Provider' : 'Verification Pending' }}
                </span>
                <span class="badge" :class="provider.is_active ? 'bg-success' : 'bg-secondary'">
                    {{ provider.is_active ? 'Active' : 'Inactive' }}
                </span>
                <span v-if="provider.is_featured" class="badge bg-warning text-dark">
                    <i class="ri-star-fill me-1"></i> Featured
                </span>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm text-center p-3">
                    <div class="text-primary fw-bold fs-3">{{ stats.total_items }}</div>
                    <small class="text-muted">Total Items</small>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm text-center p-3">
                    <div class="fw-bold fs-3" style="color: #2E7D5B;">{{ stats.pending_orders }}</div>
                    <small class="text-muted">Pending Orders</small>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm text-center p-3">
                    <div class="text-success fw-bold fs-3">{{ stats.today_orders }}</div>
                    <small class="text-muted">Today's Orders</small>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm text-center p-3">
                    <div class="text-warning fw-bold fs-3">{{ formatCurrency(stats.total_revenue) }}</div>
                    <small class="text-muted">Total Revenue</small>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-lg-3">
                <Link href="/portal/food-provider/menu" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 text-center p-4 hover-shadow">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px; background: rgba(46,125,91,0.1);">
                            <span class="display-6">📋</span>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">My Menu</h6>
                        <small class="text-muted">{{ stats.active_items }} active items</small>
                    </div>
                </Link>
            </div>
            <div class="col-sm-6 col-lg-3">
                <Link href="/portal/food-provider/orders" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 text-center p-4 hover-shadow">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px; background: rgba(255,152,0,0.1);">
                            <span class="display-6">🛒</span>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">Orders</h6>
                        <small class="text-muted">{{ stats.active_orders }} active</small>
                    </div>
                </Link>
            </div>
            <div class="col-sm-6 col-lg-3">
                <Link href="/portal/food-provider/catering" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 text-center p-4 hover-shadow">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px; background: rgba(33,150,243,0.1);">
                            <span class="display-6">🎉</span>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">Catering</h6>
                        <small class="text-muted">{{ stats.pending_catering }} pending</small>
                    </div>
                </Link>
            </div>
            <div class="col-sm-6 col-lg-3">
                <Link href="/portal/food-provider/profile" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 text-center p-4 hover-shadow">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px; background: rgba(156,39,176,0.1);">
                            <span class="display-6">👤</span>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">Profile</h6>
                        <small class="text-muted">Manage details</small>
                    </div>
                </Link>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between">
                <h5 class="mb-0 fw-semibold">Recent Orders</h5>
                <Link href="/portal/food-provider/orders" class="btn btn-sm btn-outline-success">View All</Link>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-3">Order #</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th class="px-3 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="recentOrders.length === 0">
                                <td colspan="6" class="text-center py-4 text-muted">No orders yet</td>
                            </tr>
                            <tr v-for="order in recentOrders" :key="order.id">
                                <td class="px-3">
                                    <span class="fw-semibold text-dark">#{{ order.order_number }}</span>
                                </td>
                                <td>{{ order.user?.name || 'Guest' }}</td>
                                <td class="fw-semibold">{{ formatCurrency(order.total_amount) }}</td>
                                <td>
                                    <span class="badge" :class="statusBadge(order.status)">
                                        {{ order.status?.replace('_', ' ') }}
                                    </span>
                                </td>
                                <td>{{ formatDateTime(order.created_at) }}</td>
                                <td class="px-3 text-end">
                                    <Link :href="`/portal/food-provider/orders/${order.id}`"
                                        class="btn btn-sm btn-outline-success">
                                        View
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>

<style scoped>
.hover-shadow:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    transform: translateY(-2px);
    transition: all 0.2s ease;
}
.hover-shadow {
    transition: all 0.2s ease;
}
</style>
