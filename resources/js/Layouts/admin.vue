<script>
import { Link } from '@inertiajs/vue3';

export default {
    components: { Link },
    data() {
        return {
            sidebarOpen: true,
            navItems: [
                { label: 'Dashboard', href: '/admin', icon: 'ri-dashboard-line' },
                { label: 'Users', href: '/admin/users', icon: 'ri-user-line' },
                { label: 'Verifications', href: '/admin/verifications', icon: 'ri-file-list-3-line' },
                { label: 'Vehicles', href: '/admin/vehicles', icon: 'ri-roadster-line' },
            ],
        };
    },
    computed: {
        currentPath() {
            return window.location.pathname;
        },
    },
    methods: {
        isActive(href) {
            if (href === '/admin') return this.currentPath === '/admin';
            return this.currentPath.startsWith(href);
        },
        toggleSidebar() {
            this.sidebarOpen = !this.sidebarOpen;
        },
    },
};
</script>

<template>
    <div id="layout-wrapper" class="d-flex" style="min-height: 100vh;">
        <!-- ========== Left Sidebar ========== -->
        <nav
            class="sidebar shadow-sm border-end"
            :class="{ 'sidebar-open': sidebarOpen }"
            style="width: 260px; min-width: 260px; background: #ffffff; transition: all 0.3s;"
        >
            <!-- Logo -->
            <div class="p-3 border-bottom d-flex align-items-center justify-content-between">
                <Link href="/" class="text-decoration-none">
                    <span class="fw-bold fs-5 text-primary">🚗 orezone</span>
                    <small class="d-block text-muted small">Admin Panel</small>
                </Link>
                <button class="btn btn-sm btn-light" @click="toggleSidebar">
                    <i class="ri-menu-line"></i>
                </button>
            </div>

            <!-- Navigation -->
            <div class="p-2">
                <ul class="nav flex-column">
                    <li class="nav-item" v-for="item in navItems" :key="item.href">
                        <Link
                            :href="item.href"
                            class="nav-link d-flex align-items-center gap-2 rounded-2"
                            :class="{ 'active bg-primary bg-opacity-10 text-primary fw-semibold': isActive(item.href) }"
                        >
                            <i :class="item.icon" class="fs-5"></i>
                            <span>{{ item.label }}</span>
                        </Link>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- ========== Main Content ========== -->
        <div class="flex-grow-1 d-flex flex-column" style="background: #f8f9fa;">
            <!-- Top Navbar -->
            <header class="bg-white border-bottom px-4 py-2 d-flex align-items-center justify-content-between shadow-sm" style="min-height: 56px;">
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-sm btn-light d-lg-none" @click="toggleSidebar">
                        <i class="ri-menu-line fs-5"></i>
                    </button>
                    <h5 class="mb-0 fw-semibold text-dark">orezone Admin</h5>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-primary-subtle text-primary">
                        <i class="ri-admin-line me-1"></i> Administrator
                    </span>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-grow-1 p-4">
                <slot />
            </main>

            <!-- Footer -->
            <footer class="bg-white border-top text-center py-3 px-4">
                <small class="text-muted">
                    &copy; {{ new Date().getFullYear() }} orezone — Community Sharing Platform
                </small>
            </footer>
        </div>
    </div>
</template>

<style scoped>
.nav-link {
    color: #495057;
    padding: 0.5rem 0.75rem;
    margin-bottom: 2px;
    transition: all 0.2s;
}
.nav-link:hover {
    background: #f1f3f5;
    color: #0d6efd;
}
.nav-link.active {
    border-left: 3px solid #0d6efd;
}
</style>
