<script>
import { Link, Head, useForm } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/portal-layout.vue';

export default {
    components: { Link, Head, PortalLayout },
    layout: PortalLayout,
    props: { listings: Object },
    methods: {
        typeLabel(type) {
            return { house: '🏠', car: '🚗', commercial: '🏢', room: '🛏️' }[type] || '📦';
        },
        statusClass(status) {
            return status === 'active' ? 'bg-success-subtle text-success' : status === 'draft' ? 'bg-secondary-subtle text-secondary' : 'bg-warning-subtle text-warning';
        },
        statusLabel(status) {
            return { active: 'Active', draft: 'Draft', paused: 'Paused', closed: 'Closed' }[status] || status;
        },
        formatCurrency(amount) {
            return amount ? '₹' + Number(amount).toLocaleString('en-IN') : '—';
        },
        toggleStatus(id) {
            useForm().post(`/portal/rentals/${id}/toggle-status`);
        },
        deleteListing(id) {
            if (confirm('Are you sure you want to delete this listing?')) {
                useForm().delete(`/portal/rentals/${id}`);
            }
        },
    },
};
</script>

<template>
    <Head title="My Listings" />
    <div class="container-fluid">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h4 class="mb-0 fw-bold">My Rental Listings</h4>
            <Link href="/portal/rentals/create" class="btn btn-primary">
                <i class="ri-add-line me-1"></i> Create Listing
            </Link>
        </div>

        <div v-if="$page.props.flash?.success" class="alert alert-success alert-dismissible fade show" role="alert">
            {{ $page.props.flash.success }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>

        <div class="row g-4">
            <div v-for="listing in listings.data" :key="listing.id" class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div v-if="listing.photos?.length" class="card-img-top" style="height: 160px; overflow: hidden;">
                        <img :src="listing.photos[0]" class="w-100 h-100" style="object-fit: cover;" />
                    </div>
                    <div v-else class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 160px;">
                        <span style="font-size: 48px;">{{ typeLabel(listing.rental_type) }}</span>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge" :class="statusClass(listing.status)">{{ statusLabel(listing.status) }}</span>
                            <span class="badge bg-light text-dark">{{ listing.rental_type }}</span>
                        </div>
                        <h6 class="card-title fw-bold">{{ listing.title }}</h6>
                        <p class="text-muted small mb-1"><i class="ri-map-pin-line"></i> {{ listing.city }}</p>
                        <p class="fw-bold text-success mb-3">{{ formatCurrency(listing.price_per_unit) }}/{{ listing.price_unit }}</p>
                        <div class="mt-auto d-flex gap-2 flex-wrap">
                            <Link :href="`/portal/rentals/${listing.id}`" class="btn btn-sm btn-outline-primary">View</Link>
                            <Link :href="`/portal/rentals/${listing.id}/edit`" class="btn btn-sm btn-outline-secondary">Edit</Link>
                            <button class="btn btn-sm" :class="listing.status === 'active' ? 'btn-outline-warning' : 'btn-outline-success'" @click="toggleStatus(listing.id)">
                                {{ listing.status === 'active' ? 'Deactivate' : 'Activate' }}
                            </button>
                            <button class="btn btn-sm btn-outline-danger" @click="deleteListing(listing.id)">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="!listings.data?.length" class="text-center py-5">
            <p class="text-muted mb-3">No listings yet. Create your first rental listing!</p>
            <Link href="/portal/rentals/create" class="btn btn-primary">Create Listing</Link>
        </div>
    </div>
</template>
