<script>
import { Link, Head } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/portal-layout.vue';

export default {
    components: { Link, Head, PortalLayout },
    props: {
        providers: Object,
        categories: Array,
        featuredItems: Array,
    },
    data() {
        return {
            searchQuery: '',
        };
    },
    methods: {
        doSearch() {
            if (this.searchQuery.trim()) {
                window.location.href = `/portal/food/search?q=${encodeURIComponent(this.searchQuery.trim())}`;
            }
        },
    },
};
</script>

<template>
    <Head title="Food Services - orezone Portal" />

    <PortalLayout>
        <!-- Hero -->
        <div class="text-white rounded-4 p-4 mb-4" style="background-color: #2E7D5B;">
            <div class="d-flex align-items-center gap-3">
                <span class="bg-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                    <span style="font-size: 28px;">🥗</span>
                </span>
                <div>
                    <h1 class="h4 fw-bold mb-0">Food Services</h1>
                    <p class="mb-0 opacity-75">Order delicious pure veg food from verified providers</p>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="mt-3">
                <div class="input-group" style="max-width: 500px;">
                    <span class="input-group-text bg-white border-0"><i class="ri-search-line text-muted"></i></span>
                    <input
                        v-model="searchQuery"
                        type="text"
                        class="form-control border-0"
                        placeholder="Search for food items, restaurants..."
                        @keyup.enter="doSearch"
                    />
                    <button class="btn bg-white border-0 fw-semibold" style="color: #2E7D5B;" @click="doSearch">
                        Search
                    </button>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="row g-3 mb-4">
            <div class="col-sm-4">
                <div class="card border-0 shadow-sm text-center p-3">
                    <div class="fw-bold h4 mb-0" style="color: #2E7D5B;">{{ providers.total }}</div>
                    <small class="text-muted">Verified Providers</small>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card border-0 shadow-sm text-center p-3">
                    <div class="fw-bold h4 mb-0" style="color: #2E7D5B;">100%</div>
                    <small class="text-muted">Pure Vegetarian</small>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card border-0 shadow-sm text-center p-3">
                    <div class="fw-bold h4 mb-0" style="color: #2E7D5B;">🕐</div>
                    <small class="text-muted">Fast Delivery</small>
                </div>
            </div>
        </div>

        <!-- Categories -->
        <div class="mb-4" v-if="categories.length">
            <h5 class="fw-bold mb-3">Browse by Category</h5>
            <div class="d-flex flex-wrap gap-2">
                <Link
                    v-for="cat in categories"
                    :key="cat.id"
                    :href="`/portal/food/search?category=${cat.id}`"
                    class="badge text-decoration-none px-3 py-2"
                    style="background-color: rgba(46,125,91,0.1); color: #2E7D5B; font-size: 0.9rem;"
                >
                    <i v-if="cat.icon" :class="cat.icon" class="me-1"></i>
                    {{ cat.name }}
                </Link>
            </div>
        </div>

        <!-- Featured Items -->
        <div class="mb-4" v-if="featuredItems.length">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Featured Items</h5>
                <Link href="/portal/food/search?sort=popular" class="text-decoration-none small" style="color: #2E7D5B;">
                    View All <i class="ri-arrow-right-line"></i>
                </Link>
            </div>
            <div class="row g-3">
                <div v-for="item in featuredItems" :key="item.id" class="col-sm-6 col-lg-3">
                    <Link :href="`/portal/food/item/${item.id}`" class="text-decoration-none">
                        <div class="card border-0 shadow-sm h-100 hover-shadow">
                            <div class="card-body text-center p-3">
                                <div class="rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px; background: rgba(46,125,91,0.1);">
                                    <span v-if="item.image_url" class="rounded-circle" style="width: 64px; height: 64px; object-fit: cover;" :style="{ backgroundImage: `url(${item.image_url})`, backgroundSize: 'cover', backgroundPosition: 'center', display: 'block' }"></span>
                                    <span v-else style="font-size: 28px;">🍽️</span>
                                </div>
                                <h6 class="fw-bold text-dark mb-1">{{ item.name }}</h6>
                                <small class="text-muted d-block" v-if="item.provider">{{ item.provider.business_name }}</small>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <span class="fw-bold" style="color: #2E7D5B;">₹{{ item.discount_price || item.price }}</span>
                                    <span v-if="item.has_discount" class="text-muted text-decoration-line-through small">₹{{ item.price }}</span>
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </div>

        <!-- Providers -->
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Verified Food Providers</h5>
                <Link href="/portal/food/search" class="text-decoration-none small" style="color: #2E7D5B;">
                    Browse All <i class="ri-arrow-right-line"></i>
                </Link>
            </div>
            <div class="row g-3">
                <div v-for="provider in providers.data" :key="provider.id" class="col-sm-6 col-lg-4">
                    <Link :href="`/portal/food/provider/${provider.id}`" class="text-decoration-none">
                        <div class="card border-0 shadow-sm h-100 hover-shadow">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 52px; height: 52px; background: rgba(46,125,91,0.1);">
                                        <span v-if="provider.logo_url" class="rounded-circle" style="width: 52px; height: 52px; object-fit: cover;" :style="{ backgroundImage: `url(${provider.logo_url})`, backgroundSize: 'cover', backgroundPosition: 'center', display: 'block' }"></span>
                                        <span v-else class="fw-bold" style="color: #2E7D5B;">{{ provider.business_name?.charAt(0) }}</span>
                                    </div>
                                    <div class="flex-grow-1 min-width-0">
                                        <h6 class="fw-bold text-dark mb-0 text-truncate">{{ provider.business_name }}</h6>
                                        <small class="text-muted d-block" v-if="provider.city">{{ provider.city }}</small>
                                        <div class="d-flex align-items-center gap-2 mt-1">
                                            <span class="badge" style="background: rgba(46,125,91,0.1); color: #2E7D5B;">
                                                <i class="ri-star-fill me-1" style="font-size: 10px;"></i>{{ provider.avg_rating || '0.0' }}
                                            </span>
                                            <small class="text-muted">{{ provider.items_count }} items</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="providers.last_page > 1" class="d-flex justify-content-center">
            <nav>
                <ul class="pagination">
                    <li v-for="page in providers.last_page" :key="page" class="page-item" :class="{ active: page === providers.current_page }">
                        <Link :href="`/portal/food?page=${page}`" class="page-link">{{ page }}</Link>
                    </li>
                </ul>
            </nav>
        </div>
    </PortalLayout>
</template>

<style scoped>
.hover-shadow:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    transform: translateY(-2px);
    transition: all 0.2s ease;
}
.hover-shadow {
    transition: all 0.2s ease;
}
</style>
