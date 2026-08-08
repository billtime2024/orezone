<script>
import { Link, Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/admin.vue';

export default {
    components: { Link, Head, AdminLayout },
    layout: AdminLayout,
    props: {
        request: Object,
    },
    methods: {
        statusBadgeClass(status) {
            switch (status) {
                case 'pending': return 'bg-warning-subtle text-warning';
                case 'quoted': return 'bg-info-subtle text-info';
                case 'accepted': return 'bg-success-subtle text-success';
                case 'confirmed': return 'bg-success-subtle text-success';
                case 'completed': return 'bg-secondary-subtle text-secondary';
                case 'cancelled': return 'bg-danger-subtle text-danger';
                case 'expired': return 'bg-secondary-subtle text-secondary';
                default: return 'bg-secondary-subtle text-secondary';
            }
        },
        quoteStatusClass(status) {
            switch (status) {
                case 'pending': return 'bg-warning-subtle text-warning';
                case 'accepted': return 'bg-success-subtle text-success';
                case 'rejected': return 'bg-danger-subtle text-danger';
                default: return 'bg-secondary-subtle text-secondary';
            }
        },
        formatCurrency(amount) {
            if (!amount) return '₹0';
            return '₹' + Number(amount).toLocaleString('en-IN');
        },
        formatDate(dateStr) {
            if (!dateStr) return '—';
            return new Date(dateStr).toLocaleDateString('en-IN', {
                day: '2-digit', month: 'short', year: 'numeric',
            });
        },
        formatDateTime(dateStr) {
            if (!dateStr) return '—';
            return new Date(dateStr).toLocaleDateString('en-IN', {
                day: '2-digit', month: 'short', year: 'numeric',
                hour: '2-digit', minute: '2-digit',
            });
        },
    },
};
</script>

<template>
    <Head :title="`Catering Request #${request.request_number || request.id}`" />

    <div class="container-fluid">
        <!-- Breadcrumb & Header -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <Link href="/admin/food/catering" class="text-decoration-none text-muted">
                        <i class="ri-arrow-left-line"></i> Catering
                    </Link>
                    <span class="text-muted">/</span>
                    <span class="fw-bold">#{{ request.request_number || request.id }}</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge rounded-pill" :class="statusBadgeClass(request.status)">{{ request.status }}</span>
                    <span class="fw-semibold">{{ request.event_name || 'Catering Request' }}</span>
                </div>
            </div>
            <span class="text-muted small">Created {{ formatDateTime(request.created_at) }}</span>
        </div>

        <div class="row g-4">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Event Details -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-bold">Event Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small text-muted">Event Name</label>
                                <div class="fw-semibold">{{ request.event_name || '—' }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted">Event Type</label>
                                <div class="fw-semibold text-capitalize">{{ request.event_type || '—' }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted">Number of Guests</label>
                                <div class="fw-semibold">{{ request.guests_count || '—' }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted">Event Date</label>
                                <div class="fw-semibold">{{ formatDate(request.event_date) }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted">Event Time</label>
                                <div class="fw-semibold">{{ request.event_time || '—' }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted">Venue</label>
                                <div>{{ request.venue || '—' }}</div>
                            </div>
                            <div class="col-md-12" v-if="request.venue_address">
                                <label class="form-label small text-muted">Venue Address</label>
                                <div>{{ request.venue_address }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Budget -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-bold">Budget</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-4">
                            <div class="text-center">
                                <small class="text-muted d-block">Minimum</small>
                                <span class="fw-bold text-primary fs-5">{{ formatCurrency(request.budget_min) }}</span>
                            </div>
                            <i class="ri-arrow-right-line text-muted"></i>
                            <div class="text-center">
                                <small class="text-muted d-block">Maximum</small>
                                <span class="fw-bold text-primary fs-5">{{ formatCurrency(request.budget_max) }}</span>
                            </div>
                            <div class="ms-auto">
                                <small class="text-muted">Per person: </small>
                                <span class="fw-semibold" v-if="request.guests_count && request.budget_max">
                                    ~{{ formatCurrency(Math.round(request.budget_max / request.guests_count)) }}
                                </span>
                                <span v-else class="text-muted">—</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dietary Requirements -->
                <div class="card border-0 shadow-sm mb-4" v-if="request.dietary_requirements?.length">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-bold">
                            <i class="ri-leaf-line me-1 text-success"></i> Dietary Requirements
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            <span
                                v-for="(req, idx) in request.dietary_requirements"
                                :key="idx"
                                class="badge bg-success-subtle text-success"
                            >
                                {{ req }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Menu Description -->
                <div class="card border-0 shadow-sm mb-4" v-if="request.menu_description">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-bold">Menu Description</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0 text-muted">{{ request.menu_description }}</p>
                    </div>
                </div>

                <!-- Special Requests -->
                <div class="card border-0 shadow-sm mb-4" v-if="request.special_requests">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-bold">Special Requests</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0 text-muted">{{ request.special_requests }}</p>
                    </div>
                </div>

                <!-- Quotes Section -->
                <div class="card border-0 shadow-sm" v-if="request.quotes?.length">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between">
                        <h6 class="mb-0 fw-bold">Quotes</h6>
                        <span class="badge bg-light text-dark">{{ request.quotes.length }} quotes</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Provider</th>
                                        <th>Amount</th>
                                        <th>Menu</th>
                                        <th>Status</th>
                                        <th>Submitted</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="quote in request.quotes" :key="quote.id">
                                        <td>
                                            <Link :href="`/admin/food/providers/${quote.provider?.id}`" class="text-decoration-none fw-semibold">
                                                {{ quote.provider?.business_name || '—' }}
                                            </Link>
                                        </td>
                                        <td class="fw-semibold text-success">{{ formatCurrency(quote.amount) }}</td>
                                        <td>
                                            <small class="text-muted">{{ quote.menu_description || '—' }}</small>
                                        </td>
                                        <td>
                                            <span class="badge rounded-pill" :class="quoteStatusClass(quote.status)">
                                                {{ quote.status || 'pending' }}
                                            </span>
                                        </td>
                                        <td><small class="text-muted">{{ formatDate(quote.created_at) }}</small></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Customer Info -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-bold">Customer</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="avatar-sm rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center">
                                <span class="text-info fw-semibold">
                                    {{ request.user?.name?.charAt(0)?.toUpperCase() || '?' }}
                                </span>
                            </div>
                            <div>
                                <div class="fw-semibold">{{ request.user?.name || 'Unknown' }}</div>
                                <small class="text-muted">{{ request.user?.phone || '' }}</small>
                            </div>
                        </div>
                        <div v-if="request.user?.email" class="small text-muted mb-1">
                            <i class="ri-mail-line me-1"></i> {{ request.user.email }}
                        </div>
                    </div>
                </div>

                <!-- Payment Info -->
                <div class="card border-0 shadow-sm mb-4" v-if="request.payment_status">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-bold">Payment</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <label class="form-label small text-muted">Status</label>
                            <div>
                                <span
                                    class="badge rounded-pill"
                                    :class="request.payment_status === 'paid' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning'"
                                >
                                    {{ request.payment_status }}
                                </span>
                            </div>
                        </div>
                        <div v-if="request.payment_amount">
                            <label class="form-label small text-muted">Amount Paid</label>
                            <div class="fw-semibold">{{ formatCurrency(request.payment_amount) }}</div>
                        </div>
                    </div>
                </div>

                <!-- Assigned Provider -->
                <div class="card border-0 shadow-sm" v-if="request.assigned_provider">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-bold">Assigned Provider</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-sm rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center">
                                <span class="text-success fw-semibold">
                                    {{ request.assigned_provider?.business_name?.charAt(0)?.toUpperCase() || '?' }}
                                </span>
                            </div>
                            <div>
                                <Link :href="`/admin/food/providers/${request.assigned_provider?.id}`" class="text-decoration-none fw-semibold">
                                    {{ request.assigned_provider?.business_name }}
                                </Link>
                                <div><small class="text-muted text-capitalize">{{ request.assigned_provider?.provider_type }}</small></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
