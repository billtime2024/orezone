<script>
import { Link, Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/admin.vue';

export default {
    components: { Link, Head, AdminLayout },
    layout: AdminLayout,
    props: {
        orders: Object,
        filters: Object,
    },
    data() {
        return {
            search: this.filters?.search || '',
            statusFilter: this.filters?.status || '',
            orderType: this.filters?.order_type || '',
            deliveryType: this.filters?.delivery_type || '',
            dateFrom: this.filters?.date_from || '',
            dateTo: this.filters?.date_to || '',
        };
    },
    methods: {
        applyFilters() {
            this.$inertia.get('/admin/food/orders', {
                search: this.search || undefined,
                status: this.statusFilter || undefined,
                order_type: this.orderType || undefined,
                delivery_type: this.deliveryType || undefined,
                date_from: this.dateFrom || undefined,
                date_to: this.dateTo || undefined,
            }, { preserveState: true });
        },
        clearFilters() {
            this.search = '';
            this.statusFilter = '';
            this.orderType = '';
            this.deliveryType = '';
            this.dateFrom = '';
            this.dateTo = '';
            this.$inertia.get('/admin/food/orders');
        },
        statusBadgeClass(status) {
            switch (status) {
                case 'pending': return 'bg-warning-subtle text-warning';
                case 'confirmed': return 'bg-info-subtle text-info';
                case 'preparing': return 'bg-info-subtle text-info';
                case 'out_for_delivery': return 'bg-primary-subtle text-primary';
                case 'delivered': case 'completed': return 'bg-success-subtle text-success';
                case 'cancelled': return 'bg-danger-subtle text-danger';
                case 'refunded': return 'bg-secondary-subtle text-secondary';
                default: return 'bg-secondary-subtle text-secondary';
            }
        },
        paymentBadgeClass(status) {
            switch (status) {
                case 'paid': return 'bg-success-subtle text-success';
                case 'pending': return 'bg-warning-subtle text-warning';
                case 'failed': return 'bg-danger-subtle text-danger';
                case 'refunded': return 'bg-secondary-subtle text-secondary';
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
            if (this.orderType) params.set('order_type', this.orderType);
            if (this.deliveryType) params.set('delivery_type', this.deliveryType);
            if (this.dateFrom) params.set('date_from', this.dateFrom);
            if (this.dateTo) params.set('date_to', this.dateTo);
            params.set('page', page);
            return '/admin/food/orders?' + params.toString();
        },
    },
};
</script>

<template>
    <Head title="Food Orders" />

    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-1 fw-bold">Food Orders</h4>
                <p class="text-muted mb-0">View and manage all food service orders</p>
            </div>
            <span class="badge bg-primary fs-6">
                {{ orders.total }} Total Orders
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
                                placeholder="Order #, customer name..."
                                @keyup.enter="applyFilters"
                            />
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Status</label>
                        <select v-model="statusFilter" class="form-select" @change="applyFilters">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="preparing">Preparing</option>
                            <option value="out_for_delivery">Out for Delivery</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Order Type</label>
                        <select v-model="orderType" class="form-select" @change="applyFilters">
                            <option value="">All Types</option>
                            <option value="delivery">Delivery</option>
                            <option value="pickup">Pickup</option>
                            <option value="dine_in">Dine In</option>
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

        <!-- Orders Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Order #</th>
                                <th>Customer</th>
                                <th>Provider</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Amount</th>
                                <th>Payment</th>
                                <th>Date</th>
                                <th class="text-end pe-3" style="width: 80px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="(orders?.data || []).length === 0">
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="ri-shopping-bag-line fs-1 d-block mb-2"></i>
                                    No orders found matching your criteria.
                                </td>
                            </tr>
                            <tr v-for="order in (orders?.data || [])" :key="order.id">
                                <td class="ps-3">
                                    <Link :href="`/admin/food/orders/${order.id}`" class="text-decoration-none fw-semibold">
                                        #{{ order.order_number }}
                                    </Link>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-sm rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center">
                                            <span class="text-info fw-semibold small">
                                                {{ order.customer?.name?.charAt(0)?.toUpperCase() || '?' }}
                                            </span>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ order.customer?.name || 'Unknown' }}</div>
                                            <small class="text-muted">{{ order.customer?.phone || '' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-semibold">{{ order.provider?.business_name || '—' }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark text-capitalize">{{ order.order_type || '—' }}</span>
                                </td>
                                <td>
                                    <span class="badge rounded-pill" :class="statusBadgeClass(order.status)">
                                        {{ order.status?.replace('_', ' ') || 'unknown' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-success">{{ formatCurrency(order.total_amount) }}</span>
                                </td>
                                <td>
                                    <span class="badge rounded-pill" :class="paymentBadgeClass(order.payment_status)">
                                        {{ order.payment_status || 'pending' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="small text-muted">{{ formatDate(order.created_at) }}</span>
                                </td>
                                <td class="text-end pe-3">
                                    <Link :href="`/admin/food/orders/${order.id}`" class="btn btn-sm btn-outline-primary">
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
        <div class="d-flex align-items-center justify-content-between mt-4" v-if="orders.last_page > 1">
            <div class="text-muted small">
                Showing {{ orders.from }} to {{ orders.to }} of {{ orders.total }} orders
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item" :class="{ disabled: !orders.prev_page_url }">
                        <Link
                            :href="orders.prev_page_url || '#'"
                            class="page-link"
                            preserve-state
                        >
                            <i class="ri-arrow-left-s-line"></i>
                        </Link>
                    </li>
                    <li
                        v-for="page in orders.last_page"
                        :key="page"
                        class="page-item"
                        :class="{ active: page === orders.current_page }"
                    >
                        <Link
                            :href="paginationUrl(page)"
                            class="page-link"
                            preserve-state
                        >
                            {{ page }}
                        </Link>
                    </li>
                    <li class="page-item" :class="{ disabled: !orders.next_page_url }">
                        <Link
                            :href="orders.next_page_url || '#'"
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
