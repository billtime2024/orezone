<script>
import { Link, Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/admin.vue';

export default {
    components: { Link, Head, AdminLayout },
    layout: AdminLayout,
    props: {
        providers: Object,
        filters: Object,
    },
    data() {
        return {
            search: this.filters?.search || '',
            providerType: this.filters?.provider_type || '',
            statusFilter: this.filters?.status || '',
            isActive: this.filters?.is_active ?? '',
        };
    },
    methods: {
        applyFilters() {
            this.$inertia.get('/admin/food-providers', {
                search: this.search || undefined,
                provider_type: this.providerType || undefined,
                status: this.statusFilter || undefined,
                is_active: this.isActive !== '' ? this.isActive : undefined,
            }, { preserveState: true });
        },
        clearFilters() {
            this.search = '';
            this.providerType = '';
            this.statusFilter = '';
            this.isActive = '';
            this.$inertia.get('/admin/food-providers');
        },
        statusBadgeClass(status) {
            switch (status) {
                case 'approved': return 'bg-success-subtle text-success';
                case 'pending': return 'bg-warning-subtle text-warning';
                case 'rejected': return 'bg-danger-subtle text-danger';
                default: return 'bg-secondary-subtle text-secondary';
            }
        },
        formatDate(dateStr) {
            if (!dateStr) return '—';
            return new Date(dateStr).toLocaleDateString('en-IN', {
                day: '2-digit', month: 'short', year: 'numeric',
            });
        },
        formatCurrency(amount) {
            if (!amount) return '₹0';
            return '₹' + Number(amount).toLocaleString('en-IN');
        },
        paginationUrl(page) {
            const params = new URLSearchParams();
            if (this.search) params.set('search', this.search);
            if (this.providerType) params.set('provider_type', this.providerType);
            if (this.statusFilter) params.set('status', this.statusFilter);
            if (this.isActive !== '') params.set('is_active', this.isActive);
            params.set('page', page);
            return '/admin/food-providers?' + params.toString();
        },
    },
};
</script>

<template>
    <Head title="Food Providers" />

    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-1 fw-bold">Food Providers</h4>
                <p class="text-muted mb-0">Manage all food service providers on the platform</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-primary fs-6">
                    {{ providers.total }} Total Providers
                </span>
                <Link href="/admin/food-providers/create" class="btn btn-primary">
                    <i class="ri-add-line me-1"></i> Create Provider
                </Link>
            </div>
        </div>

        <!-- Filters -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Search</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="ri-search-line text-muted"></i>
                            </span>
                            <input
                                v-model="search"
                                type="text"
                                class="form-control"
                                placeholder="Search by name, business..."
                                @keyup.enter="applyFilters"
                            />
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Provider Type</label>
                        <select v-model="providerType" class="form-select" @change="applyFilters">
                            <option value="">All Types</option>
                            <option value="homemade">Homemade</option>
                            <option value="catering">Catering</option>
                            <option value="hotel">Hotel</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Status</label>
                        <select v-model="statusFilter" class="form-select" @change="applyFilters">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Active</label>
                        <select v-model="isActive" class="form-select" @change="applyFilters">
                            <option value="">All</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button class="btn btn-primary" @click="applyFilters">
                            <i class="ri-search-line me-1"></i> Search
                        </button>
                        <button class="btn btn-outline-secondary" @click="clearFilters">
                            <i class="ri-close-line me-1"></i> Clear
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Providers Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" style="width: 60px;">ID</th>
                                <th>Business Name</th>
                                <th>Type</th>
                                <th>User</th>
                                <th>Rating</th>
                                <th>Orders</th>
                                <th>Revenue</th>
                                <th>Status</th>
                                <th>Verified</th>
                                <th class="text-end pe-3" style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="(providers?.data || []).length === 0">
                                <td colspan="10" class="text-center py-5 text-muted">
                                    <i class="ri-restaurant-line fs-1 d-block mb-2"></i>
                                    No food providers found matching your criteria.
                                </td>
                            </tr>
                            <tr v-for="provider in (providers?.data || [])" :key="provider.id">
                                <td class="ps-3">
                                    <span class="text-muted">#{{ provider.id }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-sm rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center">
                                            <img
                                                v-if="provider.logo"
                                                :src="provider.logo"
                                                class="rounded-circle"
                                                style="width: 36px; height: 36px; object-fit: cover;"
                                            />
                                            <span v-else class="text-success fw-semibold small">
                                                {{ provider.business_name ? provider.business_name.charAt(0).toUpperCase() : '?' }}
                                            </span>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ provider.business_name }}</div>
                                            <small class="text-muted" v-if="provider.is_featured">
                                                <i class="ri-star-fill text-warning"></i> Featured
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark text-capitalize">
                                        {{ provider.provider_type || '—' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted">{{ provider.user?.name || '—' }}</span>
                                </td>
                                <td>
                                    <span class="text-warning" v-if="provider.avg_rating">
                                        ★ {{ Number(provider.avg_rating).toFixed(1) }}
                                    </span>
                                    <span v-else class="text-muted">—</span>
                                </td>
                                <td>
                                    <span class="fw-semibold">{{ provider.total_orders || 0 }}</span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-success">{{ formatCurrency(provider.total_revenue) }}</span>
                                </td>
                                <td>
                                    <span class="badge rounded-pill" :class="statusBadgeClass(provider.status)">
                                        {{ provider.status || 'unknown' }}
                                    </span>
                                </td>
                                <td>
                                    <span v-if="provider.is_verified" class="badge bg-success-subtle text-success">
                                        <i class="ri-check-line me-1"></i> Yes
                                    </span>
                                    <span v-else class="badge bg-secondary-subtle text-secondary">
                                        <i class="ri-close-line me-1"></i> No
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                            <i class="ri-more-2-fill"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <Link :href="`/admin/food-providers/${provider.id}`" class="dropdown-item">
                                                    <i class="ri-eye-line me-2"></i> View
                                                </Link>
                                            </li>
                                            <li>
                                                <Link :href="`/admin/food-providers/${provider.id}/edit`" class="dropdown-item">
                                                    <i class="ri-edit-line me-2"></i> Edit
                                                </Link>
                                            </li>
                                            <li>
                                                <Link :href="`/admin/food-providers/${provider.id}/login-as`" method="post" as="button" class="dropdown-item text-primary">
                                                    <i class="ri-login-box-line me-2"></i> Login as Provider
                                                </Link>
                                            </li>
                                            <li v-if="provider.status === 'pending'">
                                                <Link :href="`/admin/food-providers/${provider.id}/verify`" method="patch" as="button" class="dropdown-item text-success">
                                                    <i class="ri-check-double-line me-2"></i> Approve
                                                </Link>
                                            </li>
                                            <li>
                                                <Link :href="`/admin/food-providers/${provider.id}/featured`" method="patch" as="button" class="dropdown-item">
                                                    <i class="ri-star-line me-2"></i> {{ provider.is_featured ? 'Unfeature' : 'Feature' }}
                                                </Link>
                                            </li>
                                            <li>
                                                <Link :href="`/admin/food-providers/${provider.id}/active`" method="patch" as="button" class="dropdown-item">
                                                    <i class="ri-toggle-line me-2"></i> {{ provider.is_active ? 'Deactivate' : 'Activate' }}
                                                </Link>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="d-flex align-items-center justify-content-between mt-4" v-if="providers.last_page > 1">
            <div class="text-muted small">
                Showing {{ providers.from }} to {{ providers.to }} of {{ providers.total }} providers
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item" :class="{ disabled: !providers.prev_page_url }">
                        <Link
                            :href="providers.prev_page_url || '#'"
                            class="page-link"
                            preserve-state
                        >
                            <i class="ri-arrow-left-s-line"></i>
                        </Link>
                    </li>
                    <li
                        v-for="page in providers.last_page"
                        :key="page"
                        class="page-item"
                        :class="{ active: page === providers.current_page }"
                    >
                        <Link
                            :href="paginationUrl(page)"
                            class="page-link"
                            preserve-state
                        >
                            {{ page }}
                        </Link>
                    </li>
                    <li class="page-item" :class="{ disabled: !providers.next_page_url }">
                        <Link
                            :href="providers.next_page_url || '#'"
                            class="page-link"
                            preserve-state
                        >
                            <i class="ri-arrow-right-s-line"></i>
                        </Link>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</template>

<style scoped>
.avatar-sm {
    width: 36px;
    height: 36px;
    font-size: 14px;
}
</style>
