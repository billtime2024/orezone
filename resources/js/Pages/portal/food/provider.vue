<script>
import { Link, Head, router } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/portal-layout.vue';

export default {
    components: { Link, Head, PortalLayout },
    props: {
        provider: Object,
        items: Array,
        categories: Array,
        reviews: Array,
    },
    data() {
        return {
            activeCategory: null,
            addingToCart: null,
            quantities: {},
        };
    },
    computed: {
        filteredItems() {
            if (!this.activeCategory) return this.items;
            return this.items.filter(i => i.category_id === this.activeCategory);
        },
        groupedItems() {
            const groups = {};
            this.items.forEach(item => {
                const catName = item.category?.name || 'Other';
                if (!groups[catName]) groups[catName] = [];
                groups[catName].push(item);
            });
            return groups;
        },
    },
    methods: {
        addToCart(item) {
            const qty = this.quantities[item.id] || 1;
            this.addingToCart = item.id;
            router.post('/portal/food/cart/add', {
                food_item_id: item.id,
                quantity: qty,
            }, {
                preserveState: true,
                onSuccess: () => {
                    this.addingToCart = null;
                    this.quantities[item.id] = 1;
                },
                onError: () => {
                    this.addingToCart = null;
                },
            });
        },
    },
};
</script>

<template>
    <Head :title="`${provider.business_name} - Food Services`" />

    <PortalLayout>
        <!-- Breadcrumb -->
        <div class="mb-3">
            <Link href="/portal/food" class="text-decoration-none" style="color: #2E7D5B;">
                <i class="ri-arrow-left-line"></i> Food Services
            </Link>
            <span class="text-muted mx-2">/</span>
            <span class="text-dark fw-semibold">{{ provider.business_name }}</span>
        </div>

        <!-- Provider Header -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-4">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 80px; height: 80px; background: rgba(46,125,91,0.1);">
                        <span v-if="provider.logo_url" class="rounded-circle" style="width: 80px; height: 80px; object-fit: cover;" :style="{ backgroundImage: `url(${provider.logo_url})`, backgroundSize: 'cover', backgroundPosition: 'center', display: 'block' }"></span>
                        <span v-else class="fw-bold fs-3" style="color: #2E7D5B;">{{ provider.business_name?.charAt(0) }}</span>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h4 class="fw-bold mb-0">{{ provider.business_name }}</h4>
                            <span v-if="provider.verification_status === 'approved'" class="badge" style="background-color: #2E7D5B;">
                                <i class="ri-verified-badge-fill me-1"></i>Verified
                            </span>
                        </div>
                        <p class="text-muted mb-2" v-if="provider.description">{{ provider.description }}</p>
                        <div class="d-flex flex-wrap gap-3">
                            <span v-if="provider.city" class="text-muted small"><i class="ri-map-pin-line me-1"></i>{{ provider.city }}</span>
                            <span v-if="provider.avg_rating > 0" class="small">
                                <i class="ri-star-fill" style="color: #f59e0b;"></i> {{ provider.avg_rating }}
                            </span>
                            <span class="text-muted small"><i class="ri-shopping-bag-line me-1"></i>{{ provider.items_count }} items</span>
                            <span v-if="provider.delivery_radius_km" class="text-muted small"><i class="ri-truck-line me-1"></i>{{ provider.delivery_radius_km }}km delivery</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Tabs -->
        <div class="d-flex flex-wrap gap-2 mb-4" v-if="categories.length">
            <button
                class="btn btn-sm rounded-pill"
                :class="activeCategory === null ? 'text-white' : ''"
                :style="activeCategory === null ? 'background-color: #2E7D5B;' : 'border: 1px solid #dee2e6;'"
                @click="activeCategory = null"
            >
                All
            </button>
            <button
                v-for="cat in categories"
                :key="cat.id"
                class="btn btn-sm rounded-pill"
                :class="activeCategory === cat.id ? 'text-white' : ''"
                :style="activeCategory === cat.id ? 'background-color: #2E7D5B;' : 'border: 1px solid #dee2e6;'"
                @click="activeCategory = cat.id"
            >
                {{ cat.name }}
            </button>
        </div>

        <!-- Menu Items -->
        <div v-if="filteredItems.length">
            <!-- Grouped view -->
            <div v-for="(catItems, catName) in groupedItems" :key="catName" class="mb-4" v-show="!activeCategory || filteredItems.some(i => i.category?.name === catName)">
                <h5 class="fw-bold mb-3">{{ catName }}</h5>
                <div class="row g-3">
                    <div v-for="item in catItems.filter(i => !activeCategory || i.category_id === activeCategory)" :key="item.id" class="col-sm-6 col-lg-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="d-flex" style="min-height: 120px;">
                                <div class="d-flex align-items-center justify-content-center flex-shrink-0" style="width: 120px; background: #f0f7f3;">
                                    <span v-if="item.image_url" style="width: 120px; height: 120px; object-fit: cover;" :style="{ backgroundImage: `url(${item.image_url})`, backgroundSize: 'cover', backgroundPosition: 'center', display: 'block' }"></span>
                                    <span v-else style="font-size: 32px;">🍽️</span>
                                </div>
                                <div class="card-body p-3 d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <h6 class="fw-bold mb-1">{{ item.name }}</h6>
                                        <div class="d-flex gap-1">
                                            <span v-if="item.is_jain" class="badge bg-warning text-dark" style="font-size: 0.6rem;">Jain</span>
                                            <span v-if="item.is_vegan" class="badge bg-success" style="font-size: 0.6rem;">Vegan</span>
                                        </div>
                                    </div>
                                    <p class="text-muted small mb-2 flex-grow-1" v-if="item.description">{{ item.description?.substring(0, 80) }}...</p>
                                    <div class="d-flex align-items-center justify-content-between mt-auto">
                                        <div>
                                            <span class="fw-bold" style="color: #2E7D5B;">₹{{ item.discount_price || item.price }}</span>
                                            <span v-if="item.has_discount" class="text-muted text-decoration-line-through small ms-1">₹{{ item.price }}</span>
                                        </div>
                                        <button
                                            class="btn btn-sm text-white"
                                            style="background-color: #2E7D5B;"
                                            :disabled="addingToCart === item.id"
                                            @click="addToCart(item)"
                                        >
                                            <i v-if="addingToCart === item.id" class="ri-loader-4-line spin"></i>
                                            <span v-else><i class="ri-add-line"></i> Add</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="text-center py-5">
            <p class="text-muted">No menu items available at the moment.</p>
        </div>

        <!-- Reviews -->
        <div v-if="reviews.length" class="mt-4">
            <h5 class="fw-bold mb-3">Recent Reviews</h5>
            <div v-for="review in reviews" :key="review.id" class="card border-0 shadow-sm mb-2">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-semibold">{{ review.user?.name || 'Customer' }}</span>
                            <div>
                                <i v-for="s in 5" :key="s" class="ri-star-fill" :style="{ color: s <= review.rating ? '#f59e0b' : '#dee2e6', fontSize: '12px' }"></i>
                            </div>
                        </div>
                        <small class="text-muted">{{ review.created_at }}</small>
                    </div>
                    <p class="mb-0 text-muted small" v-if="review.comment">{{ review.comment }}</p>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>

<style scoped>
.spin { animation: spin 1s linear infinite; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>
