<script>
import { Link, Head, router } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/portal-layout.vue';

export default {
    components: { Link, Head, PortalLayout },
    props: {
        user: Object,
        trips: Array,
    },
    methods: {
        deleteTrip(id) {
            if (confirm('Are you sure you want to delete this draft trip?')) {
                router.delete(`/portal/trips/${id}`);
            }
        },
        statusBadge(status) {
            const map = {
                draft: 'bg-secondary',
                published: 'bg-primary',
                active: 'bg-info',
                in_progress: 'bg-warning text-dark',
                completed: 'bg-success',
                cancelled: 'bg-danger',
            };
            return map[status] || 'bg-secondary';
        },
        formatDate(date) {
            if (!date) return '—';
            return new Date(date).toLocaleDateString('en-US', {
                year: 'numeric', month: 'short', day: 'numeric',
                hour: '2-digit', minute: '2-digit',
            });
        },
    },
};
</script>

<template>
    <Head title="My Trips - orezone" />

    <PortalLayout>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">My Trips</h4>
                <p class="text-muted mb-0">Manage the trips you've offered to share</p>
            </div>
            <div class="d-flex gap-2">
                <Link href="/portal/trips/search" class="btn btn-outline-primary">
                    <i class="ri-search-line me-1"></i> Search Trips
                </Link>
                <Link href="/portal/trips/create" class="btn btn-primary">
                    <i class="ri-add-line me-1"></i> Create Trip
                </Link>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="!trips || trips.length === 0" class="text-center py-5">
            <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                <span class="display-5">🚗</span>
            </div>
            <h5 class="fw-bold text-dark">No Trips Yet</h5>
            <p class="text-muted">Create your first trip to start sharing rides</p>
            <Link href="/portal/trips/create" class="btn btn-primary">
                <i class="ri-add-line me-1"></i> Create Trip
            </Link>
        </div>

        <!-- Trip Cards -->
        <div v-else class="row g-4">
            <div class="col-md-6 col-lg-4" v-for="trip in trips" :key="trip.id">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="fw-bold text-dark mb-1">
                                    {{ trip.origin_name }}
                                    <i class="ri-arrow-right-line mx-1 text-muted" style="font-size: 0.8rem;"></i>
                                    {{ trip.destination_name }}
                                </h5>
                            </div>
                            <span class="badge" :class="statusBadge(trip.status)">
                                {{ trip.status?.replace('_', ' ') }}
                            </span>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="ri-calendar-line text-muted small"></i>
                                <small>{{ formatDate(trip.departure_at) }}</small>
                            </div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="ri-seat-line text-muted small"></i>
                                <small>{{ trip.available_seats }}/{{ trip.total_seats }} seats</small>
                            </div>
                            <div class="d-flex align-items-center gap-2" v-if="trip.vehicle">
                                <i class="ri-car-line text-muted small"></i>
                                <small>{{ trip.vehicle.brand }} {{ trip.vehicle.model }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-top d-flex gap-2">
                        <Link :href="`/portal/trips/${trip.id}`" class="btn btn-sm btn-outline-primary flex-fill">
                            <i class="ri-eye-line"></i> View
                        </Link>
                        <Link
                            v-if="trip.status === 'draft'"
                            :href="`/portal/trips/${trip.id}/edit`"
                            class="btn btn-sm btn-outline-secondary flex-fill"
                        >
                            <i class="ri-edit-line"></i> Edit
                        </Link>
                        <button
                            v-if="trip.status === 'draft'"
                            class="btn btn-sm btn-outline-danger"
                            @click="deleteTrip(trip.id)"
                            title="Delete Trip"
                        >
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>
