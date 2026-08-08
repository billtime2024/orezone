<script>
import { Link, Head } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/admin.vue';

export default {
    components: { Link, Head, AdminLayout },
    layout: AdminLayout,
    props: {
        verificationRequest: Object,
    },
    data() {
        return {
            showRejectReason: false,
            approveForm: useForm({}),
            rejectForm: useForm({ reason: '' }),
        };
    },
    methods: {
        approve() {
            if (confirm('Are you sure you want to approve this verification?')) {
                this.approveForm.post(`/admin/verifications/${this.verificationRequest.id}/approve`);
            }
        },
        reject() {
            if (!this.rejectForm.reason) {
                alert('Please provide a reason for rejection.');
                return;
            }
            if (confirm('Are you sure you want to reject this verification?')) {
                this.rejectForm.post(`/admin/verifications/${this.verificationRequest.id}/reject`);
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
        statusLabel(status) {
            const labels = { pending: 'Pending', under_review: 'Under Review', approved: 'Approved', rejected: 'Rejected' };
            return labels[status] || status;
        },
        docStatusBadgeClass(status) {
            switch (status) {
                case 'pending': return 'bg-warning-subtle text-warning';
                case 'approved': return 'bg-success-subtle text-success';
                case 'rejected': return 'bg-danger-subtle text-danger';
                default: return 'bg-secondary-subtle text-secondary';
            }
        },
        formatDate(dateStr) {
            if (!dateStr) return '—';
            return new Date(dateStr).toLocaleDateString('en-IN', {
                day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit',
            });
        },
    },
};
</script>

<template>
    <Head :title="`Verification #${verificationRequest.id}`" />

    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Verification Request #{{ verificationRequest.id }}</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><Link href="/admin/verifications">Verifications</Link></li>
                        <li class="breadcrumb-item active">Request #{{ verificationRequest.id }}</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                <button v-if="verificationRequest.status === 'pending' || verificationRequest.status === 'under_review'"
                    class="btn btn-success" @click="approve">
                    <i class="ri-check-line me-1"></i> Approve
                </button>
                <button v-if="verificationRequest.status === 'pending' || verificationRequest.status === 'under_review'"
                    class="btn btn-danger" @click="showRejectReason = !showRejectReason">
                    <i class="ri-close-line me-1"></i> Reject
                </button>
            </div>
        </div>

        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-8">
                <!-- Request Details -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Request Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Type</label>
                                <div class="fw-semibold">{{ verificationRequest.type?.replace('_', ' ').toUpperCase() }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Status</label>
                                <div>
                                    <span class="badge" :class="statusBadgeClass(verificationRequest.status)">
                                        {{ statusLabel(verificationRequest.status) }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Submitted</label>
                                <div>{{ formatDate(verificationRequest.created_at) }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Last Updated</label>
                                <div>{{ formatDate(verificationRequest.updated_at) }}</div>
                            </div>
                            <div v-if="verificationRequest.reviewed_at" class="col-md-6">
                                <label class="form-label text-muted small">Reviewed At</label>
                                <div>{{ formatDate(verificationRequest.reviewed_at) }}</div>
                            </div>
                            <div v-if="verificationRequest.reviewer" class="col-md-6">
                                <label class="form-label text-muted small">Reviewed By</label>
                                <div>{{ verificationRequest.reviewer.name }}</div>
                            </div>
                            <div v-if="verificationRequest.rejection_reason" class="col-12">
                                <label class="form-label text-muted small">Rejection Reason</label>
                                <div class="alert alert-danger py-2 mb-0">{{ verificationRequest.rejection_reason }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Documents -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Documents ({{ verificationRequest.documents?.length || 0 }})</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Document Type</th>
                                        <th>Status</th>
                                        <th>File</th>
                                        <th>Submitted</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="doc in verificationRequest.documents" :key="doc.id">
                                        <td>{{ doc.document_type?.replace('_', ' ').toUpperCase() }}</td>
                                        <td>
                                            <span class="badge" :class="docStatusBadgeClass(doc.status)">
                                                {{ doc.status }}
                                            </span>
                                        </td>
                                        <td>
                                            <a v-if="doc.file_path" :href="`/storage/${doc.file_path}`" target="_blank"
                                                class="text-decoration-none">
                                                <i class="ri-file-line me-1"></i> View Document
                                            </a>
                                            <span v-else class="text-muted">No file</span>
                                        </td>
                                        <td>{{ formatDate(doc.created_at) }}</td>
                                    </tr>
                                    <tr v-if="!verificationRequest.documents?.length">
                                        <td colspan="4" class="text-center text-muted py-4">No documents uploaded</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Reject Reason Form -->
                <div v-if="showRejectReason" class="card mb-4 border-danger">
                    <div class="card-header bg-danger-subtle">
                        <h5 class="card-title mb-0 text-danger">Rejection Reason</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Reason for rejection <span class="text-danger">*</span></label>
                            <textarea v-model="rejectForm.reason" class="form-control" rows="3"
                                placeholder="Enter the reason for rejecting this verification..."></textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-danger" @click="reject" :disabled="rejectForm.processing">
                                <i class="ri-close-line me-1"></i> Confirm Rejection
                            </button>
                            <button class="btn btn-light" @click="showRejectReason = false">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-lg-4">
                <!-- User Info -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">User Info</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="avatar-lg mx-auto mb-2 bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center">
                                <i class="ri-user-line text-primary fs-2"></i>
                            </div>
                            <h6 class="mb-0">{{ verificationRequest.user?.name }}</h6>
                            <span class="text-muted small">{{ verificationRequest.user?.email }}</span>
                        </div>
                        <div class="border-top pt-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Phone</span>
                                <span>{{ verificationRequest.user?.phone || '—' }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Status</span>
                                <span class="badge" :class="statusBadgeClass(verificationRequest.user?.status)">
                                    {{ verificationRequest.user?.status }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Joined</span>
                                <span>{{ formatDate(verificationRequest.user?.created_at) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status History -->
                <div v-if="verificationRequest.status_history?.length" class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Status History</h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div v-for="(entry, index) in verificationRequest.status_history" :key="index"
                                class="timeline-item mb-3 pb-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <span class="badge" :class="statusBadgeClass(entry.status)">
                                            {{ statusLabel(entry.status) }}
                                        </span>
                                        <div v-if="entry.reason" class="text-muted small mt-1">{{ entry.reason }}</div>
                                    </div>
                                    <small class="text-muted">{{ formatDate(entry.created_at) }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
