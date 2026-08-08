<script>
import { Link, Head, router } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/portal-layout.vue';

export default {
    components: { Link, Head, PortalLayout },
    props: {
        provider: Object,
        items: Object,
    },
    methods: {
        formatCurrency(amount) {
            return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(amount || 0);
        },
        toggleAvailability(item) {
            router.post(`/portal/food-provider/menu/${item.id}/toggle-availability`, {}, {
                preserveScroll: true,
            });
        },
        deleteItem(item) {
            if (confirm(`Delete "${item.name}"? This cannot be undone.`)) {
                router.delete(`/portal/food-provider/menu/${item.id}`);
            }
        },
    },
};
</script>

<template>
    <Head title="My Menu - orezone Provider" />
    <PortalLayout>
        <!-- Header -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold text-dark mb-1">My Menu</h4>
                <p class="text-muted mb-0">Manage your food items and pricing</p>
            </div>
            <Link href="/portal/food-provider/menu/create" class="btn" style="background-color: #2E7D5B; color: white;">
                <i class="ri-add-line me-1"></i> Add Item
            </Link>
        </div>

        <!-- Items Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-3">Item</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Prep Time</th>
                                <th>Orders</th>
                                <th>Rating</th>
                                <th>Status</th>
                                <th class="px-3 text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="items.data?.length === 0">
                                <td colspan="8" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="ri-restaurant-line fs-1 d-block mb-2"></i>
                                        <p class="mb-2">No menu items yet</p>
                                        <Link href="/portal/food-provider/menu/create" class="btn btn-sm" style="background-color: #2E7D5B; color: white;">
                                            Add Your First Item
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                            <tr v-for="item in items.data" :key="item.id">
                                <td class="px-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <img v-if="item.image_url" :src="item.image_url" alt=""
                                            class="rounded" style="width: 48px; height: 48px; object-fit: cover;">
                                        <div v-else class="rounded d-flex align-items-center justify-content-center"
                                            style="width: 48px; height: 48px; background: rgba(46,125,91,0.1);">
                                            <span>🍽️</span>
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark">{{ item.name }}</div>
                                            <small class="text-muted d-block text-truncate" style="max-width: 200px;">
                                                {{ item.description || 'No description' }}
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ item.category?.name || 'Uncategorized' }}</span>
                                </td>
                                <td>
                                    <div v-if="item.has_discount" class="d-flex flex-column">
                                        <span class="text-decoration-line-through text-muted small">{{ formatCurrency(item.price) }}</span>
                                        <span class="fw-semibold" style="color: #2E7D5B;">{{ formatCurrency(item.discount_price) }}</span>
                                    </div>
                                    <span v-else class="fw-semibold">{{ formatCurrency(item.price) }}</span>
                                </td>
                                <td>{{ item.preparation_time_min || '—' }} min</td>
                                <td>{{ item.total_orders || 0 }}</td>
                                <td>
                                    <span v-if="item.avg_rating">
                                        ⭐ {{ Number(item.avg_rating).toFixed(1) }}
                                    </span>
                                    <span v-else class="text-muted">—</span>
                                </td>
                                <td>
                                    <span class="badge" :class="item.is_available ? 'bg-success' : 'bg-secondary'">
                                        {{ item.is_available ? 'Available' : 'Unavailable' }}
                                    </span>
                                </td>
                                <td class="px-3 text-end">
                                    <div class="btn-group btn-group-sm">
                                        <Link :href="`/portal/food-provider/menu/${item.id}/edit`"
                                            class="btn btn-outline-success" title="Edit">
                                            <i class="ri-edit-line"></i>
                                        </Link>
                                        <button class="btn btn-outline-danger" @click="deleteItem(item)" title="Delete">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="items.last_page > 1" class="d-flex justify-content-center mt-4">
            <nav>
                <ul class="pagination">
                    <li v-for="page in items.last_page" :key="page" class="page-item"
                        :class="{ active: page === items.current_page }">
                        <Link :href="items.path + '?page=' + page" class="page-link">{{ page }}</Link>
                    </li>
                </ul>
            </nav>
        </div>
    </PortalLayout>
</template>
