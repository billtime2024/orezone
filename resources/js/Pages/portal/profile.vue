<script>
import { Link, Head, useForm, router } from '@inertiajs/vue3';

export default {
    components: { Link, Head },
    props: {
        user: Object,
        success: String,
    },
    data() {
        return {
            form: useForm({
                name: this.user.name || '',
                email: this.user.email || '',
            }),
        };
    },
    methods: {
        logout() {
            router.post('/logout');
        },
        submit() {
            this.form.put('/user/profile-information');
        },
    },
};
</script>

<template>
    <Head title="Profile - orezone" />

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
                            <li><Link href="/portal/profile" class="dropdown-item active">Profile</Link></li>
                            <li><Link href="/portal/trips" class="dropdown-item">My Trips</Link></li>
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
                    <span style="font-size: 2rem;">👤</span>
                    <div>
                        <h1 class="h4 fw-bold mb-0">Profile</h1>
                        <p class="mb-0 opacity-75 small">Update your account information</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="container py-4">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <!-- Success Message -->
                    <div v-if="success" class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ success }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-4">Edit Profile</h5>
                            <form @submit.prevent="submit">
                                <!-- Name -->
                                <div class="mb-3">
                                    <label for="name" class="form-label fw-semibold">Name</label>
                                    <input
                                        id="name"
                                        v-model="form.name"
                                        type="text"
                                        class="form-control"
                                        :class="{ 'is-invalid': form.errors.name }"
                                        required
                                    />
                                    <div v-if="form.errors.name" class="invalid-feedback">{{ form.errors.name }}</div>
                                </div>

                                <!-- Email -->
                                <div class="mb-4">
                                    <label for="email" class="form-label fw-semibold">Email</label>
                                    <input
                                        id="email"
                                        v-model="form.email"
                                        type="email"
                                        class="form-control"
                                        :class="{ 'is-invalid': form.errors.email }"
                                        required
                                    />
                                    <div v-if="form.errors.email" class="invalid-feedback">{{ form.errors.email }}</div>
                                </div>

                                <!-- Submit -->
                                <div class="d-flex justify-content-end">
                                    <button
                                        type="submit"
                                        class="btn btn-primary"
                                        :disabled="form.processing"
                                    >
                                        <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                                        Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- User Info Card -->
                    <div class="card border-0 shadow-sm mt-4">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-3">Account Details</h5>
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <p class="text-muted small mb-1">Phone</p>
                                    <p class="fw-semibold mb-0">{{ user.phone || '—' }}</p>
                                </div>
                                <div class="col-sm-6">
                                    <p class="text-muted small mb-1">Status</p>
                                    <span class="badge bg-success">{{ user.status || 'active' }}</span>
                                </div>
                                <div class="col-sm-6">
                                    <p class="text-muted small mb-1">Email Verified</p>
                                    <span :class="['badge', user.email_verified_at ? 'bg-success' : 'bg-warning text-dark']">
                                        {{ user.email_verified_at ? 'Verified' : 'Not Verified' }}
                                    </span>
                                </div>
                                <div class="col-sm-6">
                                    <p class="text-muted small mb-1">Member Since</p>
                                    <p class="fw-semibold mb-0">{{ user.created_at ? new Date(user.created_at).toLocaleDateString() : '—' }}</p>
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
