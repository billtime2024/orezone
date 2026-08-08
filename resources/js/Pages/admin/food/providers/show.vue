<script>
import { Link, Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/admin.vue';

export default {
    components: { Link, Head, AdminLayout },
    layout: AdminLayout,
    props: {
        provider: Object,
        stats: Object,
    },
    methods: {
        confirmDelete() {
            if (confirm(`Are you sure you want to delete "${this.provider.business_name}"? This action cannot be undone.`)) {
                this.$inertia.delete(`/admin/food-providers/${this.provider.id}`);
            }
        },
        statusBadgeClass(status) {
            switch (status) {
                case 'approved': return 'bg-success-subtle text-success';
                case 'pending': return 'bg-warning-subtle text-warning';
                case 'rejected': return 'bg-danger-subtle text-danger';
                default: return 'bg-secondary-subtle text-secondary';
            }
        },
        orderStatusClass(status) {
            switch (status) {
                case 'pending': return 'bg-warning-subtle text-warning';
                case 'confirmed': return 'bg-info-subtle text-info';
                case 'preparing': return 'bg-info-subtle text-info';
                case 'delivered': case 'completed': return 'bg-success-subtle text-success';
                case 'cancelled': return 'bg-danger-subtle text-danger';
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
    <Head :title="`Provider: ${provider.business_name}`" />

    <div class="container-fluid">
        <!-- Breadcrumb & Header -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <Link href="/admin/food/providers" class="text-decoration-none text-muted">
                        <i class="ri-arrow-left-line"></i> Providers
                    </Link>
                    <span class="text-muted">/</span>
                    <span class="fw-bold">{{ provider.business_name }}</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge rounded-pill" :class="statusBadgeClass(provider.status)">{{ provider.status }}</span>
                    <span class="badge bg-light text-dark text-capitalize">{{ provider.provider_type }}</span>
                    <span v-if="provider.is_verified" class="badge bg-success-subtle text-success">
                        <i class="ri-verified-badge-fill me-1"></i> Verified
                    </span>
                    <span v-if="provider.is_featured" class="badge bg-warning-subtle text-warning">
                        <i class="ri-star-fill me-1"></i> Featured
                    </span>
                </div>
            </div>
            <div class="d-flex gap-2">
                <Link :href="`/admin/food-providers/${provider.id}/edit`" class="btn btn-sm btn-outline-primary">
                    <i class="ri-edit-line me-1"></i> Edit
                </Link>
                <button @click="confirmDelete" class="btn btn-sm btn-outline-danger">
                    <i class="ri-delete-bin-line me-1"></i> Delete
                </button>
                <Link :href="`/admin/food/providers/${provider.id}/approve`" method="post" as="button" class="btn btn-sm btn-success" v-if="provider.status !== 'approved'">
                    <i class="ri-check-line me-1"></i> Approve
                </Link>
                <Link :href="`/admin/food/providers/${provider.id}/reject`" method="post" as="button" class="btn btn-sm btn-danger" v-if="provider.status === 'pending'">
                    <i class="ri-close-line me-1"></i> Reject
                </Link>
                <Link :href="`/admin/food/providers/${provider.id}/toggle-featured`" method="post" as="button" class="btn btn-sm btn-outline-warning">
                    <i class="ri-star-line me-1"></i> {{ provider.is_featured ? 'Unfeature' : 'Feature' }}
                </Link>
                <Link :href="`/admin/food/providers/${provider.id}/toggle-active`" method="post" as="button" class="btn btn-sm btn-outline-secondary">
                    <i class="ri-toggle-line me-1"></i> {{ provider.is_active ? 'Deactivate' : 'Activate' }}
                </Link>
            </div>
        </div>

        <!-- Stats Row -->
        <div class="row g-3 mb-4">
            <div class="col-md-2">
                <div class="card border-0 shadow-sm text-center p-3">
                    <div class="fw-bold text-primary fs-4">{{ stats.total_orders || 0 }}</div>
                    <small class="text-muted">Total Orders</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-0 shadow-sm text-center p-3">
                    <div class="fw-bold text-success fs-4">{{ formatCurrency(stats.total_revenue) }}</div>
                    <small class="text-muted">Revenue</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-0 shadow-sm text-center p-3">
                    <div class="fw-bold text-info fs-4">{{ formatCurrency(stats.total_commission) }}</div>
                    <small class="text-muted">Commission</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-0 shadow-sm text-center p-3">
                    <div class="fw-bold text-warning fs-4">★ {{ stats.avg_rating || '0' }}</div>
                    <small class="text-muted">Rating</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-0 shadow-sm text-center p-3">
                    <div class="fw-bold fs-4">{{ stats.items_count || 0 }}</div>
                    <small class="text-muted">Menu Items</small>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Contact & Business Info -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-bold">Business Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small text-muted">Contact Phone</label>
                                <div class="fw-semibold">{{ provider.contact_phone || '—' }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted">Email</label>
                                <div class="fw-semibold">{{ provider.contact_email || '—' }}</div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small text-muted">Address</label>
                                <div>{{ provider.address || '—' }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted">FSSAI License</label>
                                <div>{{ provider.fssai_number || '—' }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted">GST Number</label>
                                <div>{{ provider.gst_number || '—' }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted">PAN</label>
                                <div>{{ provider.pan_number || '—' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Operating Hours -->
                <div class="card border-0 shadow-sm mb-4" v-if="provider.operating_hours">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-bold">Operating Hours</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div
                                v-for="(hours, day) in provider.operating_hours"
                                :key="day"
                                class="col-md-3 col-6"
                            >
                                <div class="small">
                                    <span class="fw-semibold text-capitalize">{{ day }}:</span>
                                    <span v-if="hours.open" class="text-muted">{{ hours.open }} - {{ hours.close }}</span>
                                    <span v-else class="text-danger">Closed</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Menu Items -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between">
                        <h6 class="mb-0 fw-bold">Menu Items</h6>
                        <span class="badge bg-light text-dark">{{ provider.menu_items?.length || 0 }} items</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Item Name</th>
                                    <th>Price</th>
                                    <th>Category</th>
                                    <th>Veg</th>
                                    <th>Available</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in provider.menu_items?.slice(0, 10)" :key="item.id">
                                    <td class="fw-semibold">{{ item.name }}</td>
                                    <td>{{ formatCurrency(item.price) }}</td>
                                    <td><span class="badge bg-light text-dark">{{ item.category || '—' }}</span></td>
                                    <td>
                                        <span class="badge bg-success-subtle text-success">
                                            <i class="ri-leaf-line me-1"></i> Veg
                                        </span>
                                    </td>
                                    <td>
                                        <span v-if="item.is_available" class="badge bg-success-subtle text-success">Yes</span>
                                        <span v-else class="badge bg-danger-subtle text-danger">No</span>
                                    </td>
                                </tr>
                                <tr v-if="!provider.menu_items?.length">
                                    <td colspan="5" class="text-center text-muted py-3">No menu items added yet</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between">
                        <h6 class="mb-0 fw-bold">Recent Orders</h6>
                        <Link :href="`/admin/food/orders?provider_id=${provider.id}`" class="text-primary small text-decoration-none">View All</Link>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="order in provider.recent_orders?.slice(0, 5)" :key="order.id">
                                    <td><Link :href="`/admin/food/orders/${order.id}`" class="text-decoration-none">#{{ order.order_number }}</Link></td>
                                    <td>{{ order.customer?.name || '—' }}</td>
                                    <td class="fw-semibold">{{ formatCurrency(order.total_amount) }}</td>
                                    <td><span class="badge rounded-pill" :class="orderStatusClass(order.status)">{{ order.status }}</span></td>
                                    <td><small class="text-muted">{{ formatDate(order.created_at) }}</small></td>
                                </tr>
                                <tr v-if="!provider.recent_orders?.length">
                                    <td colspan="5" class="text-center text-muted py-3">No orders yet</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Verification & Payment -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-bold">Verification & Payment</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label small text-muted">Bank Account</label>
                            <div class="fw-semibold">{{ provider.bank_account_number || '—' }}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted">Bank Name</label>
                            <div>{{ provider.bank_name || '—' }}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted">IFSC Code</label>
                            <div>{{ provider.ifsc_code || '—' }}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted">UPI ID</label>
                            <div>{{ provider.upi_id || '—' }}</div>
                        </div>
                        <div>
                            <label class="form-label small text-muted">Commission Rate</label>
                            <div class="fw-semibold text-primary">{{ provider.commission_rate || 10 }}%</div>
                        </div>
                    </div>
                </div>

                <!-- Reviews -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-bold">Reviews</h6>
                    </div>
                    <div class="card-body">
                        <div v-for="review in provider.reviews?.slice(0, 5)" :key="review.id" class="mb-3 pb-3 border-bottom">
                            <div class="d-flex justify-content-between">
                                <div class="fw-semibold">{{ review.user?.name }}</div>
                                <span class="text-warning">★ {{ review.rating }}</span>
                            </div>
                            <p class="text-muted small mb-0 mt-1">{{ review.comment || 'No comment' }}</p>
                            <small class="text-muted">{{ formatDate(review.created_at) }}</small>
                        </div>
                        <div v-if="!provider.reviews?.length" class="text-center text-muted py-3">No reviews yet</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
