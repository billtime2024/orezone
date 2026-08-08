<script>
import { Link, router, usePage } from '@inertiajs/vue3';

export default {
    components: { Link },
    data() {
        return {
            sidebarOpen: true,
            userDropdownOpen: false,
            openMenus: ['ride-module', 'rental-module', 'food-module'],
            navItems: [
                { label: 'Dashboard', href: '/portal', icon: 'ri-dashboard-line' },
                {
                    label: 'Ride Module',
                    href: '/portal/trips',
                    icon: 'ri-roadster-line',
                    children: [
                        { label: 'My Trips', href: '/portal/trips' },
                        { label: 'Search Trips', href: '/portal/trips/search' },
                        { label: 'My Bookings', href: '/portal/bookings' },
                    ],
                },
                {
                    label: 'Rentals',
                    href: '/portal/rentals',
                    icon: 'ri-home-4-line',
                    children: [
                        { label: 'Browse Rentals', href: '/portal/rentals' },
                        { label: 'My Listings', href: '/portal/rentals/my' },
                        { label: 'My Bookings', href: '/portal/rentals-bookings' },
                        { label: 'Owner Bookings', href: '/portal/owner/rentals-bookings' },
                    ],
                },
                {
                    label: 'Food Services',
                    href: '/portal/food',
                    icon: 'ri-restaurant-2-line',
                    children: [
                        { label: 'Browse Food', href: '/portal/food' },
                        { label: 'My Orders', href: '/portal/food/orders' },
                        { label: 'Cart', href: '/portal/food/cart' },
                        { label: 'Catering', href: '/portal/food/catering' },
                    ],
                },
                {
                    label: 'Provider Panel',
                    href: '/portal/food-provider',
                    icon: 'ri-restaurant-2-line',
                    children: [
                        { label: 'Dashboard', href: '/portal/food-provider' },
                        { label: 'My Menu', href: '/portal/food-provider/menu' },
                        { label: 'Orders', href: '/portal/food-provider/orders' },
                        { label: 'Catering', href: '/portal/food-provider/catering' },
                        { label: 'Profile', href: '/portal/food-provider/profile' },
                    ],
                },
                { label: 'Vehicles', href: '/portal/vehicles', icon: 'ri-car-line' },
                { label: 'Wallet', href: '/portal/wallet', icon: 'ri-wallet-3-line' },
                { label: 'Profile', href: '/portal/profile', icon: 'ri-user-line' },
            ],
        };
    },
    computed: {
        currentPath() {
            return window.location.pathname;
        },
        user() {
            return this.$page.props.auth?.user || this.$page.props.user;
        },
    },
    methods: {
        isActive(href) {
            if (href === '/portal') return this.currentPath === '/portal';
            return this.currentPath.startsWith(href);
        },
        isMenuActive(item) {
            if (item.children) {
                return item.children.some(child => this.isActive(child.href));
            }
            return this.isActive(item.href);
        },
        toggleSidebar() {
            this.sidebarOpen = !this.sidebarOpen;
        },
        toggleMenu(label) {
            const index = this.openMenus.indexOf(label);
            if (index > -1) {
                this.openMenus.splice(index, 1);
            } else {
                this.openMenus.push(label);
            }
        },
        isMenuOpen(label) {
            return this.openMenus.includes(label);
        },
        toggleUserDropdown() {
            this.userDropdownOpen = !this.userDropdownOpen;
        },
        closeUserDropdown() {
            this.userDropdownOpen = false;
        },
        logout() {
            router.post('/logout');
        },
    },
};
</script>

