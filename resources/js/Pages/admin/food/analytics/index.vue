<script>
import { Link, Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/admin.vue';

export default {
    components: { Link, Head, AdminLayout },
    layout: AdminLayout,
    props: {
        stats: {
            type: Object,
            default: () => ({}),
        },
    },
    data() {
        return {
            period: this.stats?.period || '30d',
        };
    },
    methods: {
        changePeriod(p) {
            this.period = p;
            this.$inertia.get('/admin/food/analytics', { period: p }, { preserveState: true });
        },
        formatCurrency(amount) {
            if (!amount) return '₹0';
            return '₹' + Number(amount).toLocaleString('en-IN');
        },
        statusBadgeClass(status) {
            switch (status) {
                case 'pending': return 'bg-warning-subtle text-warning';
                case 'confirmed': return 'bg-info-subtle text-info';
                case 'preparing': return 'bg-info-subtle text-info';
                case 'delivered': case 'completed': return 'bg-success-subtle text-success';
                case 'cancelled': return 'bg-danger-subtle text-danger';
                default: return 'bg-secondary-subtle text-secondary';
            }
        },
    },
};
</script>

<template>
    <Head title="Food Services Analytics" />

    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-1 fw-bold">Food Services Analytics</h4>
                <p class="text-muted mb-0">Performance overview for the food services module</p>
            </div>
            <div class="btn-group">
                <button
                    class="btn btn-sm"
                    :class="period === '7d' ? 'btn-primary' : 'btn-outline-secondary'"
                    @click="changePeriod('7d')"
                >7 Days</button>
                <button
                    class="btn btn-sm"
                    :class="period === '30d' ? 'btn-primary' : 'btn-outline-secondary'"
                    @click="changePeriod('30d')"
                >30 Days</button>
                <button
                    class="btn btn-sm"
                    :class="period === '90d' ? 'btn-primary' : 'btn-outline-secondary'"
                    @click="changePeriod('90d')"
                >90 Days</button>
                <button
                    class="btn btn-sm"
                    :class="period === 'all' ? 'btn-primary' : 'btn-outline-secondary'"
                    @click="changePeriod('all')"
                >All Time</button>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-2">
                <div class="card border-0 shadow-sm text-center p-3">
                    <div class="fw-bold text-primary fs-4">{{ stats.total_orders || 0 }}</div>
                    <small class="text-muted">Total Orders</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-0 shadow-sm text-center p-3">
                    <div class="fw-bold text-success fs-4">{{ formatCurrency(stats.total_revenue) }}</div>
                    <small class="text-muted">Revenue</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-0 shadow-sm text-center p-3">
                    <div class="fw-bold text-info fs-4">{{ formatCurrency(stats.total_commission) }}</div>
                    <small class="text-muted">Commission</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-0 shadow-sm text-center p-3">
                    <div class="fw-bold fs-4">{{ stats.active_providers || 0 }}</div>
                    <small class="text-muted">Active Providers</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-0 shadow-sm text-center p-3">
                    <div class="fw-bold text-warning fs-4">{{ formatCurrency(stats.avg_order_value) }}</div>
                    <small class="text-muted">Avg Order Value</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-0 shadow-sm text-center p-3">
                    <div class="fw-bold fs-4">{{ stats.avg_delivery_time || '—' }}</div>
                    <small class="text-muted">Avg Delivery (min)</small>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Top Providers -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-bold">Top Providers</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Provider</th>
                                    <th class="text-end">Orders</th>
                                    <th class="text-end">Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(provider, idx) in (stats?.top_providers || [])" :key="provider.id">
                                    <td>
                                        <span class="badge bg-light text-dark">{{ idx + 1 }}</span>
                                    </td>
                                    <td>
                                        <Link :href="`/admin/food/providers/${provider.id}`" class="text-decoration-none fw-semibold">
                                            {{ provider.business_name }}
                                        </Link>
                                    </td>
                                    <td class="text-end">{{ provider.total_orders || 0 }}</td>
                                    <td class="text-end fw-semibold text-success">{{ formatCurrency(provider.total_revenue) }}</td>
                                </tr>
                                <tr v-if="!stats.top_providers?.length">
                                    <td colspan="4" class="text-center text-muted py-3">No data available</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Order Status Distribution -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-bold">Order Status Distribution</h6>
                    </div>
                    <div class="card-body">
                        <div v-for="(count, status) in (stats?.status_distribution || {})" :key="status" class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="badge rounded-pill" :class="statusBadgeClass(status)">
                                    {{ status?.replace('_', ' ') }}
                                </span>
                                <span class="fw-semibold">{{ count }}</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div
                                    class="progress-bar bg-primary"
                                    :style="{ width: (stats.total_orders ? (count / stats.total_orders * 100) : 0) + '%' }"
                                ></div>
                            </div>
                        </div>
                        <div v-if="!stats.status_distribution" class="text-center text-muted py-3">No data available</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Performance -->
        <div class="card border-0 shadow-sm mt-4" v-if="stats.category_performance?.length">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-bold">Category Performance</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Category</th>
                            <th class="text-center">Orders</th>
                            <th class="text-end">Revenue</th>
                            <th class="text-end">Avg Rating</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="cat in (stats?.category_performance || [])" :key="cat.name">
                            <td class="fw-semibold">{{ cat.name }}</td>
                            <td class="text-center">{{ cat.orders || 0 }}</td>
                            <td class="text-end fw-semibold text-success">{{ formatCurrency(cat.revenue) }}</td>
                            <td class="text-end">
                                <span class="text-warning" v-if="cat.avg_rating">★ {{ Number(cat.avg_rating).toFixed(1) }}</span>
                                <span v-else class="text-muted">—</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
