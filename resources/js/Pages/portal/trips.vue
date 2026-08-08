<script>
import { Link, Head, router } from '@inertiajs/vue3';

export default {
    components: { Link, Head },
    props: {
        user: Object,
        trips: Array,
    },
    methods: {
        logout() {
            router.post('/logout');
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

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <Link href="/" class="navbar-brand fw-bold fs-4 text-white">orezone</Link>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#portalNav">
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
                            <li><Link href="/portal" class="dropdown-item">Dashboard</Link></li>
                            <li><Link href="/portal/profile" class="dropdown-item">Profile</Link></li>
                            <li><Link href="/portal/trips" class="dropdown-item active">My Trips</Link></li>
                            <li><Link href="/portal/bookings" class="dropdown-item">My Bookings</Link></li>
                            <li><Link href="/portal/wallet" class="dropdown-item">Wallet</Link></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><button @click="logout" class="dropdown-item text-danger">Logout</button></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="bg-light min-vh-100">
        <!-- Header -->
        <div class="bg-primary text-white py-4">
            <div class="container">
                <div class="d-flex align-items-center gap-3">
                    <span style="font-size: 2rem;">🚗</span>
                    <div>
                        <h1 class="h4 fw-bold mb-0">My Trips</h1>
                        <p class="mb-0 opacity-75 small">Trips you've offered as a host</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="container py-4">
            <!-- Empty State -->
            <div v-if="!trips || trips.length === 0" class="text-center py-5">
                <div class="mb-3" style="font-size: 4rem;">🚗</div>
                <h5 class="fw-bold text-muted">No trips yet</h5>
                <p class="text-muted">You haven't offered any trips. Create your first trip to start sharing rides!</p>
                <Link href="/portal" class="btn btn-primary">Back to Dashboard</Link>
            </div>

            <!-- Trip List -->
            <div v-else>
                <div class="row g-3">
                    <div v-for="trip in trips" :key="trip.id" class="col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <span :class="['badge', statusBadge(trip.status)]">
                                        {{ trip.status }}
                                    </span>
                                    <small class="text-muted">#{{ trip.id }}</small>
                                </div>
                                <div class="mb-2">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <span class="text-success fw-bold">●</span>
                                        <span class="fw-semibold">{{ trip.origin_name }}</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-danger fw-bold">●</span>
                                        <span class="fw-semibold">{{ trip.destination_name }}</span>
                                    </div>
                                </div>
                                <hr class="my-2">
                                <div class="d-flex justify-content-between text-muted small">
                                    <span>📅 {{ formatDate(trip.departure_at) }}</span>
                                    <span>💺 {{ trip.available_seats }}/{{ trip.total_seats }} seats</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-dark text-white py-4">
        <div class="container text-center">
            <p class="mb-0 small opacity-75">&copy; 2026 orezone. All rights reserved.</p>
        </div>
    </footer>
</template>
