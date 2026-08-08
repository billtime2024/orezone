<script>
import { Link, Head, router } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/portal-layout.vue';

export default {
    components: { Link, Head, PortalLayout },
    props: {
        item: Object,
        relatedItems: Array,
    },
    data() {
        return {
            quantity: 1,
            specialNotes: '',
            addingToCart: false,
            selectedTier: null,
        };
    },
    computed: {
        effectivePrice() {
            if (this.selectedTier) return this.selectedTier.price;
            return this.item.discount_price || this.item.price;
        },
        totalPrice() {
            return (this.effectivePrice * this.quantity).toFixed(2);
        },
    },
    methods: {
        addToCart() {
            this.addingToCart = true;
            router.post('/portal/food/cart/add', {
                food_item_id: this.item.id,
                quantity: this.quantity,
                pricing_tier_id: this.selectedTier?.id || null,
                special_notes: this.specialNotes || null,
            }, {
                preserveState: true,
                onSuccess: () => {
                    this.addingToCart = false;
                },
                onError: () => {
                    this.addingToCart = false;
                },
            });
        },
        increment() {
            if (this.quantity < (this.item.max_quantity || 20)) this.quantity++;
        },
        decrement() {
            if (this.quantity > (this.item.min_quantity || 1)) this.quantity--;
        },
    },
};
</script>

