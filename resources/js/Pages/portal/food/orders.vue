<script>
import { Link, Head } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/portal-layout.vue';

export default {
    components: { Link, Head, PortalLayout },
    props: {
        orders: Object,
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
        };
    },
    methods: {
        formatStatus(status) {
            return status?.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        },
    },
};
</script>

<template>
    <Head title="My Orders - orezone Food" />

    <PortalLayout>
        <!-- Breadcrumb -->
        <div class="mb-3">
            <Link href="/portal/food" class="text-decoration-none" style="color: #2E7D5B;">
                <i class="ri-arrow-left-line"></i> Food Services
            </Link>
            <span class="text-muted mx-2">/</span>
            <span class="text-dark fw-semibold">My Orders</span>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">My Orders</h4>
            <Link href="/portal/food" class="btn btn-sm text-white" style="background-color: #2E7D5B;">
                <i class="ri-add-line me-1"></i> Order Food
            </Link>
        </div>

        <div v-if="orders.data.length">
            <div v-for="order in orders.data" :key="order.id" class="card border-0 shadow-sm mb-3">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="fw-bold">Order #{{ order.order_number }}</span>
                                <span class="badge" :class="'bg-' + (statusColors[order.status] || 'secondary')">
                                    {{ formatStatus(order.status) }}
                                </span>
                            </div>
                            <small class="text-muted">{{ order.created_at }}</small>
                        </div>
                        <Link :href="`/portal/food/orders/${order.id}`" class="btn btn-sm btn-outline-secondary">
                            View Details
                        </Link>
                    </div>

                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: rgba(46,125,91,0.1);">
                            <span v-if="order.provider?.logo_url" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;" :style="{ backgroundImage: `url(${order.provider.logo_url})`, backgroundSize: 'cover', backgroundPosition: 'center', display: 'block' }"></span>
                            <span v-else class="fw-bold" style="color: #2E7D5B;">{{ order.provider?.business_name?.charAt(0) }}</span>
                        </div>
                        <div>
                            <small class="fw-semibold">{{ order.provider?.business_name }}</small>
                            <small class="text-muted d-block">{{ order.items_count }} items</small>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                        <div class="d-flex gap-3">
                            <small class="text-muted"><i class="ri-map-pin-line me-1"></i>{{ order.delivery_type === 'delivery' ? 'Delivery' : 'Pickup' }}</small>
                            <small class="text-muted"><i class="ri-wallet-line me-1"></i>{{ order.payment_status === 'paid' ? 'Paid' : 'Pending' }}</small>
                        </div>
                        <span class="fw-bold" style="color: #2E7D5B;">₹{{ order.total_amount }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="text-center py-5">
            <div style="font-size: 64px;" class="mb-3">📋</div>
            <h5 class="text-muted">No orders yet</h5>
            <p class="text-muted">Place your first food order and it will appear here</p>
            <Link href="/portal/food" class="btn text-white" style="background-color: #2E7D5B;">Browse Food</Link>
        </div>

        <div v-if="orders.last_page > 1" class="d-flex justify-content-center mt-4">
            <nav>
                <ul class="pagination">
                    <li v-for="page in orders.last_page" :key="page" class="page-item" :class="{ active: page === orders.current_page }">
                        <Link :href="`/portal/food/orders?page=${page}`" class="page-link">{{ page }}</Link>
                    </li>
                </ul>
            </nav>
        </div>
    </PortalLayout>
</template>
