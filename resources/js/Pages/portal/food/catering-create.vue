<script>
import { Link, Head, router } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/portal-layout.vue';

export default {
    components: { Link, Head, PortalLayout },
    props: {
        providers: Array,
    },
    data() {
        return {
            form: {
                provider_id: '',
                event_type: '',
                event_name: '',
                event_date: '',
                event_end_date: '',
                event_time: '',
                venue_address: '',
                guest_count: '',
                budget_min: '',
                budget_max: '',
                cuisine_preferences: [],
                dietary_requirements: ['pure_veg'],
                menu_description: '',
                special_requests: '',
                tasting_requested: false,
            },
            submitting: false,
            cuisineOptions: [
                'North Indian', 'South Indian', 'Gujarati', 'Rajasthani',
                'Maharashtrian', 'Bengali', 'Punjabi', 'Chaat',
                'Chinese', 'Continental', 'Italian', 'Mediterranean',
            ],
            dietaryOptions: [
                'pure_veg', 'jain', 'vegan', 'gluten_free',
                'no_onion_garlic', 'low_spice', 'high_spice',
            ],
        };
    },
    methods: {
        submit() {
            this.submitting = true;
            router.post('/portal/food/catering', this.form, {
                onFinish: () => { this.submitting = false; },
            });
        },
    },
};
</script>

<template>
    <Head title="Create Catering Request - orezone Food" />

    <PortalLayout>
        <!-- Breadcrumb -->
        <div class="mb-3">
            <Link href="/portal/food/catering" class="text-decoration-none" style="color: #2E7D5B;">
                <i class="ri-arrow-left-line"></i> Catering
            </Link>
            <span class="text-muted mx-2">/</span>
            <span class="text-dark fw-semibold">New Request</span>
        </div>

        <h4 class="fw-bold mb-4">Create Catering Request</h4>

        <form @submit.prevent="submit">
            <div class="row g-4">
                <!-- Left Column -->
                <div class="col-lg-8">
                    <!-- Event Details -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h6 class="fw-bold mb-0"><i class="ri-calendar-event-line me-2" style="color: #2E7D5B;"></i>Event Details</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Event Type *</label>
                                    <select v-model="form.event_type" class="form-select" required>
                                        <option value="">Select type</option>
                                        <option value="Wedding">Wedding</option>
                                        <option value="Birthday">Birthday Party</option>
                                        <option value="Corporate">Corporate Event</option>
                                        <option value="Festival">Festival Celebration</option>
                                        <option value="House Party">House Party</option>
                                        <option value="Religious">Religious Function</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Event Name *</label>
                                    <input v-model="form.event_name" type="text" class="form-control" placeholder="e.g., Sharma Family Wedding" required />
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Event Date *</label>
                                    <input v-model="form.event_date" type="date" class="form-control" required />
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">End Date (multi-day)</label>
                                    <input v-model="form.event_end_date" type="date" class="form-control" />
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Event Time *</label>
                                    <input v-model="form.event_time" type="text" class="form-control" placeholder="e.g., 7:00 PM - 11:00 PM" required />
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label small fw-semibold">Venue Address *</label>
                                    <textarea v-model="form.venue_address" class="form-control" rows="2" placeholder="Full venue address with landmarks" required></textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Guest Count *</label>
                                    <input v-model.number="form.guest_count" type="number" class="form-control" min="1" max="50000" placeholder="Number of guests" required />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Menu Preferences -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h6 class="fw-bold mb-0"><i class="ri-restaurant-line me-2" style="color: #2E7D5B;"></i>Menu & Preferences</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label small fw-semibold">Cuisine Preferences</label>
                                    <div class="d-flex flex-wrap gap-2">
                                        <button
                                            v-for="cuisine in cuisineOptions"
                                            :key="cuisine"
                                            type="button"
                                            class="btn btn-sm rounded-pill"
                                            :class="form.cuisine_preferences.includes(cuisine) ? 'text-white' : ''"
                                            :style="form.cuisine_preferences.includes(cuisine) ? 'background-color: #2E7D5B;' : 'border: 1px solid #dee2e6;'"
                                            @click="
                                                if (form.cuisine_preferences.includes(cuisine)) {
                                                    form.cuisine_preferences = form.cuisine_preferences.filter(c => c !== cuisine);
                                                } else {
                                                    form.cuisine_preferences.push(cuisine);
                                                }
                                            "
                                        >
                                            {{ cuisine }}
                                        </button>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-semibold">Dietary Requirements</label>
                                    <div class="d-flex flex-wrap gap-2">
                                        <button
                                            v-for="diet in dietaryOptions"
                                            :key="diet"
                                            type="button"
                                            class="btn btn-sm rounded-pill"
                                            :class="form.dietary_requirements.includes(diet) ? 'text-white' : ''"
                                            :style="form.dietary_requirements.includes(diet) ? 'background-color: #2E7D5B;' : 'border: 1px solid #dee2e6;'"
                                            @click="
                                                if (form.dietary_requirements.includes(diet)) {
                                                    form.dietary_requirements = form.dietary_requirements.filter(d => d !== diet);
                                                } else {
                                                    form.dietary_requirements.push(diet);
                                                }
                                            "
                                        >
                                            {{ diet.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                                        </button>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-semibold">Menu Description (Optional)</label>
                                    <textarea v-model="form.menu_description" class="form-control" rows="3" placeholder="Describe your ideal menu, preferred dishes, courses..."></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-semibold">Special Requests (Optional)</label>
                                    <textarea v-model="form.special_requests" class="form-control" rows="2" placeholder="Any special requirements, decorations, dietary needs..."></textarea>
                                </div>
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" v-model="form.tasting_requested" id="tastingCheck" />
                                        <label class="form-check-label" for="tastingCheck">I would like a tasting session before confirming</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-lg-4">
                    <!-- Budget & Provider -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h6 class="fw-bold mb-0"><i class="ri-money-rupee-circle-line me-2" style="color: #2E7D5B;"></i>Budget & Provider</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Minimum Budget (₹)</label>
                                <input v-model.number="form.budget_min" type="number" class="form-control" min="0" placeholder="e.g., 50000" />
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Maximum Budget (₹)</label>
                                <input v-model.number="form.budget_max" type="number" class="form-control" min="0" placeholder="e.g., 200000" />
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Preferred Provider (Optional)</label>
                                <select v-model="form.provider_id" class="form-select">
                                    <option value="">Open to all providers</option>
                                    <option v-for="p in providers" :key="p.id" :value="p.id">
                                        {{ p.business_name }} - {{ p.city }}
                                    </option>
                                </select>
                                <small class="text-muted">Leave empty to get quotes from multiple providers</small>
                            </div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="alert alert-info small mb-3" style="background: rgba(46,125,91,0.1); border-color: rgba(46,125,91,0.2); color: #2E7D5B;">
                                <i class="ri-information-line me-1"></i>
                                All food served is <strong>100% Pure Vegetarian</strong>. Verified providers will send you quotes within 24 hours.
                            </div>
                            <button type="submit" class="btn w-100 text-white fw-semibold" style="background-color: #2E7D5B;" :disabled="submitting">
                                <i v-if="submitting" class="ri-loader-4-line spin me-1"></i>
                                <i v-else class="ri-send-plane-line me-1"></i>
                                {{ submitting ? 'Submitting...' : 'Submit Request' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </PortalLayout>
</template>

<style scoped>
.spin { animation: spin 1s linear infinite; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>
