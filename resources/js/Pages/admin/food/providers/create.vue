<script>
import { Link, Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/admin.vue';

export default {
    components: { Link, Head, AdminLayout },
    layout: AdminLayout,
    props: {
        users: Array,
    },
    data() {
        return {
            form: useForm({
                user_id: '',
                provider_type: 'homemade',
                business_name: '',
                description: '',
                phone: '',
                email: '',
                address: '',
                latitude: '',
                longitude: '',
                city: '',
                state: '',
                pincode: '',
                fssai_license: '',
                fssai_expiry: '',
                gst_number: '',
                pan_number: '',
                bank_account_number: '',
                bank_ifsc: '',
                upi_id: '',
                commission_rate: 10,
                delivery_radius_km: 5,
                min_order_amount: 0,
            }),
        };
    },
    methods: {
        submit() {
            this.form.post('/admin/food-providers', {
                onSuccess: () => {},
            });
        },
    },
};
</script>

<template>
    <Head title="Create Food Provider" />

    <div class="container-fluid">
        <!-- Breadcrumb & Header -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <Link href="/admin/food-providers" class="text-decoration-none text-muted">
                        <i class="ri-arrow-left-line"></i> Providers
                    </Link>
                    <span class="text-muted">/</span>
                    <span class="fw-bold">Create New Provider</span>
                </div>
                <p class="text-muted mb-0">Add a new food service provider to the platform</p>
            </div>
        </div>

        <form @submit.prevent="submit">
            <div class="row g-4">
                <!-- Main Content -->
                <div class="col-lg-8">
                    <!-- Basic Information -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h6 class="mb-0 fw-bold">Basic Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">User <span class="text-danger">*</span></label>
                                    <select v-model="form.user_id" class="form-select" :class="{ 'is-invalid': form.errors.user_id }">
                                        <option value="">Select User</option>
                                        <option v-for="user in users" :key="user.id" :value="user.id">
                                            {{ user.name }} ({{ user.email }})
                                        </option>
                                    </select>
                                    <div class="invalid-feedback">{{ form.errors.user_id }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Provider Type <span class="text-danger">*</span></label>
                                    <select v-model="form.provider_type" class="form-select" :class="{ 'is-invalid': form.errors.provider_type }">
                                        <option value="homemade">Homemade</option>
                                        <option value="catering">Catering</option>
                                        <option value="hotel">Hotel</option>
                                    </select>
                                    <div class="invalid-feedback">{{ form.errors.provider_type }}</div>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Business Name <span class="text-danger">*</span></label>
                                    <input v-model="form.business_name" type="text" class="form-control" :class="{ 'is-invalid': form.errors.business_name }" placeholder="Enter business name" />
                                    <div class="invalid-feedback">{{ form.errors.business_name }}</div>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Description</label>
                                    <textarea v-model="form.description" class="form-control" rows="3" placeholder="Brief description of the food business"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h6 class="mb-0 fw-bold">Contact Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Phone</label>
                                    <input v-model="form.phone" type="text" class="form-control" placeholder="Phone number" />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input v-model="form.email" type="email" class="form-control" placeholder="Email address" />
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Address</label>
                                    <textarea v-model="form.address" class="form-control" rows="2" placeholder="Full address"></textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">City</label>
                                    <input v-model="form.city" type="text" class="form-control" placeholder="City" />
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">State</label>
                                    <input v-model="form.state" type="text" class="form-control" placeholder="State" />
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Pincode</label>
                                    <input v-model="form.pincode" type="text" class="form-control" placeholder="Pincode" />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Latitude</label>
                                    <input v-model="form.latitude" type="number" step="0.000001" class="form-control" placeholder="e.g. 28.6139" />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Longitude</label>
                                    <input v-model="form.longitude" type="number" step="0.000001" class="form-control" placeholder="e.g. 77.2090" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Legal & Compliance -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h6 class="mb-0 fw-bold">Legal & Compliance</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">FSSAI License</label>
                                    <input v-model="form.fssai_license" type="text" class="form-control" placeholder="FSSAI license number" />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">FSSAI Expiry</label>
                                    <input v-model="form.fssai_expiry" type="date" class="form-control" />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">GST Number</label>
                                    <input v-model="form.gst_number" type="text" class="form-control" placeholder="GST number" />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">PAN Number</label>
                                    <input v-model="form.pan_number" type="text" class="form-control" placeholder="PAN number" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Banking Details -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h6 class="mb-0 fw-bold">Banking Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Bank Account Number</label>
                                    <input v-model="form.bank_account_number" type="text" class="form-control" placeholder="Account number" />
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">IFSC Code</label>
                                    <input v-model="form.bank_ifsc" type="text" class="form-control" placeholder="IFSC code" />
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">UPI ID</label>
                                    <input v-model="form.upi_id" type="text" class="form-control" placeholder="UPI ID" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Settings -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h6 class="mb-0 fw-bold">Provider Settings</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Commission Rate (%)</label>
                                <input v-model="form.commission_rate" type="number" step="0.01" min="0" max="100" class="form-control" />
                                <small class="text-muted">Default: 10%</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Delivery Radius (km)</label>
                                <input v-model="form.delivery_radius_km" type="number" min="0" class="form-control" />
                                <small class="text-muted">Default: 5 km</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Min Order Amount (₹)</label>
                                <input v-model="form.min_order_amount" type="number" step="0.01" min="0" class="form-control" />
                                <small class="text-muted">Default: ₹0</small>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <button type="submit" class="btn btn-primary w-100" :disabled="form.processing">
                                <i class="ri-save-line me-1" v-if="!form.processing"></i>
                                <span class="spinner-border spinner-border-sm me-1" v-if="form.processing"></span>
                                {{ form.processing ? 'Creating...' : 'Create Provider' }}
                            </button>
                            <Link href="/admin/food-providers" class="btn btn-outline-secondary w-100 mt-2">
                                <i class="ri-close-line me-1"></i> Cancel
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>
