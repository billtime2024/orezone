<script>
import { Link, Head } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/portal-layout.vue';

export default {
    components: { Link, Head, PortalLayout },
    props: {
        provider: Object,
        orders: Object,
        statusFilter: String,
    },
    data() {
        return {
            statuses: [
                { value: '', label: 'All Orders' },
                { value: 'placed', label: 'Placed' },
                { value: 'confirmed', label: 'Confirmed' },
                { value: 'preparing', label: 'Preparing' },
                { value: 'ready', label: 'Ready' },
                { value: 'out_for_delivery', label: 'Out for Delivery' },
                { value: 'delivered', label: 'Delivered' },
                { value: 'cancelled', label: 'Cancelled' },
            ],
        };
    },
    methods: {
        formatCurrency(amount) {
            return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(amount || 0);
        },
        formatDateTime(date) {
            if (!date) return '—';
            return new Date(date).toLocaleString('en-IN', {
                day: 'numeric', month: 'short', year: 'numeric',
                hour: '2-digit', minute: '2-digit',
            });
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
        getStatusUrl(status) {
            return status ? `/portal/food-provider/orders?status=${status}` : '/portal/food-provider/orders';
        },
    },
};
</script>

<template>
    <Head title="Orders - orezone Provider" />
    <PortalLayout>
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold text-dark mb-1">Orders</h4>
                <p class="text-muted mb-0">Manage incoming and past orders</p>
            </div>
        </div>

        <!-- Status Filter Tabs -->
        <div class="mb-4">
            <div class="d-flex flex-wrap gap-2">
                <Link v-for="s in statuses" :key="s.value"
                    :href="getStatusUrl(s.value)"
                    class="btn btn-sm rounded-pill"
                    :class="statusFilter === s.value || (!statusFilter && s.value === '') ? 'text-white' : 'btn-outline-secondary'"
                    :style="(statusFilter === s.value || (!statusFilter && s.value === '')) ? 'background-color: #2E7D5B;' : ''">
                    {{ s.label }}
                </Link>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-3">Order #</th>
                                <th>Customer</th>
                                <th>Type</th>
                                <th>Items</th>
                                <th>Amount</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th class="px-3 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="orders.data?.length === 0">
                                <td colspan="9" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="ri-shopping-cart-2-line fs-1 d-block mb-2"></i>
                                        <p class="mb-0">No orders found</p>
                                    </div>
                                </td>
                            </tr>
                            <tr v-for="order in orders.data" :key="order.id">
                                <td class="px-3">
                                    <span class="fw-semibold text-dark">#{{ order.order_number }}</span>
                                </td>
                                <td>
                                    <div class="fw-medium">{{ order.user?.name || 'Guest' }}</div>
                                    <small class="text-muted">{{ order.user?.phone || '' }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark text-capitalize">{{ order.delivery_type }}</span>
                                </td>
                                <td>{{ order.items_count || '—' }}</td>
                                <td class="fw-semibold">{{ formatCurrency(order.total_amount) }}</td>
                                <td>
                                    <span class="badge" :class="order.payment_status === 'paid' ? 'bg-success' : 'bg-warning text-dark'">
                                        {{ order.payment_status }}
                                    </span>
                                </td>
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

        <!-- Pagination -->
        <div v-if="orders.last_page > 1" class="d-flex justify-content-center mt-4">
            <nav>
                <ul class="pagination">
                    <li v-for="page in orders.last_page" :key="page" class="page-item"
                        :class="{ active: page === orders.current_page }">
                        <Link :href="orders.path + (statusFilter ? '?status=' + statusFilter + '&' : '?') + 'page=' + page"
                            class="page-link">{{ page }}</Link>
                    </li>
                </ul>
            </nav>
        </div>
    </PortalLayout>
</template>
