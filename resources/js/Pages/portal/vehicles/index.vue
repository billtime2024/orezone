<script>
import { Link, Head, router } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/portal-layout.vue';

export default {
    components: { Link, Head, PortalLayout },
    props: {
        user: Object,
        vehicles: Array,
        categories: Array,
    },
    methods: {
        deleteVehicle(id) {
            if (confirm('Are you sure you want to delete this vehicle?')) {
                router.delete(`/portal/vehicles/${id}`);
            }
        },
        submitForVerification(id) {
            if (confirm('Submit this vehicle for verification?')) {
                router.post(`/portal/vehicles/${id}/verify`);
            }
        },
        verificationBadge(status) {
            const map = {
                pending: 'bg-warning text-dark',
                approved: 'bg-success',
                rejected: 'bg-danger',
            };
            return map[status] || 'bg-secondary';
        },
        formatDate(date) {
            if (!date) return '—';
            return new Date(date).toLocaleDateString('en-US', {
                year: 'numeric', month: 'short', day: 'numeric',
            });
        },
    },
};
</script>

<template>
    <Head title="My Vehicles - orezone" />

    <PortalLayout>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">My Vehicles</h4>
                <p class="text-muted mb-0">Manage your registered vehicles</p>
            </div>
            <Link href="/portal/vehicles/create" class="btn btn-primary">
                <i class="ri-add-line me-1"></i> Add Vehicle
            </Link>
        </div>

        <!-- Empty State -->
        <div v-if="!vehicles || vehicles.length === 0" class="text-center py-5">
            <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                <span class="display-5">🚗</span>
            </div>
            <h5 class="fw-bold text-dark">No Vehicles Yet</h5>
            <p class="text-muted">Add your first vehicle to start sharing rides</p>
            <Link href="/portal/vehicles/create" class="btn btn-primary">
                <i class="ri-add-line me-1"></i> Add Vehicle
            </Link>
        </div>

        <!-- Vehicle Cards -->
        <div v-else class="row g-4">
            <div class="col-md-6 col-lg-4" v-for="vehicle in vehicles" :key="vehicle.id">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="fw-bold text-dark mb-1">{{ vehicle.brand }} {{ vehicle.model }}</h5>
                                <small class="text-muted">{{ vehicle.registration_number }}</small>
                            </div>
                            <span class="badge" :class="verificationBadge(vehicle.verification_status)">
                                {{ vehicle.verification_status }}
                            </span>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="text-muted">Category</small>
                                <small class="fw-medium">{{ vehicle.category?.name || '—' }}</small>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <small class="text-muted">Year</small>
                                <small class="fw-medium">{{ vehicle.year || '—' }}</small>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <small class="text-muted">Color</small>
                                <small class="fw-medium">{{ vehicle.color || '—' }}</small>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <small class="text-muted">Seats</small>
                                <small class="fw-medium">{{ vehicle.seating_capacity || '—' }}</small>
                            </div>
                            <div class="d-flex justify-content-between">
                                <small class="text-muted">Status</small>
                                <span class="badge" :class="vehicle.is_active ? 'bg-success' : 'bg-secondary'">
                                    {{ vehicle.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-top d-flex gap-2">
                        <Link :href="`/portal/vehicles/${vehicle.id}`" class="btn btn-sm btn-outline-primary flex-fill">
                            <i class="ri-eye-line"></i> View
                        </Link>
                        <Link :href="`/portal/vehicles/${vehicle.id}/edit`" class="btn btn-sm btn-outline-secondary flex-fill">
                            <i class="ri-edit-line"></i> Edit
                        </Link>
                        <button
                            v-if="vehicle.verification_status === 'pending' || vehicle.verification_status === 'rejected'"
                            class="btn btn-sm btn-outline-warning"
                            @click="submitForVerification(vehicle.id)"
                            title="Submit for Verification"
                        >
                            <i class="ri-verified-badge-line"></i>
                        </button>
                        <button
                            class="btn btn-sm btn-outline-danger"
                            @click="deleteVehicle(vehicle.id)"
                            title="Delete Vehicle"
                        >
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>
