<script>
import { Link, Head, useForm } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/portal-layout.vue';

export default {
    components: { Link, Head, PortalLayout },
    props: {
        user: Object,
        trips: Object,
        filters: Object,
    },
    data() {
        return {
            form: useForm({
                origin: this.filters?.origin || '',
                destination: this.filters?.destination || '',
                departure_date: this.filters?.departure_date || '',
            }),
        };
    },
    methods: {
        search() {
            this.form.get('/portal/trips/search', {
                preserveState: true,
            });
        },
        statusBadge(status) {
            const map = {
                published: 'bg-primary',
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
    <Head title="Search Trips - orezone" />

    <PortalLayout>
        <div class="mb-4">
            <h4 class="fw-bold mb-1">Search Trips</h4>
            <p class="text-muted mb-0">Find available rides to your destination</p>
        </div>

        <!-- Search Form -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <form @submit.prevent="search">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="origin" class="form-label fw-semibold">Origin</label>
                            <input
                                id="origin"
                                v-model="form.origin"
                                type="text"
                                class="form-control"
                                placeholder="e.g. Mumbai"
                            />
                        </div>
                        <div class="col-md-4">
                            <label for="destination" class="form-label fw-semibold">Destination</label>
                            <input
                                id="destination"
                                v-model="form.destination"
                                type="text"
                                class="form-control"
                                placeholder="e.g. Pune"
                            />
                        </div>
                        <div class="col-md-3">
                            <label for="departure_date" class="form-label fw-semibold">Date</label>
                            <input
                                id="departure_date"
                                v-model="form.departure_date"
                                type="date"
                                class="form-control"
                            />
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100" :disabled="form.processing">
                                <i class="ri-search-line"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Results -->
        <div v-if="trips && trips.data && trips.data.length > 0">
            <h6 class="fw-bold text-dark mb-3">{{ trips.total || trips.data.length }} trips found</h6>
            <div class="row g-4">
                <div class="col-md-6 col-lg-4" v-for="trip in trips.data" :key="trip.id">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="fw-bold text-dark mb-0">{{ trip.origin_name }}</h5>
                                    <i class="ri-arrow-right-line text-muted mx-1"></i>
                                    <h5 class="fw-bold text-dark mb-0">{{ trip.destination_name }}</h5>
                                </div>
                                <span class="badge" :class="statusBadge(trip.status)">
                                    {{ trip.status }}
                                </span>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="ri-calendar-line text-muted"></i>
                                    <small>{{ formatDate(trip.departure_at) }}</small>
                                </div>
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="ri-seat-line text-muted"></i>
                                    <small>{{ trip.available_seats }}/{{ trip.total_seats }} seats available</small>
                                </div>
                                <div class="d-flex align-items-center gap-2" v-if="trip.host">
                                    <i class="ri-user-line text-muted"></i>
                                    <small>{{ trip.host.name }}</small>
                                </div>
                                <div class="d-flex align-items-center gap-2" v-if="trip.booking_mode">
                                    <i class="ri-flashlight-line text-muted"></i>
                                    <small class="text-capitalize">{{ trip.booking_mode }} booking</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-top">
                            <Link :href="`/portal/trips/${trip.id}`" class="btn btn-primary w-100">
                                <i class="ri-eye-line me-1"></i> View Details
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="trips.last_page > 1" class="d-flex justify-content-center mt-4">
                <nav>
                    <ul class="pagination">
                        <li class="page-item" :class="{ disabled: !trips.prev_page_url }">
                            <Link
                                :href="trips.prev_page_url || '#'"
                                class="page-link"
                                :preserve-state="true"
                            >
                                Previous
                            </Link>
                        </li>
                        <li class="page-item disabled">
                            <span class="page-link">Page {{ trips.current_page }} of {{ trips.last_page }}</span>
                        </li>
                        <li class="page-item" :class="{ disabled: !trips.next_page_url }">
                            <Link
                                :href="trips.next_page_url || '#'"
                                class="page-link"
                                :preserve-state="true"
                            >
                                Next
                            </Link>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>

        <!-- No Results -->
        <div v-else class="text-center py-5">
            <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                <span class="display-5">🔍</span>
            </div>
            <h5 class="fw-bold text-dark">No Trips Found</h5>
            <p class="text-muted">Try adjusting your search criteria</p>
        </div>
    </PortalLayout>
</template>
