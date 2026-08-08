<script>
import { Link, Head, router } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/portal-layout.vue';

export default {
    components: { Link, Head, PortalLayout },
    props: {
        items: Object,
        categories: Array,
        filters: Object,
    },
    data() {
        return {
            searchQuery: this.filters.q || '',
            selectedCategory: this.filters.category || '',
            selectedSort: this.filters.sort || 'relevance',
        };
    },
    methods: {
        applyFilters() {
            const params = {};
            if (this.searchQuery.trim()) params.q = this.searchQuery.trim();
            if (this.selectedCategory) params.category = this.selectedCategory;
            if (this.selectedSort !== 'relevance') params.sort = this.selectedSort;
            if (this.filters.city) params.city = this.filters.city;

            router.get('/portal/food/search', params, { preserveState: true });
        },
        formatPrice(item) {
            return item.discount_price || item.price;
        },
    },
};
</script>

<template>
    <Head title="Search Food - orezone Portal" />

    <PortalLayout>
        <!-- Search Header -->
        <div class="mb-4">
            <div class="d-flex align-items-center gap-2 mb-3">
                <Link href="/portal/food" class="text-decoration-none" style="color: #2E7D5B;">
                    <i class="ri-arrow-left-line"></i> Food Services
                </Link>
                <span class="text-muted">/</span>
                <span class="text-dark fw-semibold">Search</span>
            </div>
            <h4 class="fw-bold">Find Food</h4>
        </div>

        <!-- Filters -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Search</label>
                        <input
                            v-model="searchQuery"
                            type="text"
                            class="form-control"
                            placeholder="Search items..."
                            @keyup.enter="applyFilters"
                        />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Category</label>
                        <select v-model="selectedCategory" class="form-select" @change="applyFilters">
                            <option value="">All Categories</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Sort By</label>
                        <select v-model="selectedSort" class="form-select" @change="applyFilters">
                            <option value="relevance">Relevance</option>
                            <option value="price_low">Price: Low to High</option>
                            <option value="price_high">Price: High to Low</option>
                            <option value="rating">Top Rated</option>
                            <option value="popular">Most Popular</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn w-100 fw-semibold" style="background-color: #2E7D5B; color: white;" @click="applyFilters">
                            <i class="ri-search-line me-1"></i> Search
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Results Count -->
        <p class="text-muted mb-3">
            Showing {{ items.data.length }} of {{ items.total }} items
            <span v-if="filters.q"> for "<strong>{{ filters.q }}</strong>"</span>
        </p>

        <!-- Items Grid -->
        <div v-if="items.data.length" class="row g-3 mb-4">
            <div v-for="item in items.data" :key="item.id" class="col-sm-6 col-lg-3">
                <Link :href="`/portal/food/item/${item.id}`" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 hover-shadow">
                        <div class="position-relative">
                            <div class="d-flex align-items-center justify-content-center" style="height: 160px; background: #f0f7f3;">
                                <span v-if="item.image_url" style="width: 100%; height: 160px; object-fit: cover;" :style="{ backgroundImage: `url(${item.image_url})`, backgroundSize: 'cover', backgroundPosition: 'center', display: 'block' }"></span>
                                <span v-else style="font-size: 40px;">🍽️</span>
                            </div>
                            <span v-if="item.has_discount" class="badge position-absolute top-0 end-0 m-2" style="background-color: #2E7D5B;">
                                {{ Math.round((1 - item.discount_price / item.price) * 100) }}% OFF
                            </span>
                        </div>
                        <div class="card-body p-3">
                            <h6 class="fw-bold text-dark mb-1">{{ item.name }}</h6>
                            <small class="text-muted d-block mb-2" v-if="item.provider">{{ item.provider.business_name }}</small>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span v-if="item.category" class="badge" style="background: rgba(46,125,91,0.1); color: #2E7D5B; font-size: 0.7rem;">
                                    {{ item.category.name }}
                                </span>
                                <span v-if="item.is_jain" class="badge bg-warning text-dark" style="font-size: 0.7rem;">Jain</span>
                                <span v-if="item.is_vegan" class="badge bg-success" style="font-size: 0.7rem;">Vegan</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="fw-bold" style="color: #2E7D5B; font-size: 1.1rem;">₹{{ formatPrice(item) }}</span>
                                    <span v-if="item.has_discount" class="text-muted text-decoration-line-through small ms-1">₹{{ item.price }}</span>
                                </div>
                                <div class="d-flex align-items-center gap-1" v-if="item.avg_rating > 0">
                                    <i class="ri-star-fill" style="color: #f59e0b; font-size: 12px;"></i>
                                    <small class="text-muted">{{ item.avg_rating }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </Link>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-5">
            <div style="font-size: 48px;" class="mb-3">🔍</div>
            <h5 class="text-muted">No items found</h5>
            <p class="text-muted">Try adjusting your search or filters</p>
            <Link href="/portal/food" class="btn" style="background-color: #2E7D5B; color: white;">Browse All Food</Link>
        </div>

        <!-- Pagination -->
        <div v-if="items.last_page > 1" class="d-flex justify-content-center">
            <nav>
                <ul class="pagination">
                    <li v-for="page in items.last_page" :key="page" class="page-item" :class="{ active: page === items.current_page }">
                        <Link :href="`/portal/food/search?...${$route.query}&page=${page}`" class="page-link">{{ page }}</Link>
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
