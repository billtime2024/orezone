<script>
import { Link, Head, router } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/portal-layout.vue';

export default {
    components: { Link, Head, PortalLayout },
    props: {
        provider: Object,
        order: Object,
    },
    data() {
        return {
            updating: false,
            nextStatuses: {
                placed: ['confirmed', 'cancelled'],
                confirmed: ['preparing', 'cancelled'],
                preparing: ['ready'],
                ready: ['out_for_delivery'],
                out_for_delivery: ['delivered'],
            },
        };
    },
    computed: {
        availableStatuses() {
            return this.nextStatuses[this.order.status] || [];
        },
        statusLabel() {
            const map = {
                placed: 'Placed',
                confirmed: 'Confirmed',
                preparing: 'Preparing',
                ready: 'Ready for Pickup',
                out_for_delivery: 'Out for Delivery',
                delivered: 'Delivered',
                cancelled: 'Cancelled',
            };
            return map[this.order.status] || this.order.status;
        },
        statusBadgeClass() {
            const map = {
                placed: 'bg-warning text-dark',
                confirmed: 'bg-info text-dark',
                preparing: 'bg-primary',
                ready: 'bg-success',
                out_for_delivery: 'bg-info',
                delivered: 'bg-success',
                cancelled: 'bg-danger',
            };
            return map[this.order.status] || 'bg-secondary';
        },
    },
    methods: {
        formatCurrency(amount) {
            return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(amount || 0);
        },
        formatDate(date) {
            if (!date) return '—';
            return new Date(date).toLocaleString('en-IN', {
                day: 'numeric', month: 'short', year: 'numeric',
                hour: '2-digit', minute: '2-digit',
            });
        },
        updateStatus(status) {
            if (!confirm(`Update order to "${status}"?`)) return;
            this.updating = true;
            router.post(`/portal/food-provider/orders/${this.order.id}/status`, {
                status: status,
            }, {
                onFinish: () => { this.updating = false; },
            });
        },
    },
};
</script>

<template>
    <Head :title="`Order #${order.order_number} - orezone Provider`" />
    <PortalLayout>
        <div class="d-flex align-items-center gap-3 mb-4">
            <Link href="/portal/food-provider/orders" class="btn btn-sm btn-outline-secondary">
                <i class="ri-arrow-left-line"></i>
            </Link>
            <div class="flex-grow-1">
                <h4 class="fw-bold text-dark mb-1">Order #{{ order.order_number }}</h4>
                <p class="text-muted mb-0">Placed {{ formatDate(order.created_at) }}</p>
            </div>
            <span class="badge fs-6" :class="statusBadgeClass">{{ statusLabel }}</span>
        </div>

        <div class="row g-4">
            <!-- Order Details -->
            <div class="col-lg-8">
                <!-- Items -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-semibold">Order Items</h6>
                    </div>
                    <div class="card-body p-0">
                        <table class="table align-middle mb-0">
                            <tbody>
                                <tr v-for="item in order.items" :key="item.id">
                                    <td class="px-3" style="width: 60px;">
                                        <img v-if="item.food_item?.image_url" :src="item.food_item.image_url"
                                            class="rounded" style="width: 48px; height: 48px; object-fit: cover;">
                                        <div v-else class="rounded d-flex align-items-center justify-content-center"
                                            style="width: 48px; height: 48px; background: rgba(46,125,91,0.1);">
                                            🍽️
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ item.food_item?.name || 'Item' }}</div>
                                        <small class="text-muted">Qty: {{ item.quantity }} × {{ formatCurrency(item.unit_price) }}</small>
                                    </td>
                                    <td class="px-3 text-end fw-semibold">{{ formatCurrency(item.total_price) }}</td>
                                </tr>
                                <tr v-if="!order.items?.length">
                                    <td colspan="3" class="text-center py-3 text-muted">No items</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-semibold">Order Summary</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal</span>
                            <span>{{ formatCurrency(order.subtotal) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Delivery Charge</span>
                            <span>{{ formatCurrency(order.delivery_charge) }}</span>
                        </div>
                        <div v-if="order.discount_amount > 0" class="d-flex justify-content-between mb-2 text-success">
                            <span>Discount</span>
                            <span>-{{ formatCurrency(order.discount_amount) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Tax</span>
                            <span>{{ formatCurrency(order.tax_amount) }}</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold fs-5">
                            <span>Total</span>
                            <span style="color: #2E7D5B;">{{ formatCurrency(order.total_amount) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Special Instructions -->
                <div v-if="order.special_instructions" class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-semibold">Special Instructions</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ order.special_instructions }}</p>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Customer Info -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-semibold">Customer</h6>
                    </div>
                    <div class="card-body">
                        <div class="fw-semibold text-dark">{{ order.user?.name || 'Guest' }}</div>
                        <div class="text-muted small">{{ order.user?.phone || '' }}</div>
                        <div class="text-muted small">{{ order.user?.email || '' }}</div>
                    </div>
                </div>

                <!-- Delivery Info -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-semibold">Delivery</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <span class="badge bg-light text-dark text-capitalize">{{ order.delivery_type }}</span>
                        </div>
                        <p v-if="order.delivery_address" class="mb-1 small">{{ order.delivery_address }}</p>
                        <p v-if="order.scheduled_at" class="mb-0 small text-muted">
                            Scheduled: {{ formatDate(order.scheduled_at) }}
                        </p>
                    </div>
                </div>

                <!-- Payment -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-semibold">Payment</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Method</span>
                            <span class="text-capitalize">{{ order.payment_method || 'Online' }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Status</span>
                            <span class="badge" :class="order.payment_status === 'paid' ? 'bg-success' : 'bg-warning text-dark'">
                                {{ order.payment_status }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card border-0 shadow-sm" v-if="availableStatuses.length > 0">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-semibold">Update Status</h6>
                    </div>
                    <div class="card-body d-grid gap-2">
                        <button v-for="status in availableStatuses" :key="status"
                            class="btn text-white text-capitalize"
                            :class="status === 'cancelled' ? 'btn-danger' : ''"
                            :style="status !== 'cancelled' ? 'background-color: #2E7D5B;' : ''"
                            :disabled="updating"
                            @click="updateStatus(status)">
                            <span v-if="updating" class="spinner-border spinner-border-sm me-1"></span>
                            {{ status.replace('_', ' ') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>
