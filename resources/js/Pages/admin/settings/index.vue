<script>
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/admin.vue';

export default {
    components: { Head, AdminLayout },
    layout: AdminLayout,
    props: {
        settings: Object,
    },
    data() {
        return {
            expandedGroups: Object.keys(this.settings || {}),
        };
    },
    methods: {
        toggleGroup(group) {
            const index = this.expandedGroups.indexOf(group);
            if (index > -1) {
                this.expandedGroups.splice(index, 1);
            } else {
                this.expandedGroups.push(group);
            }
        },
        isExpanded(group) {
            return this.expandedGroups.includes(group);
        },
        groupIcon(group) {
            switch (group) {
                case 'platform_fees': return 'ri-percent-line';
                case 'cancellation': return 'ri-close-circle-line';
                case 'search': return 'ri-search-line';
                case 'verification': return 'ri-shield-check-line';
                case 'features': return 'ri-settings-3-line';
                default: return 'ri-settings-line';
            }
        },
        groupLabel(group) {
            const labels = {
                platform_fees: 'Platform Fees',
                cancellation: 'Cancellation Policies',
                search: 'Search Settings',
                verification: 'Verification Settings',
                features: 'Feature Flags',
            };
            return labels[group] || group?.replace('_', ' ').toUpperCase();
        },
        formatValue(value) {
            if (value === null || value === undefined) return '—';
            if (typeof value === 'boolean') return value ? 'Yes' : 'No';
            return String(value);
        },
    },
};
</script>

<template>
    <Head title="Admin Settings" />

    <div class="container-fluid">
        <!-- Header -->
        <div class="mb-4">
            <h4 class="mb-1">Platform Settings</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="/admin">Dashboard</a></li>
                    <li class="breadcrumb-item active">Settings</li>
                </ol>
            </nav>
        </div>

        <!-- Settings Accordion -->
        <div class="accordion" id="settingsAccordion">
            <div v-for="(items, group) in settings" :key="group" class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" :class="{ collapsed: !isExpanded(group) }"
                        type="button" @click="toggleGroup(group)" :aria-expanded="isExpanded(group)">
                        <i :class="groupIcon(group)" class="me-2 fs-5"></i>
                        {{ groupLabel(group) }}
                        <span class="badge bg-primary-subtle text-primary ms-2">{{ items.length }} settings</span>
                    </button>
                </h2>
                <div class="accordion-collapse collapse" :class="{ show: isExpanded(group) }">
                    <div class="accordion-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 30%">Setting</th>
                                        <th style="width: 25%">Value</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in items" :key="item.id || item.key">
                                        <td>
                                            <div class="fw-semibold small">{{ item.key }}</div>
                                        </td>
                                        <td>
                                            <code class="bg-light px-2 py-1 rounded">{{ formatValue(item.value) }}</code>
                                        </td>
                                        <td class="text-muted small">{{ item.description || '—' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty state -->
        <div v-if="!settings || Object.keys(settings).length === 0" class="card">
            <div class="card-body text-center py-5">
                <i class="ri-settings-line fs-1 text-muted mb-3"></i>
                <h5 class="text-muted">No settings configured</h5>
                <p class="text-muted mb-0">Platform settings will appear here once configured.</p>
            </div>
        </div>
    </div>
</template>
