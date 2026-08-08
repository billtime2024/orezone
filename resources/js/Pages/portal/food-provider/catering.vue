<script>
import { Link, Head } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/portal-layout.vue';

export default {
    components: { Link, Head, PortalLayout },
    props: {
        provider: Object,
        cateringRequests: Object,
        statusFilter: String,
    },
    data() {
        return {
            statuses: [
                { value: '', label: 'All' },
                { value: 'pending', label: 'Pending' },
                { value: 'confirmed', label: 'Confirmed' },
                { value: 'in_progress', label: 'In Progress' },
                { value: 'completed', label: 'Completed' },
                { value: 'cancelled', label: 'Cancelled' },
            ],
        };
    },
    methods: {
        formatCurrency(amount) {
            return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(amount || 0);
        },
        formatDate(date) {
            if (!date) return '—';
            return new Date(date).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
        },
        statusBadge(status) {
            const map = {
                pending: 'bg-warning text-dark',
                quotes_received: 'bg-info text-dark',
                quote_selected: 'bg-primary',
                tasting_scheduled: 'bg-info',
                confirmed: 'bg-success',
                in_progress: 'bg-primary',
                completed: 'bg-success',
                cancelled: 'bg-danger',
            };
            return map[status] || 'bg-secondary';
        },
        getStatusUrl(status) {
            return status ? `/portal/food-provider/catering?status=${status}` : '/portal/food-provider/catering';
        },
    },
};
</script>

<template>
    <Head title="Catering - orezone Provider" />
    <PortalLayout>
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold text-dark mb-1">Catering Requests</h4>
                <p class="text-muted mb-0">Manage catering inquiries and bookings</p>
            </div>
        </div>

        <!-- Status Filter Tabs -->
        <div class="mb-4">
            <div class="d-flex flex-wrap gap-2">
                <Link v-for="s in statuses" :key="s.value"
                    :href="getStatusUrl(s.value)"
                    class="btn btn-sm rounded-pill"
                    :class="statusFilter === s.value || (!statusFilter && s.value === '') ? 'text-white' : 'btn-outline-secondary'"
                    :style="(statusFilter === s.value || (!statusFilter && s.value === '')) ? 'background-color: #2E7D5B;' : ''">
                    {{ s.label }}
                </Link>
            </div>
        </div>

        <!-- Requests List -->
        <div class="row g-3">
            <div v-if="cateringRequests.data?.length === 0" class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <div class="text-muted">
                            <i class="ri-calendar-event-line fs-1 d-block mb-2"></i>
                            <p class="mb-0">No catering requests found</p>
                        </div>
                    </div>
                </div>
            </div>

            <div v-for="req in cateringRequests.data" :key="req.id" class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="fw-bold text-dark mb-1">{{ req.event_name || 'Catering Request' }}</h6>
                                <small class="text-muted">#{{ req.request_number }}</small>
                            </div>
                            <span class="badge" :class="statusBadge(req.status)">
                                {{ req.status?.replace('_', ' ') }}
                            </span>
                        </div>

                        <div class="mb-3">
                            <div class="small mb-1">
                                <i class="ri-calendar-line me-1 text-muted"></i>
                                {{ formatDate(req.event_date) }}
                                <span v-if="req.event_end_date && req.event_end_date !== req.event_date">
                                    — {{ formatDate(req.event_end_date) }}
                                </span>
                            </div>
                            <div class="small mb-1">
                                <i class="ri-map-pin-line me-1 text-muted"></i>
                                {{ req.venue_address || 'Venue TBD' }}
                            </div>
                            <div class="small mb-1">
                                <i class="ri-group-line me-1 text-muted"></i>
                                {{ req.guest_count }} guests
                            </div>
                            <div v-if="req.budget_min || req.budget_max" class="small mb-1">
                                <i class="ri-money-rupee-circle-line me-1 text-muted"></i>
                                {{ formatCurrency(req.budget_min) }} — {{ formatCurrency(req.budget_max) }}
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-1 mb-3">
                            <span v-if="req.event_type" class="badge bg-light text-dark text-capitalize">
                                {{ req.event_type?.replace('_', ' ') }}
                            </span>
                            <span v-if="req.tasting_requested" class="badge bg-light text-dark">
                                🍽️ Tasting Requested
                            </span>
                        </div>

                        <div class="small text-muted">
                            By {{ req.user?.name || 'Customer' }}
                            <span v-if="req.user?.phone"> · {{ req.user.phone }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="cateringRequests.last_page > 1" class="d-flex justify-content-center mt-4">
            <nav>
                <ul class="pagination">
                    <li v-for="page in cateringRequests.last_page" :key="page" class="page-item"
                        :class="{ active: page === cateringRequests.current_page }">
                        <Link :href="cateringRequests.path + (statusFilter ? '?status=' + statusFilter + '&' : '?') + 'page=' + page"
                            class="page-link">{{ page }}</Link>
                    </li>
                </ul>
            </nav>
        </div>
    </PortalLayout>
</template>