<template>
    <Head :title="`${item.name} - orezone Food`" />

    <PortalLayout>
        <!-- Breadcrumb -->
        <div class="mb-3">
            <Link href="/portal/food" class="text-decoration-none" style="color: #2E7D5B;">
                <i class="ri-arrow-left-line"></i> Food Services
            </Link>
            <span class="text-muted mx-2">/</span>
            <Link :href="`/portal/food/provider/${item.provider?.id}`" class="text-decoration-none" style="color: #2E7D5B;">
                {{ item.provider?.business_name }}
            </Link>
            <span class="text-muted mx-2">/</span>
            <span class="text-dark fw-semibold">{{ item.name }}</span>
        </div>

        <div class="row g-4">
            <!-- Left: Image -->
            <div class="col-md-5">
                <div class="card border-0 shadow-sm">
                    <div class="d-flex align-items-center justify-content-center" style="height: 320px; background: #f0f7f3; border-radius: 0.5rem;">
                        <span v-if="item.image_url" style="width: 100%; height: 320px; object-fit: cover;" :style="{ backgroundImage: `url(${item.image_url})`, backgroundSize: 'cover', backgroundPosition: 'center', display: 'block', borderRadius: '0.5rem' }"></span>
                        <span v-else style="font-size: 64px;">🍽️</span>
                    </div>
                </div>
            </div>

            <!-- Right: Details -->
            <div class="col-md-7">
                <div class="mb-3">
                    <div class="d-flex gap-2 mb-2">
                        <span v-if="item.category" class="badge" style="background: rgba(46,125,91,0.1); color: #2E7D5B;">{{ item.category.name }}</span>
                        <span v-if="item.is_jain" class="badge bg-warning text-dark">Jain Friendly</span>
                        <span v-if="item.is_vegan" class="badge bg-success">Vegan</span>
                    </div>
                    <h3 class="fw-bold">{{ item.name }}</h3>
                    <p class="text-muted" v-if="item.description">{{ item.description }}</p>
                </div>

                <!-- Provider Info -->
                <div class="d-flex align-items-center gap-3 mb-3 p-3 rounded-3" style="background: #f8f9fa;">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: rgba(46,125,91,0.1);">
                        <span class="fw-bold" style="color: #2E7D5B;">{{ item.provider?.business_name?.charAt(0) }}</span>
                    </div>
                    <div>
                        <Link :href="`/portal/food/provider/${item.provider?.id}`" class="fw-semibold text-dark text-decoration-none">
                            {{ item.provider?.business_name }}
                        </Link>
                        <small class="text-muted d-block" v-if="item.provider?.city">{{ item.provider?.city }}</small>
                    </div>
                </div>

                <!-- Price -->
                <div class="mb-3">
                    <span class="fw-bold" style="color: #2E7D5B; font-size: 1.5rem;">₹{{ effectivePrice }}</span>
                    <span v-if="item.has_discount" class="text-muted text-decoration-line-through ms-2 fs-5">₹{{ item.price }}</span>
                    <span v-if="item.has_discount" class="badge ms-2" style="background-color: #2E7D5B;">
                        Save ₹{{ (item.price - item.discount_price).toFixed(2) }}
                    </span>
                </div>

                <!-- Pricing Tiers -->
                <div v-if="item.pricing_tiers && item.pricing_tiers.length" class="mb-3">
                    <label class="form-label small fw-semibold">Select Size</label>
                    <div class="d-flex flex-wrap gap-2">
                        <button
                            v-for="tier in item.pricing_tiers"
                            :key="tier.id"
                            class="btn btn-sm"
                            :class="selectedTier?.id === tier.id ? 'text-white' : ''"
                            :style="selectedTier?.id === tier.id ? 'background-color: #2E7D5B;' : 'border: 1px solid #dee2e6;'"
                            @click="selectedTier = tier"
                        >
                            {{ tier.name }} - ₹{{ tier.price }}
                        </button>
                    </div>
                </div>

                <!-- Quantity -->
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Quantity</label>
                    <div class="d-flex align-items-center gap-3">
                        <div class="input-group" style="width: 140px;">
                            <button class="btn btn-outline-secondary" @click="decrement">−</button>
                            <input type="number" class="form-control text-center" v-model.number="quantity" :min="item.min_quantity || 1" :max="item.max_quantity || 20" readonly />
                            <button class="btn btn-outline-secondary" @click="increment">+</button>
                        </div>
                        <span v-if="item.preparation_time_min" class="text-muted small">
                            <i class="ri-time-line me-1"></i>{{ item.preparation_time_min }} min prep time
                        </span>
                    </div>
                </div>

                <!-- Special Notes -->
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Special Instructions (Optional)</label>
                    <textarea v-model="specialNotes" class="form-control" rows="2" placeholder="Any allergies or special requests..."></textarea>
                </div>

                <!-- Total & Add to Cart -->
                <div class="d-flex align-items-center justify-content-between p-3 rounded-3 mb-3" style="background: #f0f7f3;">
                    <div>
                        <small class="text-muted">Total</small>
                        <div class="fw-bold fs-5" style="color: #2E7D5B;">₹{{ totalPrice }}</div>
                    </div>
                    <button
                        class="btn btn-lg text-white px-4"
                        style="background-color: #2E7D5B;"
                        :disabled="addingToCart || !item.is_available"
                        @click="addToCart"
                    >
                        <i v-if="addingToCart" class="ri-loader-4-line spin me-1"></i>
                        <i v-else class="ri-shopping-cart-line me-1"></i>
                        {{ addingToCart ? 'Adding...' : 'Add to Cart' }}
                    </button>
                </div>

                <!-- Allergens -->
                <div v-if="item.allergens && item.allergens.length" class="mb-3">
                    <small class="text-muted"><i class="ri-alert-line me-1"></i>Contains: {{ item.allergens.join(', ') }}</small>
                </div>
            </div>
        </div>

        <!-- Related Items -->
        <div v-if="relatedItems.length" class="mt-5">
            <h5 class="fw-bold mb-3">More from {{ item.provider?.business_name }}</h5>
            <div class="row g-3">
                <div v-for="related in relatedItems" :key="related.id" class="col-sm-6 col-lg-2">
                    <Link :href="`/portal/food/item/${related.id}`" class="text-decoration-none">
                        <div class="card border-0 shadow-sm h-100 text-center hover-shadow">
                            <div class="d-flex align-items-center justify-content-center" style="height: 100px; background: #f0f7f3;">
                                <span v-if="related.image_url" style="width: 100%; height: 100px; object-fit: cover;" :style="{ backgroundImage: `url(${related.image_url})`, backgroundSize: 'cover', backgroundPosition: 'center', display: 'block' }"></span>
                                <span v-else style="font-size: 24px;">🍽️</span>
                            </div>
                            <div class="card-body p-2">
                                <small class="fw-bold text-dark d-block text-truncate">{{ related.name }}</small>
                                <small style="color: #2E7D5B;">₹{{ related.discount_price || related.price }}</small>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>

<style scoped>
.hover-shadow:hover { box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important; transform: translateY(-2px); transition: all 0.2s ease; }
.hover-shadow { transition: all 0.2s ease; }
.spin { animation: spin 1s linear infinite; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>
