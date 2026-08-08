<script>
import { Link, Head, router } from '@inertiajs/vue3';

export default {
    components: { Link, Head },
    props: {
        user: Object,
    },
    methods: {
        logout() {
            router.post('/logout');
        },
    },
};
</script>

<template>
    <Head title="My Portal - orezone" />

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <Link href="/" class="navbar-brand fw-bold fs-4 text-white">orezone</Link>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#portalNav" aria-controls="portalNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="portalNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="bg-white text-primary rounded-circle d-inline-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 14px;">
                                {{ user.name ? user.name.charAt(0).toUpperCase() : 'U' }}
                            </span>
                            <span class="d-none d-lg-inline">{{ user.name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><span class="dropdown-item-text text-muted small">{{ user.email }}</span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><Link href="/portal/profile" class="dropdown-item">Profile</Link></li>
                            <li><Link href="/portal/trips" class="dropdown-item">My Trips</Link></li>
                            <li><Link href="/portal/bookings" class="dropdown-item">My Bookings</Link></li>
                            <li><Link href="/portal/wallet" class="dropdown-item">Wallet</Link></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <button @click="logout" class="dropdown-item text-danger">Logout</button>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="bg-light min-vh-100">
        <!-- Welcome Section -->
        <div class="bg-primary text-white py-5">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="bg-white text-primary rounded-circle d-inline-flex align-items-center justify-content-center fw-bold" style="width: 56px; height: 56px; font-size: 22px;">
                                {{ user.name ? user.name.charAt(0).toUpperCase() : 'U' }}
                            </span>
                            <div>
                                <h1 class="h3 fw-bold mb-0">Welcome, {{ user.name }}!</h1>
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
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="container py-5">
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

                <!-- Logout -->
                <div class="col-sm-6 col-lg-4">
                    <button @click="logout" class="btn w-100 h-100 border-0 text-decoration-none p-0" style="background: none;">
                        <div class="card border-0 shadow-sm h-100 text-center p-4 hover-shadow">
                            <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 72px; height: 72px;">
                                <span class="display-5">🚪</span>
                            </div>
                            <h5 class="fw-bold text-dark mb-2">Logout</h5>
                            <p class="text-muted small mb-0">Sign out of your orezone account</p>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container text-center">
            <p class="mb-0 small opacity-75">&copy; 2026 orezone. All rights reserved.</p>
        </div>
    </footer>
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
