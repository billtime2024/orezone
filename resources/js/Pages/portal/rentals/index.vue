<script>
import { Link, Head } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/portal-layout.vue';

export default {
    components: { Link, Head, PortalLayout },
    layout: PortalLayout,
    props: {
        listings: Object,
        filters: Object,
    },
    data() {
        return {
            searchFilter: this.filters?.search || '',
            typeFilter: this.filters?.rental_type || '',
        };
    },
    methods: {
        applyFilters() {
            this.$inertia.get('/portal/rentals', {
                search: this.searchFilter || undefined,
                rental_type: this.typeFilter || undefined,
            }, { preserveState: true });
        },
        clearFilters() {
            this.searchFilter = '';
            this.typeFilter = '';
            this.$inertia.get('/portal/rentals');
        },
        typeLabel(type) {
            const labels = { house: '🏠 House', car: '🚗 Car', commercial: '🏢 Commercial', room: '🛏️ Room' };
            return labels[type] || type;
        },
        formatCurrency(amount) {
            if (!amount) return '—';
            return '₹' + Number(amount).toLocaleString('en-IN');
        },
    },
};
</script>

<template>
    <Head title="Browse Rentals" />

    <div class="container-fluid">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-1 fw-bold">Browse Rentals</h4>
                <p class="text-muted mb-0">Find houses, cars, commercial spaces, and rooms</p>
            </div>
            <Link href="/portal/rentals/my" class="btn btn-outline-primary btn-sm">
                <i class="ri-home-4-line me-1"></i> My Listings
            </Link>
        </div>

        <!-- Filters -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <input v-model="searchFilter" type="text" class="form-control" placeholder="Search by city, title..." @keyup.enter="applyFilters" />
                    </div>
                    <div class="col-md-3">
                        <select v-model="typeFilter" class="form-select">
                            <option value="">All Types</option>
                            <option value="house">🏠 House</option>
                            <option value="car">🚗 Car</option>
                            <option value="commercial">🏢 Commercial</option>
                            <option value="room">🛏️ Room</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary me-2" @click="applyFilters">Search</button>
                        <button class="btn btn-outline-secondary" @click="clearFilters">Clear</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Listings Grid -->
        <div class="row g-4">
            <div v-for="listing in listings.data" :key="listing.id" class="col-md-6 col-lg-4">
                <Link :href="`/portal/rentals/${listing.id}`" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100">
                        <div v-if="listing.photos?.length" class="card-img-top" style="height: 180px; overflow: hidden;">
                            <img :src="listing.photos[0]" class="w-100 h-100" style="object-fit: cover;" />
                        </div>
                        <div v-else class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 180px;">
                            <span style="font-size: 48px;">{{ listing.rental_type === 'house' ? '🏠' : listing.rental_type === 'car' ? '🚗' : listing.rental_type === 'commercial' ? '🏢' : '🛏️' }}</span>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-light text-dark">{{ typeLabel(listing.rental_type) }}</span>
                                <span v-if="listing.avg_rating > 0" class="text-warning small">★ {{ listing.avg_rating }}</span>
                            </div>
                            <h6 class="card-title fw-bold text-dark">{{ listing.title }}</h6>
                            <p class="text-muted small mb-2"><i class="ri-map-pin-line"></i> {{ listing.city }}, {{ listing.state }}</p>
                            <div class="fw-bold text-success fs-5">{{ formatCurrency(listing.price_per_unit) }}<small class="text-muted fw-normal">/{{ listing.price_unit }}</small></div>
                        </div>
                    </div>
                </Link>
            </div>
        </div>

        <div v-if="listings.data?.length === 0" class="text-center py-5">
            <div class="text-muted">No listings found. Try different filters.</div>
        </div>

        <!-- Pagination -->
        <div v-if="listings.last_page > 1" class="d-flex justify-content-center mt-4">
            <Link v-if="listings.current_page > 1" :href="`/portal/rentals?page=${listings.current_page - 1}`" class="btn btn-outline-secondary btn-sm me-2">← Previous</Link>
            <span class="btn btn-sm disabled">Page {{ listings.current_page }} of {{ listings.last_page }}</span>
            <Link v-if="listings.current_page < listings.last_page" :href="`/portal/rentals?page=${listings.current_page + 1}`" class="btn btn-outline-secondary btn-sm ms-2">Next →</Link>
        </div>
    </div>
</template>
