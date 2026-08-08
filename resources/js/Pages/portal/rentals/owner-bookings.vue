<script>
import { Link, Head, useForm } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/portal-layout.vue';

export default {
    components: { Link, Head, PortalLayout },
    layout: PortalLayout,
    props: { bookings: Object },
    methods: {
        statusLabel(status) {
            const labels = { pending: 'Pending', confirmed: 'Confirmed', active: 'Active', completed: 'Completed', cancelled_by_guest: 'Cancelled', rejected: 'Rejected', expired: 'Expired' };
            return labels[status] || status;
        },
        statusClass(status) {
            switch (status) {
                case 'pending': return 'bg-warning-subtle text-warning';
                case 'confirmed': return 'bg-info-subtle text-info';
                case 'active': return 'bg-success-subtle text-success';
                case 'completed': return 'bg-secondary-subtle text-secondary';
                default: return 'bg-danger-subtle text-danger';
            }
        },
        formatCurrency(amount) {
            return amount ? '₹' + Number(amount).toLocaleString('en-IN') : '—';
        },
        formatDate(d) {
            return d ? new Date(d).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' }) : '—';
        },
        confirmBooking(id) {
            if (confirm('Confirm this booking?')) {
                useForm().post(`/portal/rentals-bookings/${id}/confirm`);
            }
        },
        rejectBooking(id) {
            const reason = prompt('Reason for rejection:');
            if (reason) {
                useForm({ reason }).post(`/portal/rentals-bookings/${id}/reject`);
            }
        },
    },
};
</script>

<template>
    <Head title="Owner Bookings" />
    <div class="container-fluid">
        <h4 class="mb-4 fw-bold">Owner — Rental Bookings</h4>
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Listing</th>
                            <th>Guest</th>
                            <th>Dates</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="b in bookings.data" :key="b.id">
                            <td>{{ b.listing?.title || `#${b.rental_listing_id}` }}</td>
                            <td>{{ b.guest?.name }}</td>
                            <td><small>{{ formatDate(b.check_in) }} → {{ formatDate(b.check_out) }}</small></td>
                            <td class="fw-semibold">{{ formatCurrency(b.total_amount) }}</td>
                            <td><span class="badge" :class="statusClass(b.status)">{{ statusLabel(b.status) }}</span></td>
                            <td>
                                <div v-if="b.status === 'pending'" class="d-flex gap-1">
                                    <button class="btn btn-sm btn-success" @click="confirmBooking(b.id)">Confirm</button>
                                    <button class="btn btn-sm btn-outline-danger" @click="rejectBooking(b.id)">Reject</button>
                                </div>
                                <span v-else class="text-muted">—</span>
                            </td>
                        </tr>
                        <tr v-if="!bookings.data?.length"><td colspan="6" class="text-center text-muted py-4">No bookings yet</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