<template>
    <div id="layout-wrapper" class="d-flex" style="min-height: 100vh;">
        <!-- ========== Left Sidebar ========== -->
        <nav
            class="sidebar shadow-sm"
            :class="{ 'sidebar-open': sidebarOpen }"
            style="width: 260px; min-width: 260px; background: #1a1d21; transition: all 0.3s; border-right: 1px solid #2c2f33;"
        >
            <!-- Logo -->
            <div class="p-3 border-bottom d-flex align-items-center justify-content-between" style="border-color: #2c2f33 !important;">
                <Link href="/" class="text-decoration-none">
                    <span class="fw-bold fs-5" style="color: #ffffff;">🚗 orezone</span>
                    <small class="d-block small" style="color: #adb5bd;">Portal</small>
                </Link>
                <button class="btn btn-sm" style="color: #adb5bd; background: rgba(255,255,255,0.05);" @click="toggleSidebar">
                    <i class="ri-menu-line"></i>
                </button>
            </div>

            <!-- Navigation -->
            <div class="p-2">
                <ul class="nav flex-column">
                    <template v-for="item in navItems" :key="item.href">
                        <!-- Simple link (no children) -->
                        <li class="nav-item" v-if="!item.children">
                            <Link
                                :href="item.href"
                                class="nav-link d-flex align-items-center gap-2 rounded-2"
                                :class="{ 'sidebar-nav-active': isActive(item.href) }"
                            >
                                <i :class="item.icon" class="fs-5"></i>
                                <span>{{ item.label }}</span>
                            </Link>
                        </li>

                        <!-- Expandable menu with children -->
                        <li class="nav-item" v-else>
                            <button
                                class="nav-link d-flex align-items-center gap-2 rounded-2 w-100 text-start"
                                :class="{ 'sidebar-nav-active': isMenuActive(item) }"
                                @click="toggleMenu(item.label)"
                                style="border: none; background: none;"
                            >
                                <i :class="item.icon" class="fs-5"></i>
                                <span class="flex-grow-1">{{ item.label }}</span>
                                <i
                                    class="ri-arrow-down-s-line transition-transform"
                                    :style="{ transform: isMenuOpen(item.label) ? 'rotate(180deg)' : 'rotate(0deg)', transition: 'transform 0.2s' }"
                                ></i>
                            </button>
                            <ul
                                class="nav flex-column ms-3"
                                v-show="isMenuOpen(item.label)"
                                style="border-left: 2px solid #2c2f33; margin-left: 1.25rem;"
                            >
                                <li class="nav-item" v-for="child in item.children" :key="child.href">
                                    <Link
                                        :href="child.href"
                                        class="nav-link d-flex align-items-center gap-2 rounded-2"
                                        :class="{ 'sidebar-nav-active': isActive(child.href) }"
                                        style="padding-left: 0.75rem; font-size: 0.9rem;"
                                    >
                                        <i class="ri-checkbox-blank-circle-fill" style="font-size: 6px;"></i>
                                        <span>{{ child.label }}</span>
                                    </Link>
                                </li>
                            </ul>
                        </li>
                    </template>
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
                    <h5 class="mb-0 fw-semibold text-dark">orezone Portal</h5>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-success-subtle text-success">
                        <i class="ri-roadster-line me-1"></i> Ride Sharing
                    </span>

                    <!-- User Dropdown -->
                    <div class="dropdown" style="position: relative;">
                        <button
                            class="btn btn-light d-flex align-items-center gap-2"
                            @click="toggleUserDropdown"
                            style="border: 1px solid #e0e0e0;"
                        >
                            <div
                                class="rounded-circle text-white d-flex align-items-center justify-content-center"
                                style="width: 32px; height: 32px; font-size: 14px; background-color: var(--orezone-primary);"
                            >
                                {{ user?.name ? user.name.charAt(0).toUpperCase() : 'U' }}
                            </div>
                            <span class="fw-medium text-dark d-none d-md-inline">{{ user?.name || 'User' }}</span>
                            <i class="ri-arrow-down-s-line"></i>
                        </button>

                        <!-- Dropdown Menu -->
                        <div
                            v-if="userDropdownOpen"
                            class="dropdown-menu dropdown-menu-end shadow-sm show"
                            style="position: absolute; right: 0; top: 100%; min-width: 200px; z-index: 1050;"
                        >
                            <div class="px-3 py-2 border-bottom">
                                <div class="fw-semibold text-dark">{{ user?.name || 'User' }}</div>
                                <small class="text-muted">{{ user?.email || '' }}</small>
                            </div>
                            <Link href="/portal/profile" class="dropdown-item d-flex align-items-center gap-2" @click="closeUserDropdown">
                                <i class="ri-user-settings-line"></i> Profile
                            </Link>
                            <Link href="/portal/wallet" class="dropdown-item d-flex align-items-center gap-2" @click="closeUserDropdown">
                                <i class="ri-wallet-3-line"></i> Wallet
                            </Link>
                            <div class="dropdown-divider"></div>
                            <button class="dropdown-item d-flex align-items-center gap-2 text-danger" @click="logout">
                                <i class="ri-logout-box-r-line"></i> Logout
                            </button>
                        </div>
                    </div>
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

    <!-- Click outside to close dropdown -->
    <div
        v-if="userDropdownOpen"
        style="position: fixed; inset: 0; z-index: 1040;"
        @click="closeUserDropdown"
    ></div>
</template>

<style scoped>
.sidebar :deep(.nav-link) {
    color: #adb5bd;
    padding: 0.5rem 0.75rem;
    margin-bottom: 2px;
    transition: all 0.2s;
}
.sidebar :deep(.nav-link:hover) {
    background: rgba(255, 255, 255, 0.05);
    color: #ffffff;
}
.sidebar :deep(.nav-link.sidebar-nav-active) {
    background: rgba(255, 255, 255, 0.1);
    color: #ffffff;
    font-weight: 600;
}
.sidebar :deep(.nav-link.sidebar-nav-active i) {
    color: #ffffff;
}
</style>
