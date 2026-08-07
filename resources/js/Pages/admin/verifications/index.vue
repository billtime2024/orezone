<script>
import { Link, Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/admin.vue';

export default {
    components: { Link, Head, AdminLayout },
    layout: AdminLayout,
    props: {
        verificationRequests: Object,
    },
    data() {
        return {
            typeFilter: this.$page.props.verificationRequests?.query?.type || '',
            statusFilter: this.$page.props.verificationRequests?.query?.status || '',
            expandedRow: null,
        };
    },
    methods: {
        applyFilters() {
            this.$inertia.get('/admin/verifications', {
                type: this.typeFilter || undefined,
                status: this.statusFilter || undefined,
            }, { preserveState: true });
        },
        clearFilters() {
            this.typeFilter = '';
            this.statusFilter = '';
            this.$inertia.get('/admin/verifications');
        },
        toggleExpand(id) {
            this.expandedRow = this.expandedRow === id ? null : id;
        },
        typeBadgeClass(type) {
            switch (type) {
                case 'profile': return 'bg-info-subtle text-info';
                case 'host_identity': return 'bg-purple-subtle text-purple';
                case 'vehicle': return 'bg-warning-subtle text-warning';
                default: return 'bg-secondary-subtle text-secondary';
            }
        },
        statusBadgeClass(status) {
            switch (status) {
                case 'pending': return 'bg-warning-subtle text-warning';
                case 'under_review': return 'bg-info-subtle text-info';
                case 'approved': return 'bg-success-subtle text-success';
                case 'rejected': return 'bg-danger-subtle text-danger';
                default: return 'bg-secondary-subtle text-secondary';
            }
        },
        typeLabel(type) {
            switch (type) {
                case 'profile': return 'Profile';
                case 'host_identity': return 'Host Identity';
                case 'vehicle': return 'Vehicle';
                default: return type;
            }
        },
        statusLabel(status) {
            switch (status) {
                case 'pending': return 'Pending';
                case 'under_review': return 'Under Review';
                case 'approved': return 'Approved';
                case 'rejected': return 'Rejected';
                default: return status;
            }
        },
        formatDate(dateStr) {
            if (!dateStr) return '—';
            return new Date(dateStr).toLocaleDateString('en-IN', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
            });
        },
    },
};
</script>

