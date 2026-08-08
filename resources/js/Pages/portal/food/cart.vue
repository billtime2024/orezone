<script>
import { Link, Head, router } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/portal-layout.vue';

export default {
    components: { Link, Head, PortalLayout },
    props: {
        cartItems: Array,
        subtotal: Number,
    },
    computed: {
        deliveryCharge() {
            return this.subtotal >= 500 ? 0 : 40;
        },
        total() {
            return (this.subtotal + this.deliveryCharge).toFixed(2);
        },
        itemCount() {
            return this.cartItems.reduce((sum, item) => sum + item.quantity, 0);
        },
        groupedByProvider() {
            const groups = {};
            this.cartItems.forEach(cart => {
                const providerName = cart.food_item?.provider?.business_name || 'Unknown';
                if (!groups[providerName]) groups[providerName] = [];
                groups[providerName].push(cart);
            });
            return groups;
        },
    },
    methods: {
        updateQuantity(cart, newQty) {
            if (newQty < 1) return;
            router.put(`/portal/food/cart/${cart.id}`, { quantity: newQty }, { preserveState: true });
        },
        removeItem(cart) {
            router.delete(`/portal/food/cart/${cart.id}`, { preserveState: true });
        },
        lineTotal(cart) {
            const price = cart.pricing_tier?.price || cart.food_item?.discount_price || cart.food_item?.price;
            return (price * cart.quantity).toFixed(2);
        },
    },
};
</script>

<template>
    <Head title="My Cart - orezone Food" />

    <PortalLayout>
        <!-- Breadcrumb -->
        <div class="mb-3">
            <Link href="/portal/food" class="text-decoration-none" style="color: #2E7D5B;">
                <i class="ri-arrow-left-line"></i> Food Services
            </Link>
            <span class="text-muted mx-2">/</span>
            <span class="text-dark fw-semibold">My Cart</span>
        </div>

        <h4 class="fw-bold mb-4">My Cart <span class="badge" style="background-color: #2E7D5B;">{{ itemCount }}</span></h4>

        <div v-if="cartItems.length" class="row g-4">
            <!-- Cart Items -->
            <div class="col-lg-8">
                <div v-for="(items, providerName) in groupedByProvider" :key="providerName" class="mb-4">
                    <h6 class="fw-semibold mb-3"><i class="ri-store-line me-1" style="color: #2E7D5B;"></i> {{ providerName }}</h6>
                    <div v-for="cart in items" :key="cart.id" class="card border-0 shadow-sm mb-2">
                        <div class="card-body p-3">
                            <div class="d-flex gap-3">
                                <div class="d-flex align-items-center justify-content-center flex-shrink-0" style="width: 80px; height: 80px; background: #f0f7f3; border-radius: 0.5rem;">
                                    <span v-if="cart.food_item?.image_url" style="width: 80px; height: 80px; object-fit: cover;" :style="{ backgroundImage: `url(${cart.food_item.image_url})`, backgroundSize: 'cover', backgroundPosition: 'center', display: 'block', borderRadius: '0.5rem' }"></span>
                                    <span v-else style="font-size: 28px;">🍽️</span>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="fw-bold mb-0">{{ cart.food_item?.name }}</h6>
                                            <small class="text-muted" v-if="cart.pricing_tier">{{ cart.pricing_tier.name }}</small>
                                            <small class="text-muted d-block" v-if="cart.special_notes">Note: {{ cart.special_notes }}</small>
                                        </div>
                                        <button class="btn btn-sm text-danger" @click="removeItem(cart)">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="input-group" style="width: 100px;">
                                                <button class="btn btn-sm btn-outline-secondary" @click="updateQuantity(cart, cart.quantity - 1)">−</button>
                                                <span class="form-control form-control-sm text-center py-0">{{ cart.quantity }}</span>
                                                <button class="btn btn-sm btn-outline-secondary" @click="updateQuantity(cart, cart.quantity + 1)">+</button>
                                            </div>
                                        </div>
                                        <span class="fw-bold" style="color: #2E7D5B;">₹{{ lineTotal(cart) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm sticky-top" style="top: 80px;">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3">Order Summary</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal ({{ itemCount }} items)</span>
                            <span class="fw-semibold">₹{{ subtotal.toFixed(2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Delivery</span>
                            <span :class="deliveryCharge === 0 ? 'fw-semibold' : ''" :style="deliveryCharge === 0 ? 'color: #2E7D5B;' : ''">
                                {{ deliveryCharge === 0 ? 'FREE' : '₹' + deliveryCharge.toFixed(2) }}
                            </span>
                        </div>
                        <div v-if="subtotal < 500" class="small text-muted mb-2" style="color: #2E7D5B;">
                            Add ₹{{ (500 - subtotal).toFixed(2) }} more for free delivery
                        </div>
                        <hr />
                        <div class="d-flex justify-content-between mb-3">
                            <span class="fw-bold">Total</span>
                            <span class="fw-bold fs-5" style="color: #2E7D5B;">₹{{ total }}</span>
                        </div>
                        <button class="btn w-100 text-white fw-semibold" style="background-color: #2E7D5B;">
                            <i class="ri-shopping-cart-line me-1"></i> Proceed to Checkout
                        </button>
                        <Link href="/portal/food" class="btn btn-outline-secondary w-100 mt-2">
                            Continue Shopping
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty Cart -->
        <div v-else class="text-center py-5">
            <div style="font-size: 64px;" class="mb-3">🛒</div>
            <h5 class="text-muted">Your cart is empty</h5>
            <p class="text-muted">Browse our food providers and add some delicious items!</p>
            <Link href="/portal/food" class="btn text-white" style="background-color: #2E7D5B;">
                Browse Food
            </Link>
        </div>
    </PortalLayout>
</template>
