<script>
import { Link, Head } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/portal-layout.vue';

export default {
    components: { Link, Head, PortalLayout },
    props: {
        requests: Object,
    },
    data() {
        return {
            statusColors: {
                pending: 'warning',
                quotes_received: 'info',
                quote_selected: 'primary',
                tasting_scheduled: 'primary',
                confirmed: 'success',
                in_progress: 'success',
                completed: 'success',
                cancelled: 'danger',
            },
        };
    },
    methods: {
        formatStatus(status) {
            return status?.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        },
    },
};
</script>

<template>
    <Head title="My Catering Requests - orezone Food" />

    <PortalLayout>
        <!-- Breadcrumb -->
        <div class="mb-3">
            <Link href="/portal/food" class="text-decoration-none" style="color: #2E7D5B;">
                <i class="ri-arrow-left-line"></i> Food Services
            </Link>
            <span class="text-muted mx-2">/</span>
            <span class="text-dark fw-semibold">Catering</span>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">Catering Requests</h4>
            <Link href="/portal/food/catering/create" class="btn btn-sm text-white" style="background-color: #2E7D5B;">
                <i class="ri-add-line me-1"></i> New Request
            </Link>
        </div>

        <div v-if="requests.data.length">
            <div v-for="req in requests.data" :key="req.id" class="card border-0 shadow-sm mb-3">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="fw-bold">{{ req.request_number }}</span>
                                <span class="badge" :class="'bg-' + (statusColors[req.status] || 'secondary')">
                                    {{ formatStatus(req.status) }}
                                </span>
                            </div>
                            <h6 class="mb-1">{{ req.event_name }}</h6>
                            <small class="text-muted">{{ req.event_type }} • {{ req.event_date }}</small>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-sm-3">
                            <small class="text-muted d-block">Guests</small>
                            <span class="fw-semibold">{{ req.guest_count }}</span>
                        </div>
                        <div class="col-sm-3">
                            <small class="text-muted d-block">Budget</small>
                            <span class="fw-semibold">
                                <span v-if="req.budget_min">₹{{ req.budget_min }}</span>
                                <span v-if="req.budget_min && req.budget_max"> - </span>
                                <span v-if="req.budget_max">₹{{ req.budget_max }}</span>
                                <span v-if="!req.budget_min && !req.budget_max">Not specified</span>
                            </span>
                        </div>
                        <div class="col-sm-3">
                            <small class="text-muted d-block">Provider</small>
                            <span class="fw-semibold">{{ req.provider?.business_name || 'Open for quotes' }}</span>
                        </div>
                        <div class="col-sm-3">
                            <small class="text-muted d-block">Quotes</small>
                            <span class="badge" style="background-color: #2E7D5B;">{{ req.quotes_count }}</span>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                        <small class="text-muted"><i class="ri-map-pin-line me-1"></i>{{ req.venue_address }}</small>
                        <span class="text-muted small">{{ req.created_at }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="text-center py-5">
            <div style="font-size: 64px;" class="mb-3">🎉</div>
            <h5 class="text-muted">No catering requests yet</h5>
            <p class="text-muted">Need food for an event? Create a catering request and get quotes from verified providers</p>
            <Link href="/portal/food/catering/create" class="btn text-white" style="background-color: #2E7D5B;">
                Create Request
            </Link>
        </div>

        <div v-if="requests.last_page > 1" class="d-flex justify-content-center mt-4">
            <nav>
                <ul class="pagination">
                    <li v-for="page in requests.last_page" :key="page" class="page-item" :class="{ active: page === requests.current_page }">
                        <Link :href="`/portal/food/catering?page=${page}`" class="page-link">{{ page }}</Link>
                    </li>
                </ul>
            </nav>
        </div>
    </PortalLayout>
</template>
