<script>
import { Link, Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/admin.vue';

export default {
    components: { Link, Head, AdminLayout },
    layout: AdminLayout,
    props: {
        users: Object,
    },
    data() {
        return {
            search: this.$page.props.users?.query?.search || '',
            statusFilter: this.$page.props.users?.query?.status || '',
        };
    },
    methods: {
        applyFilters() {
            this.$inertia.get('/admin/users', {
                search: this.search || undefined,
                status: this.statusFilter || undefined,
            }, { preserveState: true });
        },
        clearFilters() {
            this.search = '';
            this.statusFilter = '';
            this.$inertia.get('/admin/users');
        },
        statusBadgeClass(status) {
            switch (status) {
                case 'active': return 'bg-success-subtle text-success';
                case 'suspended': return 'bg-danger-subtle text-danger';
                case 'deactivated': return 'bg-secondary-subtle text-secondary';
                default: return 'bg-warning-subtle text-warning';
            }
        },
        formatDate(dateStr) {
            if (!dateStr) return '—';
            return new Date(dateStr).toLocaleDateString('en-IN', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
            });
        },
    },
};
</script>

<template>
    <Head title="Manage Users" />

    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-1 fw-bold">Users Management</h4>
                <p class="text-muted mb-0">Manage all registered users and their accounts</p>
            </div>
            <span class="badge bg-primary fs-6">
                {{ users.total }} Total Users
            </span>
        </div>

        <!-- Filters -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label small text-muted">Search</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="ri-search-line text-muted"></i>
                            </span>
                            <input
                                v-model="search"
                                type="text"
                                class="form-control"
                                placeholder="Search by name, email, or phone..."
                                @keyup.enter="applyFilters"
                            />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Status</label>
                        <select v-model="statusFilter" class="form-select" @change="applyFilters">
                            <option value="">All Statuses</option>
                            <option value="active">Active</option>
                            <option value="suspended">Suspended</option>
                            <option value="deactivated">Deactivated</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex gap-2">
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

        <!-- Users Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" style="width: 60px;">ID</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Verified</th>
                                <th>Joined</th>
                                <th class="text-end pe-3" style="width: 100px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="users.data.length === 0">
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="ri-user-search-line fs-1 d-block mb-2"></i>
                                    No users found matching your criteria.
                                </td>
                            </tr>
                            <tr v-for="user in users.data" :key="user.id">
                                <td class="ps-3">
                                    <span class="text-muted">#{{ user.id }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-sm rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center">
                                            <span class="text-primary fw-semibold small">
                                                {{ user.name ? user.name.charAt(0).toUpperCase() : '?' }}
                                            </span>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ user.name || 'Unnamed' }}</div>
                                            <small class="text-muted" v-if="user.profile && user.profile.city">
                                                {{ user.profile.city }}
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-muted">{{ user.phone || '—' }}</span>
                                </td>
                                <td>
                                    <span class="text-muted">{{ user.email || '—' }}</span>
                                </td>
                                <td>
                                    <span class="badge rounded-pill" :class="statusBadgeClass(user.status)">
                                        {{ user.status || 'unknown' }}
                                    </span>
                                </td>
                                <td>
                                    <span v-if="user.phone_verified_at" class="badge bg-success-subtle text-success">
                                        <i class="ri-check-line me-1"></i> Verified
                                    </span>
                                    <span v-else class="badge bg-secondary-subtle text-secondary">
                                        <i class="ri-close-line me-1"></i> Unverified
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted small">{{ formatDate(user.created_at) }}</span>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                            <i class="ri-more-2-fill"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="#">
                                                    <i class="ri-eye-line me-2"></i> View Profile
                                                </a>
                                            </li>
                                            <li v-if="user.status !== 'suspended'">
                                                <a class="dropdown-item text-danger" href="#">
                                                    <i class="ri-forbid-line me-2"></i> Suspend
                                                </a>
                                            </li>
                                            <li v-else>
                                                <a class="dropdown-item text-success" href="#">
                                                    <i class="ri-check-double-line me-2"></i> Reactivate
                                                </a>
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
        <div class="d-flex align-items-center justify-content-between mt-4" v-if="users.last_page > 1">
            <div class="text-muted small">
                Showing {{ users.from }} to {{ users.to }} of {{ users.total }} users
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item" :class="{ disabled: !users.prev_page_url }">
                        <Link
                            :href="users.prev_page_url || '#'"
                            class="page-link"
                            preserve-state
                        >
                            <i class="ri-arrow-left-s-line"></i>
                        </Link>
                    </li>
                    <li
                        v-for="page in users.last_page"
                        :key="page"
                        class="page-item"
                        :class="{ active: page === users.current_page }"
                    >
                        <Link
                            :href="'/admin/users?page=' + page + (search ? '&search=' + search : '') + (statusFilter ? '&status=' + statusFilter : '')"
                            class="page-link"
                            preserve-state
                        >
                            {{ page }}
                        </Link>
                    </li>
                    <li class="page-item" :class="{ disabled: !users.next_page_url }">
                        <Link
                            :href="users.next_page_url || '#'"
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
