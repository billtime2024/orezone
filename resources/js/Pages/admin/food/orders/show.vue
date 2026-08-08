<script>
import { Link, Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/admin.vue';

export default {
    components: { Link, Head, AdminLayout },
    layout: AdminLayout,
    props: {
        order: Object,
    },
    computed: {
        canCancel() {
            return ['pending', 'confirmed'].includes(this.order.status);
        },
    },
    methods: {
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
        formatDateTime(dateStr) {
            if (!dateStr) return '—';
            return new Date(dateStr).toLocaleDateString('en-IN', {
                day: '2-digit', month: 'short', year: 'numeric',
                hour: '2-digit', minute: '2-digit',
            });
        },
    },
};
</script>

<template>
    <Head :title="`Order #${order.order_number}`" />

    <div class="container-fluid">
        <!-- Breadcrumb & Header -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <Link href="/admin/food/orders" class="text-decoration-none text-muted">
                        <i class="ri-arrow-left-line"></i> Orders
                    </Link>
                    <span class="text-muted">/</span>
                    <span class="fw-bold">#{{ order.order_number }}</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge rounded-pill" :class="statusBadgeClass(order.status)">
                        {{ order.status?.replace('_', ' ') || 'unknown' }}
                    </span>
                    <span class="text-muted small">Placed on {{ formatDateTime(order.created_at) }}</span>
                </div>
            </div>
            <div class="d-flex gap-2" v-if="canCancel">
                <Link
                    :href="`/admin/food/orders/${order.id}/cancel`"
                    method="post"
                    as="button"
                    class="btn btn-sm btn-outline-danger"
                >
                    <i class="ri-close-circle-line me-1"></i> Cancel Order
                </Link>
            </div>
        </div>

        <div class="row g-4">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Order Items -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-bold">Order Items</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Item Name</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in order.items" :key="item.id">
                                    <td class="fw-semibold">
                                        {{ item.name }}
                                        <span class="badge bg-success-subtle text-success ms-1 small" v-if="item.is_veg">
                                            <i class="ri-leaf-line"></i>
                                        </span>
                                    </td>
                                    <td class="text-center">{{ item.quantity }}</td>
                                    <td class="text-end">{{ formatCurrency(item.price) }}</td>
                                    <td class="text-end fw-semibold">{{ formatCurrency(item.price * item.quantity) }}</td>
                                </tr>
                                <tr v-if="!order.items?.length">
                                    <td colspan="4" class="text-center text-muted py-3">No items</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Payment Summary -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-bold">Payment Summary</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Subtotal</span>
                                    <span class="fw-semibold">{{ formatCurrency(order.subtotal) }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Delivery Charge</span>
                                    <span>{{ formatCurrency(order.delivery_charge) }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2" v-if="order.discount">
                                    <span class="text-muted">Discount</span>
                                    <span class="text-success">-{{ formatCurrency(order.discount_amount) }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Tax</span>
                                    <span>{{ formatCurrency(order.tax) }}</span>
                                </div>
                                <hr />
                                <div class="d-flex justify-content-between">
                                    <span class="fw-bold">Total</span>
                                    <span class="fw-bold text-primary fs-5">{{ formatCurrency(order.total_amount) }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light border-0">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted">Commission ({{ order.provider?.commission_rate || 10 }}%)</span>
                                            <span class="fw-semibold text-info">{{ formatCurrency(order.commission_amount) }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Provider Earning</span>
                                            <span class="fw-semibold text-success">{{ formatCurrency(order.total_amount - order.commission_amount) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment & Delivery Info -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-bottom">
                                <h6 class="mb-0 fw-bold">Payment Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-2">
                                    <label class="form-label small text-muted">Method</label>
                                    <div class="fw-semibold text-capitalize">{{ order.payment_method || '—' }}</div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small text-muted">Status</label>
                                    <div>
                                        <span class="badge rounded-pill" :class="paymentBadgeClass(order.payment_status)">
                                            {{ order.payment_status || 'pending' }}
                                        </span>
                                    </div>
                                </div>
                                <div v-if="order.payment_id">
                                    <label class="form-label small text-muted">Payment ID</label>
                                    <div class="small text-muted">{{ order.payment_id }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-bottom">
                                <h6 class="mb-0 fw-bold">Delivery Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-2">
                                    <label class="form-label small text-muted">Type</label>
                                    <div class="fw-semibold text-capitalize">{{ order.delivery_type || order.order_type || '—' }}</div>
                                </div>
                                <div class="mb-2" v-if="order.delivery_address">
                                    <label class="form-label small text-muted">Address</label>
                                    <div>{{ order.delivery_address }}</div>
                                </div>
                                <div v-if="order.delivery_slot">
                                    <label class="form-label small text-muted">Time Slot</label>
                                    <div>{{ order.delivery_slot }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Special Instructions -->
                <div class="card border-0 shadow-sm" v-if="order.special_instructions">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-bold">Special Instructions</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0 text-muted">{{ order.special_instructions }}</p>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Customer Info -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-bold">Customer</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="avatar-sm rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center">
                                <span class="text-info fw-semibold">
                                    {{ order.user?.name?.charAt(0)?.toUpperCase() || '?' }}
                                </span>
                            </div>
                            <div>
                                <div class="fw-semibold">{{ order.user?.name || 'Unknown' }}</div>
                                <small class="text-muted">{{ order.user?.phone || '' }}</small>
                            </div>
                        </div>
                        <div v-if="order.user?.email" class="small text-muted">
                            <i class="ri-mail-line me-1"></i> {{ order.user.email }}
                        </div>
                    </div>
                </div>

                <!-- Provider Info -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-bold">Provider</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-sm rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center">
                                <img
                                    v-if="order.provider?.logo"
                                    :src="order.provider.logo"
                                    class="rounded-circle"
                                    style="width: 36px; height: 36px; object-fit: cover;"
                                />
                                <span v-else class="text-success fw-semibold">
                                    {{ order.provider?.business_name?.charAt(0)?.toUpperCase() || '?' }}
                                </span>
                            </div>
                            <div>
                                <Link :href="`/admin/food/providers/${order.provider?.id}`" class="text-decoration-none fw-semibold">
                                    {{ order.provider?.business_name || 'Unknown' }}
                                </Link>
                                <div>
                                    <small class="text-muted text-capitalize">{{ order.provider?.provider_type || '' }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
