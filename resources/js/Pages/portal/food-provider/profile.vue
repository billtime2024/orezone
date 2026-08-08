<script>
import { Link, Head } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/portal-layout.vue';

export default {
    components: { Link, Head, PortalLayout },
    props: {
        provider: Object,
    },
    computed: {
        operatingHours() {
            if (!this.provider.operating_hours || typeof this.provider.operating_hours !== 'object') return {};
            return this.provider.operating_hours;
        },
    },
    methods: {
        formatDate(date) {
            if (!date) return '—';
            return new Date(date).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
        },
        verificationBadge() {
            const map = {
                approved: 'bg-success',
                pending: 'bg-warning text-dark',
                rejected: 'bg-danger',
            };
            return map[this.provider.verification_status] || 'bg-secondary';
        },
        verificationLabel() {
            const map = {
                approved: '✅ Verified',
                pending: '⏳ Pending Verification',
                rejected: '❌ Rejected',
            };
            return map[this.provider.verification_status] || this.provider.verification_status;
        },
    },
};
</script>

<template>
    <Head title="Provider Profile - orezone" />
    <PortalLayout>
        <div class="d-flex align-items-center gap-3 mb-4">
            <Link href="/portal/food-provider" class="btn btn-sm btn-outline-secondary">
                <i class="ri-arrow-left-line"></i>
            </Link>
            <div>
                <h4 class="fw-bold text-dark mb-1">Provider Profile</h4>
                <p class="text-muted mb-0">Your food provider business details</p>
            </div>
        </div>

        <!-- Profile Header -->
        <div class="card border-0 shadow-sm mb-4 overflow-hidden">
            <div class="p-4 text-white" style="background: linear-gradient(135deg, #2E7D5B, #1a5c3d);">
                <div class="d-flex align-items-center gap-4">
                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 80px; height: 80px; font-size: 32px;">
                        🍽️
                    </div>
                    <div>
                        <h3 class="fw-bold mb-1">{{ provider.business_name }}</h3>
                        <p class="mb-2 opacity-75">{{ provider.description || 'Pure Veg Food Provider' }}</p>
                        <div class="d-flex gap-2">
                            <span class="badge" :class="verificationBadge()">{{ verificationLabel() }}</span>
                            <span class="badge" :class="provider.is_active ? 'bg-success' : 'bg-secondary'">
                                {{ provider.is_active ? 'Active' : 'Inactive' }}
                            </span>
                            <span v-if="provider.is_featured" class="badge bg-warning text-dark">⭐ Featured</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Business Info -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-semibold">Business Information</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <td class="text-muted" style="width: 40%;">Business Name</td>
                                <td class="fw-semibold">{{ provider.business_name || '—' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Phone</td>
                                <td>{{ provider.phone || '—' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Email</td>
                                <td>{{ provider.email || '—' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Provider Type</td>
                                <td class="text-capitalize">{{ provider.provider_type || '—' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Avg Rating</td>
                                <td>
                                    <span v-if="provider.avg_rating">⭐ {{ Number(provider.avg_rating).toFixed(1) }}</span>
                                    <span v-else class="text-muted">No ratings yet</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Address -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-semibold">Address</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-1">{{ provider.address || '—' }}</p>
                        <p class="mb-1">{{ provider.city || '' }}{{ provider.state ? ', ' + provider.state : '' }} {{ provider.pincode || '' }}</p>
                    </div>
                </div>

                <!-- Delivery Settings -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-semibold">Delivery Settings</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <td class="text-muted" style="width: 40%;">Delivery Radius</td>
                                <td>{{ provider.delivery_radius_km || '—' }} km</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Min Order Amount</td>
                                <td>₹{{ provider.min_order_amount || '—' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Free Delivery Above</td>
                                <td>₹{{ provider.free_delivery_above || '—' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <!-- License Info -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-semibold">License & Compliance</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <td class="text-muted" style="width: 40%;">FSSAI License</td>
                                <td>{{ provider.fssai_license || '—' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">FSSAI Expiry</td>
                                <td>{{ formatDate(provider.fssai_expiry) }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">GST Number</td>
                                <td>{{ provider.gst_number || '—' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">PAN Number</td>
                                <td>{{ provider.pan_number || '—' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Operating Hours -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-semibold">Operating Hours</h6>
                    </div>
                    <div class="card-body">
                        <div v-if="Object.keys(operatingHours).length === 0" class="text-muted small">
                            No operating hours configured
                        </div>
                        <table v-else class="table table-borderless mb-0">
                            <tr v-for="(hours, day) in operatingHours" :key="day">
                                <td class="text-capitalize fw-medium" style="width: 40%;">{{ day }}</td>
                                <td>{{ typeof hours === 'object' ? (hours.open || '—') + ' - ' + (hours.close || '—') : hours }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Stats -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-semibold">Performance</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <td class="text-muted" style="width: 40%;">Total Orders</td>
                                <td class="fw-semibold">{{ provider.total_orders || 0 }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Total Revenue</td>
                                <td class="fw-semibold" style="color: #2E7D5B;">₹{{ Number(provider.total_revenue || 0).toLocaleString('en-IN') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Commission Rate</td>
                                <td>{{ provider.commission_rate || 0 }}%</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>
