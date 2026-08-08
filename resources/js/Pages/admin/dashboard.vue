<script>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/admin.vue';

export default {
    components: { Head, Link, AdminLayout },
    layout: AdminLayout,
    props: {
        stats: Object,
    },
    data() {
        return {
            cards: [
                { label: 'Total Users', value: this.stats.total_users, icon: 'ri-user-line', color: 'primary', href: '/admin/users' },
                { label: 'Active Users', value: this.stats.active_users, icon: 'ri-user-follow-line', color: 'success', href: '/admin/users' },
                { label: 'Total Trips', value: this.stats.total_trips, icon: 'ri-route-line', color: 'info', href: '/admin/trips' },
                { label: 'Published Trips', value: this.stats.published_trips, icon: 'ri-roadster-line', color: 'warning', href: '/admin/trips' },
                { label: 'Total Bookings', value: this.stats.total_bookings, icon: 'ri-bookmark-line', color: 'danger', href: '/admin/bookings' },
                { label: 'Confirmed Bookings', value: this.stats.confirmed_bookings, icon: 'ri-check-double-line', color: 'success', href: '/admin/bookings' },
                { label: 'Total Vehicles', value: this.stats.total_vehicles, icon: 'ri-roadster-line', color: 'secondary', href: '/admin/vehicles' },
                { label: 'Pending Verifications', value: this.stats.pending_verifications, icon: 'ri-file-list-3-line', color: 'warning', href: '/admin/verifications' },
                { label: 'Total Wallets', value: this.stats.total_wallets, icon: 'ri-wallet-3-line', color: 'primary', href: '/admin/wallets' },
                { label: 'Total Balance', value: '₹' + Number(this.stats.total_balance).toLocaleString('en-IN'), icon: 'ri-money-rupee-circle-line', color: 'success', href: '/admin/wallets' },
                { label: 'Total Reviews', value: this.stats.total_reviews, icon: 'ri-star-line', color: 'warning', href: '/admin/reviews' },
                { label: 'Pending Reports', value: this.stats.pending_reports, icon: 'ri-flag-line', color: 'danger', href: '/admin/reports' },
                { label: 'Active SOS Alerts', value: this.stats.active_sos, icon: 'ri-alarm-warning-line', color: 'danger', href: '/admin/sos' },
                { label: 'Food Providers', value: this.stats.total_food_providers, icon: 'ri-restaurant-line', color: 'success', href: '/admin/food-providers' },
                { label: 'Pending Food Verification', value: this.stats.pending_food_verifications, icon: 'ri-file-list-3-line', color: 'warning', href: '/admin/food-providers' },
                { label: 'Food Orders', value: this.stats.total_food_orders, icon: 'ri-shopping-bag-line', color: 'info', href: '/admin/food-orders' },
                { label: 'Active Food Orders', value: this.stats.active_food_orders, icon: 'ri-fire-line', color: 'danger', href: '/admin/food-orders' },
                { label: 'Food Revenue', value: '₹' + Number(this.stats.total_food_revenue).toLocaleString('en-IN'), icon: 'ri-money-rupee-circle-line', color: 'success', href: '/admin/food-analytics' },
                { label: 'Catering Requests', value: this.stats.total_catering_requests, icon: 'ri-goblet-line', color: 'primary', href: '/admin/food-catering' },
            ],
        };
    },
};
</script>

<template>
    <Head title="Admin Dashboard" />

    <div>
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-1 fw-bold">Dashboard</h4>
                <p class="text-muted mb-0">Overview of platform activity</p>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-3">
            <div v-for="card in cards" :key="card.label" class="col-sm-6 col-lg-4 col-xl-3">
                <Link :href="card.href" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-muted mb-1 small">{{ card.label }}</p>
                                    <h4 class="fw-bold mb-0">{{ card.value }}</h4>
                                </div>
                                <div
                                    class="rounded-circle d-flex align-items-center justify-content-center"
                                    :class="`bg-${card.color}-subtle`"
                                    style="width: 48px; height: 48px;"
                                >
                                    <i :class="card.icon" class="fs-4" :style="`color: var(--bs-${card.color})`"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </Link>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="row g-3 mt-2">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">Quick Actions</h6>
                        <div class="d-flex flex-wrap gap-2">
                            <Link href="/admin/verifications" class="btn btn-outline-warning btn-sm">
                                <i class="ri-file-list-3-line me-1"></i> Review Verifications
                            </Link>
                            <Link href="/admin/reports" class="btn btn-outline-danger btn-sm">
                                <i class="ri-flag-line me-1"></i> Review Reports
                            </Link>
                            <Link href="/admin/sos" class="btn btn-outline-danger btn-sm">
                                <i class="ri-alarm-warning-line me-1"></i> SOS Alerts
                            </Link>
                            <Link href="/admin/users" class="btn btn-outline-primary btn-sm">
                                <i class="ri-user-line me-1"></i> Manage Users
                            </Link>
                            <Link href="/admin/trips" class="btn btn-outline-info btn-sm">
                                <i class="ri-route-line me-1"></i> Manage Trips
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
