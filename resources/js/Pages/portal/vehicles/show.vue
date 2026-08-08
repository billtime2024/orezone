<script>
import { Link, Head, router } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/portal-layout.vue';

export default {
    components: { Link, Head, PortalLayout },
    props: {
        user: Object,
        vehicle: Object,
    },
    methods: {
        deleteVehicle() {
            if (confirm('Are you sure you want to delete this vehicle? This action cannot be undone.')) {
                router.delete(`/portal/vehicles/${this.vehicle.id}`);
            }
        },
        submitForVerification() {
            if (confirm('Submit this vehicle for admin verification?')) {
                router.post(`/portal/vehicles/${this.vehicle.id}/verify`);
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
                year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit',
            });
        },
    },
};
</script>

<template>
    <Head :title="`${vehicle.brand} ${vehicle.model} - orezone`" />

    <PortalLayout>
        <div class="mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><Link href="/portal/vehicles">My Vehicles</Link></li>
                    <li class="breadcrumb-item active">{{ vehicle.brand }} {{ vehicle.model }}</li>
                </ol>
            </nav>
        </div>

        <div class="row g-4">
            <!-- Vehicle Info -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div>
                                <h4 class="fw-bold text-dark mb-1">{{ vehicle.brand }} {{ vehicle.model }}</h4>
                                <p class="text-muted mb-0">{{ vehicle.registration_number }}</p>
                            </div>
                            <span class="badge fs-6" :class="verificationBadge(vehicle.verification_status)">
                                <i class="ri-verified-badge-line me-1" v-if="vehicle.verification_status === 'approved'"></i>
                                {{ vehicle.verification_status.charAt(0).toUpperCase() + vehicle.verification_status.slice(1) }}
                            </span>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-sm-6 col-md-3">
                                <div class="bg-light rounded-3 p-3 text-center">
                                    <small class="text-muted d-block mb-1">Category</small>
                                    <strong>{{ vehicle.category?.name || '—' }}</strong>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <div class="bg-light rounded-3 p-3 text-center">
                                    <small class="text-muted d-block mb-1">Year</small>
                                    <strong>{{ vehicle.year || '—' }}</strong>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <div class="bg-light rounded-3 p-3 text-center">
                                    <small class="text-muted d-block mb-1">Color</small>
                                    <strong>{{ vehicle.color || '—' }}</strong>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <div class="bg-light rounded-3 p-3 text-center">
                                    <small class="text-muted d-block mb-1">Seats</small>
                                    <strong>{{ vehicle.seating_capacity || '—' }}</strong>
                                </div>
                            </div>
                        </div>

                        <!-- Documents -->
                        <div v-if="vehicle.documents && vehicle.documents.length > 0" class="mt-4">
                            <h6 class="fw-bold text-dark mb-3">Documents</h6>
                            <div class="list-group">
                                <div class="list-group-item d-flex justify-content-between align-items-center" v-for="doc in vehicle.documents" :key="doc.id">
                                    <div>
                                        <i class="ri-file-text-line me-2"></i>
                                        <span>{{ doc.type || 'Document' }}</span>
                                    </div>
                                    <span class="badge bg-light text-dark">{{ doc.status || 'uploaded' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Activity -->
                        <div class="mt-4 pt-3 border-top">
                            <div class="d-flex justify-content-between">
                                <small class="text-muted">Created: {{ formatDate(vehicle.created_at) }}</small>
                                <small class="text-muted">Updated: {{ formatDate(vehicle.updated_at) }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Actions -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-dark mb-3">Actions</h6>
                        <div class="d-grid gap-2">
                            <Link :href="`/portal/vehicles/${vehicle.id}/edit`" class="btn btn-outline-primary">
                                <i class="ri-edit-line me-1"></i> Edit Vehicle
                            </Link>
                            <button
                                v-if="vehicle.verification_status === 'pending' || vehicle.verification_status === 'rejected'"
                                class="btn btn-outline-warning"
                                @click="submitForVerification"
                            >
                                <i class="ri-verified-badge-line me-1"></i> Submit for Verification
                            </button>
                            <button class="btn btn-outline-danger" @click="deleteVehicle">
                                <i class="ri-delete-bin-line me-1"></i> Delete Vehicle
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Status Card -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-dark mb-3">Status</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <small class="text-muted">Verification</small>
                            <span class="badge" :class="verificationBadge(vehicle.verification_status)">
                                {{ vehicle.verification_status }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <small class="text-muted">Active</small>
                            <span class="badge" :class="vehicle.is_active ? 'bg-success' : 'bg-secondary'">
                                {{ vehicle.is_active ? 'Yes' : 'No' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>
