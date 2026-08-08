<script>
import { Link, Head } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/portal-layout.vue';

export default {
    components: { Link, Head, PortalLayout },
    props: {
        order: Object,
    },
    data() {
        return {
            statusColors: {
                placed: 'primary',
                confirmed: 'info',
                preparing: 'warning',
                ready: 'success',
                out_for_delivery: 'success',
                delivered: 'success',
                cancelled: 'danger',
                refunded: 'secondary',
            },
            statusSteps: ['placed', 'confirmed', 'preparing', 'ready', 'out_for_delivery', 'delivered'],
        };
    },
    computed: {
        currentStepIndex() {
            return this.statusSteps.indexOf(this.order.status);
        },
    },
    methods: {
        formatStatus(status) {
            return status?.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        },
    },
};
</script>

<template>
    <Head :title="`Order #${order.order_number} - orezone Food`" />

    <PortalLayout>
        <!-- Breadcrumb -->
        <div class="mb-3">
            <Link href="/portal/food/orders" class="text-decoration-none" style="color: #2E7D5B;">
                <i class="ri-arrow-left-line"></i> My Orders
            </Link>
            <span class="text-muted mx-2">/</span>
            <span class="text-dark fw-semibold">#{{ order.order_number }}</span>
        </div>

        <!-- Order Header -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h4 class="fw-bold mb-1">Order #{{ order.order_number }}</h4>
                        <span class="text-muted">{{ order.created_at }}</span>
                    </div>
                    <span class="badge fs-6" :class="'bg-' + (statusColors[order.status] || 'secondary')">
                        {{ formatStatus(order.status) }}
                    </span>
                </div>

                <!-- Progress Bar -->
                <div v-if="order.status !== 'cancelled' && order.status !== 'refunded'" class="mt-4">
                    <div class="d-flex justify-content-between position-relative" style="margin-bottom: 8px;">
                        <div v-for="(step, idx) in statusSteps" :key="step" class="text-center flex-grow-1">
                            <div
                                class="rounded-circle mx-auto d-flex align-items-center justify-content-center mb-1"
                                :style="{
                                    width: '24px', height: '24px', fontSize: '12px',
                                    backgroundColor: idx <= currentStepIndex ? '#2E7D5B' : '#dee2e6',
                                    color: idx <= currentStepIndex ? 'white' : '#6c757d',
                                }"
                            >
                                <i v-if="idx < currentStepIndex" class="ri-check-line"></i>
                                <span v-else>{{ idx + 1 }}</span>
                            </div>
                            <small class="text-muted" style="font-size: 0.7rem;">{{ formatStatus(step) }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Order Items -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="fw-bold mb-0">Order Items</h6>
                    </div>
                    <div class="card-body p-0">
                        <div v-for="item in order.items" :key="item.id" class="d-flex align-items-center gap-3 p-3 border-bottom">
                            <div class="rounded d-flex align-items-center justify-content-center flex-shrink-0" style="width: 56px; height: 56px; background: #f0f7f3;">
                                <span v-if="item.food_item?.image_url" style="width: 56px; height: 56px; object-fit: cover; border-radius: 4px;" :style="{ backgroundImage: `url(${item.food_item.image_url})`, backgroundSize: 'cover', backgroundPosition: 'center', display: 'block' }"></span>
                                <span v-else style="font-size: 20px;">🍽️</span>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold">{{ item.name }}</div>
                                <small class="text-muted">Qty: {{ item.quantity }} × ₹{{ item.price }}</small>
                            </div>
                            <span class="fw-bold" style="color: #2E7D5B;">₹{{ item.total }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Details -->
            <div class="col-lg-4">
                <!-- Payment Summary -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="fw-bold mb-0">Payment Summary</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal</span>
                            <span>₹{{ order.subtotal }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Delivery</span>
                            <span>{{ order.delivery_charge > 0 ? '₹' + order.delivery_charge : 'FREE' }}</span>
                        </div>
                        <div v-if="order.discount_amount > 0" class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Discount</span>
                            <span style="color: #2E7D5B;">-₹{{ order.discount_amount }}</span>
                        </div>
                        <div v-if="order.tax_amount > 0" class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Tax</span>
                            <span>₹{{ order.tax_amount }}</span>
                        </div>
                        <hr />
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold">Total</span>
                            <span class="fw-bold fs-5" style="color: #2E7D5B;">₹{{ order.total_amount }}</span>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">Payment: {{ order.payment_method || 'N/A' }}</small>
                            <span class="badge ms-2" :class="order.payment_status === 'paid' ? 'bg-success' : 'bg-warning'">
                                {{ order.payment_status }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Provider Info -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">Provider</h6>
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: rgba(46,125,91,0.1);">
                                <span class="fw-bold" style="color: #2E7D5B;">{{ order.provider?.business_name?.charAt(0) }}</span>
                            </div>
                            <div>
                                <div class="fw-semibold">{{ order.provider?.business_name }}</div>
                                <small class="text-muted" v-if="order.provider?.phone">{{ order.provider.phone }}</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delivery Info -->
                <div class="card border-0 shadow-sm" v-if="order.delivery_address">
                    <div class="card-body">
                        <h6 class="fw-bold mb-2">Delivery Address</h6>
                        <p class="text-muted mb-0 small">{{ order.delivery_address }}</p>
                        <small v-if="order.scheduled_at" class="text-muted">
                            <i class="ri-time-line me-1"></i>Scheduled: {{ order.scheduled_at }}
                        </small>
                    </div>
                </div>

                <div v-if="order.special_instructions" class="card border-0 shadow-sm mt-4">
                    <div class="card-body">
                        <h6 class="fw-bold mb-2">Special Instructions</h6>
                        <p class="text-muted mb-0 small">{{ order.special_instructions }}</p>
                    </div>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>