<template>
    <Head title="Verification Requests" />

    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-1 fw-bold">Verification Requests</h4>
                <p class="text-muted mb-0">Review and manage user verification submissions</p>
            </div>
            <span class="badge bg-primary fs-6">
                {{ verificationRequests.total }} Total Requests
            </span>
        </div>

        <!-- Filters -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Type</label>
                        <select v-model="typeFilter" class="form-select" @change="applyFilters">
                            <option value="">All Types</option>
                            <option value="profile">Profile</option>
                            <option value="host_identity">Host Identity</option>
                            <option value="vehicle">Vehicle</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Status</label>
                        <select v-model="statusFilter" class="form-select" @change="applyFilters">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="under_review">Under Review</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-6 d-flex gap-2">
                        <button class="btn btn-primary" @click="applyFilters">
                            <i class="ri-filter-3-line me-1"></i> Filter
                        </button>
                        <button class="btn btn-outline-secondary" @click="clearFilters">
                            <i class="ri-close-line me-1"></i> Clear
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Verification Requests Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" style="width: 60px;">ID</th>
                                <th>User</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Documents</th>
                                <th>Submitted</th>
                                <th class="text-end pe-3" style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="verificationRequests.data.length === 0">
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="ri-file-list-3-line fs-1 d-block mb-2"></i>
                                    No verification requests found.
                                </td>
                            </tr>
                            <template v-for="req in verificationRequests.data" :key="req.id">
                                <tr
                                    @click="toggleExpand(req.id)"
                                    class="cursor-pointer"
                                >
                                    <td class="ps-3">
                                        <span class="text-muted">#{{ req.id }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-sm rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center">
                                                <span class="text-primary fw-semibold small">
                                                    {{ req.user?.name?.charAt(0)?.toUpperCase() || '?' }}
                                                </span>
                                            </div>
                                            <div>
                                                <div class="fw-semibold">{{ req.user?.name || 'Unknown' }}</div>
                                                <small class="text-muted">{{ req.user?.phone || '' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill" :class="typeBadgeClass(req.type)">
                                            {{ typeLabel(req.type) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill" :class="statusBadgeClass(req.status)">
                                            {{ statusLabel(req.status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            <i class="ri-attachment-2 me-1"></i>
                                            {{ req.documents?.length || 0 }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-muted small">{{ formatDate(req.created_at) }}</span>
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="d-flex gap-1 justify-content-end">
                                            <button
                                                v-if="req.status === 'pending' || req.status === 'under_review'"
                                                class="btn btn-sm btn-success-subtle text-success"
                                                title="Approve"
                                            >
                                                <i class="ri-check-line"></i>
                                            </button>
                                            <button
                                                v-if="req.status === 'pending' || req.status === 'under_review'"
                                                class="btn btn-sm btn-danger-subtle text-danger"
                                                title="Reject"
                                            >
                                                <i class="ri-close-line"></i>
                                            </button>
                                            <button
                                                class="btn btn-sm btn-light"
                                                @click.stop="toggleExpand(req.id)"
                                                title="Toggle documents"
                                            >
                                                <i :class="expandedRow === req.id ? 'ri-arrow-up-s-line' : 'ri-arrow-down-s-line'"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Expanded Document List -->
                                <tr v-if="expandedRow === req.id">
                                    <td colspan="7" class="bg-light bg-opacity-50">
                                        <div class="p-3">
                                            <h6 class="fw-semibold mb-3">
                                                <i class="ri-attachment-2 me-1"></i> Documents ({{ req.documents?.length || 0 }})
                                            </h6>
                                            <div v-if="req.documents && req.documents.length" class="row g-2">
                                                <div v-for="doc in req.documents" :key="doc.id" class="col-md-3">
                                                    <div class="bg-white rounded border p-2 text-center small">
                                                        <i class="ri-file-image-line fs-4 text-muted d-block mb-1"></i>
                                                        <div class="text-truncate">{{ doc.original_name || doc.type }}</div>
                                                        <small class="text-muted">{{ doc.type }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <p v-else class="text-muted mb-0 small">No documents uploaded.</p>
                                            <div v-if="req.notes || req.rejection_reason" class="mt-3">
                                                <small class="text-muted">
                                                    <strong>Notes:</strong> {{ req.notes || '—' }}
                                                </small>
                                                <br v-if="req.rejection_reason" />
                                                <small v-if="req.rejection_reason" class="text-danger">
                                                    <strong>Rejection Reason:</strong> {{ req.rejection_reason }}
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="d-flex align-items-center justify-content-between mt-4" v-if="verificationRequests.last_page > 1">
            <div class="text-muted small">
                Showing {{ verificationRequests.from }} to {{ verificationRequests.to }} of {{ verificationRequests.total }} requests
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item" :class="{ disabled: !verificationRequests.prev_page_url }">
                        <Link
                            :href="verificationRequests.prev_page_url || '#'"
                            class="page-link"
                            preserve-state
                        >
                            <i class="ri-arrow-left-s-line"></i>
                        </Link>
                    </li>
                    <li
                        v-for="page in verificationRequests.last_page"
                        :key="page"
                        class="page-item"
                        :class="{ active: page === verificationRequests.current_page }"
                    >
                        <Link
                            :href="'/admin/verifications?page=' + page + (typeFilter ? '&type=' + typeFilter : '') + (statusFilter ? '&status=' + statusFilter : '')"
                            class="page-link"
                            preserve-state
                        >
                            {{ page }}
                        </Link>
                    </li>
                    <li class="page-item" :class="{ disabled: !verificationRequests.next_page_url }">
                        <Link
                            :href="verificationRequests.next_page_url || '#'"
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
.cursor-pointer {
    cursor: pointer;
}
.avatar-sm {
    width: 36px;
    height: 36px;
    font-size: 14px;
}
.bg-purple-subtle {
    background-color: #f3e8ff !important;
}
.text-purple {
    color: #7c3aed !important;
}
</style>
