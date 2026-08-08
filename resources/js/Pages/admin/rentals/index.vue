<script>
import { Link, Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/admin.vue';

export default {
    components: { Link, Head, AdminLayout },
    layout: AdminLayout,
    props: {
        listings: Object,
    },
    data() {
        return {
            searchFilter: this.$page.props.listings?.query?.search || '',
            typeFilter: this.$page.props.listings?.query?.rental_type || '',
            statusFilter: this.$page.props.listings?.query?.status || '',
        };
    },
    methods: {
        applyFilters() {
            this.$inertia.get('/admin/rentals', {
                search: this.searchFilter || undefined,
                rental_type: this.typeFilter || undefined,
                status: this.statusFilter || undefined,
            }, { preserveState: true });
        },
        clearFilters() {
            this.searchFilter = '';
            this.typeFilter = '';
            this.statusFilter = '';
            this.$inertia.get('/admin/rentals');
        },
        typeLabel(type) {
            const labels = { house: '🏠 House', car: '🚗 Car', commercial: '🏢 Commercial', room: '🛏️ Room' };
            return labels[type] || type;
        },
        statusBadgeClass(status) {
            switch (status) {
                case 'active': return 'bg-success-subtle text-success';
                case 'draft': return 'bg-secondary-subtle text-secondary';
                case 'paused': return 'bg-warning-subtle text-warning';
                case 'closed': return 'bg-danger-subtle text-danger';
                default: return 'bg-secondary-subtle text-secondary';
            }
        },
        formatCurrency(amount) {
            if (!amount) return '—';
            return '₹' + Number(amount).toLocaleString('en-IN');
        },
        formatDate(dateStr) {
            if (!dateStr) return '—';
            return new Date(dateStr).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
        },
    },
};
</script>

<template>
    <Head title="Manage Rentals" />

    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-1 fw-bold">Rental Listings</h4>
                <p class="text-muted mb-0">View and manage all rental listings on the platform</p>
            </div>
            <span class="badge bg-primary fs-6">
                {{ listings.total }} Total Listings
            </span>
        </div>

        <!-- Filters -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Search</label>
                        <input
                            v-model="searchFilter"
                            type="text"
                            class="form-control"
                            placeholder="Title, city, address..."
                            @keyup.enter="applyFilters"
                        />
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Type</label>
                        <select v-model="typeFilter" class="form-select">
                            <option value="">All Types</option>
                            <option value="house">🏠 House</option>
                            <option value="car">🚗 Car</option>
                            <option value="commercial">🏢 Commercial</option>
                            <option value="room">🛏️ Room</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Status</label>
                        <select v-model="statusFilter" class="form-select">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="draft">Draft</option>
                            <option value="paused">Paused</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary me-2" @click="applyFilters">
                            <i class="ri-search-line me-1"></i> Filter
                        </button>
                        <button class="btn btn-outline-secondary" @click="clearFilters">Clear</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Listings Table -->
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="fw-semibold">Listing</th>
                            <th class="fw-semibold">Type</th>
                            <th class="fw-semibold">Owner</th>
                            <th class="fw-semibold">City</th>
                            <th class="fw-semibold">Price</th>
                            <th class="fw-semibold">Status</th>
                            <th class="fw-semibold">Bookings</th>
                            <th class="fw-semibold">Rating</th>
                            <th class="fw-semibold">Created</th>
                            <th class="fw-semibold text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="listing in listings.data" :key="listing.id">
                            <td>
                                <div class="fw-semibold">{{ listing.title }}</div>
                                <small class="text-muted">{{ listing.address_line1 }}</small>
                            </td>
                            <td><span class="badge bg-light text-dark">{{ typeLabel(listing.rental_type) }}</span></td>
                            <td>
                                <div>{{ listing.owner?.name }}</div>
                                <small class="text-muted">{{ listing.owner?.email }}</small>
                            </td>
                            <td>{{ listing.city }}</td>
                            <td class="fw-semibold">{{ formatCurrency(listing.price_per_unit) }}<small class="text-muted">/{{ listing.price_unit }}</small></td>
                            <td><span class="badge" :class="statusBadgeClass(listing.status)">{{ listing.status }}</span></td>
                            <td class="text-center">{{ listing.total_bookings }}</td>
                            <td>
                                <span v-if="listing.avg_rating > 0" class="text-warning">★ {{ listing.avg_rating }}</span>
                                <span v-else class="text-muted">—</span>
                            </td>
                            <td><small>{{ formatDate(listing.created_at) }}</small></td>
                            <td class="text-end">
                                <Link :href="`/admin/rentals/${listing.id}`" class="btn btn-sm btn-outline-primary">
                                    <i class="ri-eye-line"></i>
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="listings.data.length === 0">
                            <td colspan="10" class="text-center text-muted py-4">No listings found</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center" v-if="listings.last_page > 1">
                <small class="text-muted">
                    Showing {{ listings.from }} to {{ listings.to }} of {{ listings.total }} listings
                </small>
                <div class="d-flex gap-1">
                    <Link
                        v-for="page in listings.last_page"
                        :key="page"
                        :href="`/admin/rentals?page=${page}`"
                        class="btn btn-sm"
                        :class="page === listings.current_page ? 'btn-primary' : 'btn-outline-secondary'"
                        preserve-state
                    >
                        {{ page }}
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
