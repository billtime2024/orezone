<script>
import { Link, Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/admin.vue';

export default {
    components: { Link, Head, AdminLayout },
    layout: AdminLayout,
    props: {
        reviews: Object,
    },
    data() {
        return {
            ratingFilter: this.$page.props.reviews?.query?.rating || '',
        };
    },
    methods: {
        applyFilters() {
            this.$inertia.get('/admin/reviews', {
                rating: this.ratingFilter || undefined,
            }, { preserveState: true });
        },
        clearFilters() {
            this.ratingFilter = '';
            this.$inertia.get('/admin/reviews');
        },
        renderStars(rating) {
            return '★'.repeat(rating) + '☆'.repeat(5 - rating);
        },
        formatDate(dateStr) {
            if (!dateStr) return '—';
            return new Date(dateStr).toLocaleDateString('en-IN', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
            });
        },
    },
};
</script>

<template>
    <Head title="Manage Reviews" />

    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-1 fw-bold">Reviews Management</h4>
                <p class="text-muted mb-0">View and manage user reviews and ratings</p>
            </div>
            <span class="badge bg-primary fs-6">
                {{ reviews.total }} Total Reviews
            </span>
        </div>

        <!-- Filters -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Filter by Rating</label>
                        <select v-model="ratingFilter" class="form-select" @change="applyFilters">
                            <option value="">All Ratings</option>
                            <option value="5">★★★★★ (5)</option>
                            <option value="4">★★★★☆ (4)</option>
                            <option value="3">★★★☆☆ (3)</option>
                            <option value="2">★★☆☆☆ (2)</option>
                            <option value="1">★☆☆☆☆ (1)</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button class="btn btn-primary" @click="applyFilters">
                            <i class="ri-search-line me-1"></i> Filter
                        </button>
                        <button class="btn btn-outline-secondary" @click="clearFilters">
                            <i class="ri-close-line me-1"></i> Clear
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reviews Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" style="width: 60px;">ID</th>
                                <th>Reviewer</th>
                                <th>Reviewee</th>
                                <th>Trip</th>
                                <th>Rating</th>
                                <th>Comment</th>
                                <th>Visible</th>
                                <th>Date</th>
                                <th class="text-end pe-3" style="width: 100px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="reviews.data.length === 0">
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="ri-star-line fs-1 d-block mb-2"></i>
                                    No reviews found matching your criteria.
                                </td>
                            </tr>
                            <tr v-for="review in reviews.data" :key="review.id">
                                <td class="ps-3">
                                    <span class="text-muted">#{{ review.id }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-sm rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center">
                                            <span class="text-primary fw-semibold small">
                                                {{ review.reviewer?.name ? review.reviewer.name.charAt(0).toUpperCase() : '?' }}
                                            </span>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ review.reviewer?.name || 'Unknown' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-sm rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center">
                                            <span class="text-warning fw-semibold small">
                                                {{ review.reviewee?.name ? review.reviewee.name.charAt(0).toUpperCase() : '?' }}
                                            </span>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ review.reviewee?.name || 'Unknown' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-muted small">Trip #{{ review.trip_id }}</span>
                                </td>
                                <td>
                                    <span class="text-warning fs-6">{{ renderStars(review.rating) }}</span>
                                </td>
                                <td>
                                    <span class="text-muted small text-truncate d-inline-block" style="max-width: 200px;" :title="review.comment">
                                        {{ review.comment || '—' }}
                                    </span>
                                </td>
                                <td>
                                    <span v-if="review.is_visible" class="badge bg-success-subtle text-success">
                                        <i class="ri-eye-line me-1"></i> Yes
                                    </span>
                                    <span v-else class="badge bg-secondary-subtle text-secondary">
                                        <i class="ri-eye-off-line me-1"></i> No
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted small">{{ formatDate(review.created_at) }}</span>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                            <i class="ri-more-2-fill"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="#">
                                                    <i class="ri-eye-line me-2"></i> View Details
                                                </a>
                                            </li>
                                            <li v-if="review.is_visible">
                                                <a class="dropdown-item text-danger" href="#">
                                                    <i class="ri-eye-off-line me-2"></i> Hide Review
                                                </a>
                                            </li>
                                            <li v-else>
                                                <a class="dropdown-item text-success" href="#">
                                                    <i class="ri-eye-line me-2"></i> Show Review
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="d-flex align-items-center justify-content-between mt-4" v-if="reviews.last_page > 1">
            <div class="text-muted small">
                Showing {{ reviews.from }} to {{ reviews.to }} of {{ reviews.total }} reviews
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item" :class="{ disabled: !reviews.prev_page_url }">
                        <Link
                            :href="reviews.prev_page_url || '#'"
                            class="page-link"
                            preserve-state
                        >
                            <i class="ri-arrow-left-s-line"></i>
                        </Link>
                    </li>
                    <li
                        v-for="page in reviews.last_page"
                        :key="page"
                        class="page-item"
                        :class="{ active: page === reviews.current_page }"
                    >
                        <Link
                            :href="'/admin/reviews?page=' + page + (ratingFilter ? '&rating=' + ratingFilter : '')"
                            class="page-link"
                            preserve-state
                        >
                            {{ page }}
                        </Link>
                    </li>
                    <li class="page-item" :class="{ disabled: !reviews.next_page_url }">
                        <Link
                            :href="reviews.next_page_url || '#'"
                            class="page-link"
                            preserve-state
                        >
                            <i class="ri-arrow-right-s-line"></i>
                        </Link>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</template>

<style scoped>
.avatar-sm {
    width: 36px;
    height: 36px;
    font-size: 14px;
}
</style>
