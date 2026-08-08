<script>
import { Link, Head } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/portal-layout.vue';

export default {
    components: { Link, Head, PortalLayout },
    props: {
        user: Object,
    },
};
</script>

<template>
    <Head title="My Portal - orezone" />

    <PortalLayout>
        <!-- Welcome Section -->
        <div class="text-white rounded-4 p-4 mb-4" style="background-color: var(--orezone-primary);">
            <div class="d-flex align-items-center gap-3">
                <span class="bg-white rounded-circle d-inline-flex align-items-center justify-content-center fw-bold" style="width: 56px; height: 56px; font-size: 22px; color: var(--orezone-primary);">
                    {{ user.name ? user.name.charAt(0).toUpperCase() : 'U' }}
                </span>
                <div>
                    <h1 class="h4 fw-bold mb-0">Welcome, {{ user.name }}!</h1>
                    <p class="mb-0 opacity-75">Your orezone dashboard</p>
                </div>
            </div>
            <div class="d-flex gap-4 flex-wrap mt-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="opacity-75">📧</span>
                    <span class="small">{{ user.email }}</span>
                </div>
                <div v-if="user.phone" class="d-flex align-items-center gap-2">
                    <span class="opacity-75">📱</span>
                    <span class="small">{{ user.phone }}</span>
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="row g-4">
            <!-- My Trips -->
            <div class="col-sm-6 col-lg-4">
                <Link href="/portal/trips" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 text-center p-4 hover-shadow">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 72px; height: 72px;">
                            <span class="display-5">🚗</span>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">My Trips</h5>
                        <p class="text-muted small mb-0">View and manage the trips you've offered to share</p>
                    </div>
                </Link>
            </div>

            <!-- Search Trips -->
            <div class="col-sm-6 col-lg-4">
                <Link href="/portal/trips/search" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 text-center p-4 hover-shadow">
                        <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 72px; height: 72px;">
                            <span class="display-5">🔍</span>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">Search Trips</h5>
                        <p class="text-muted small mb-0">Find available rides to your destination</p>
                    </div>
                </Link>
            </div>

            <!-- My Bookings -->
            <div class="col-sm-6 col-lg-4">
                <Link href="/portal/bookings" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 text-center p-4 hover-shadow">
                        <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 72px; height: 72px;">
                            <span class="display-5">📋</span>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">My Bookings</h5>
                        <p class="text-muted small mb-0">Check your ride bookings and reservation status</p>
                    </div>
                </Link>
            </div>

            <!-- Food Services -->
            <div class="col-sm-6 col-lg-4">
                <Link href="/portal/food" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 text-center p-4 hover-shadow">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 72px; height: 72px; background-color: rgba(46,125,91,0.1);">
                            <span class="display-5">🥗</span>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">Food Services</h5>
                        <p class="text-muted small mb-0">Order delicious pure veg food from verified providers</p>
                    </div>
                </Link>
            </div>

            <!-- Vehicles -->
            <div class="col-sm-6 col-lg-4">
                <Link href="/portal/vehicles" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 text-center p-4 hover-shadow">
                        <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 72px; height: 72px;">
                            <span class="display-5">🚙</span>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">My Vehicles</h5>
                        <p class="text-muted small mb-0">Manage your registered vehicles for ride sharing</p>
                    </div>
                </Link>
            </div>

            <!-- Wallet -->
            <div class="col-sm-6 col-lg-4">
                <Link href="/portal/wallet" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 text-center p-4 hover-shadow">
                        <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 72px; height: 72px;">
                            <span class="display-5">💰</span>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">Wallet</h5>
                        <p class="text-muted small mb-0">Manage your wallet balance and transactions</p>
                    </div>
                </Link>
            </div>

            <!-- Profile -->
            <div class="col-sm-6 col-lg-4">
                <Link href="/portal/profile" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 text-center p-4 hover-shadow">
                        <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 72px; height: 72px;">
                            <span class="display-5">👤</span>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">Profile</h5>
                        <p class="text-muted small mb-0">Update your profile, documents, and preferences</p>
                    </div>
                </Link>
            </div>
        </div>
    </PortalLayout>
</template>

<style scoped>
.hover-shadow:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    transform: translateY(-2px);
    transition: all 0.2s ease;
}
.hover-shadow {
    transition: all 0.2s ease;
}
</style>
