<script>
import { Link, Head, useForm } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/portal-layout.vue';

export default {
    components: { Link, Head, PortalLayout },
    props: {
        provider: Object,
        item: Object,
        categories: Array,
    },
    data() {
        return {
            form: useForm({
                _method: 'put',
                category_id: this.item.category_id || '',
                name: this.item.name || '',
                description: this.item.description || '',
                price: this.item.price || '',
                discount_price: this.item.discount_price || '',
                unit: this.item.unit || 'plate',
                min_quantity: this.item.min_quantity || 1,
                max_quantity: this.item.max_quantity || 50,
                preparation_time_min: this.item.preparation_time_min || 30,
                is_jain: this.item.is_jain || false,
                is_vegan: this.item.is_vegan || false,
                spice_level: this.item.spice_level || 'medium',
                allergens: Array.isArray(this.item.allergens) ? this.item.allergens.join(', ') : (this.item.allergens || ''),
                ingredients: this.item.ingredients || '',
                is_available: this.item.is_available ?? true,
                is_featured: this.item.is_featured || false,
                available_days: this.item.available_days || ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'],
                available_from: this.item.available_from || '',
                available_to: this.item.available_to || '',
            }),
        };
    },
    methods: {
        submit() {
            this.form.post(`/portal/food-provider/menu/${this.item.id}`);
        },
        toggleDay(day) {
            const idx = this.form.available_days.indexOf(day);
            if (idx > -1) {
                this.form.available_days.splice(idx, 1);
            } else {
                this.form.available_days.push(day);
            }
        },
    },
};
</script>

<template>
    <Head title="Edit Menu Item - orezone Provider" />
    <PortalLayout>
        <div class="d-flex align-items-center gap-3 mb-4">
            <Link href="/portal/food-provider/menu" class="btn btn-sm btn-outline-secondary">
                <i class="ri-arrow-left-line"></i>
            </Link>
            <div>
                <h4 class="fw-bold text-dark mb-1">Edit Menu Item</h4>
                <p class="text-muted mb-0">Update "{{ item.name }}"</p>
            </div>
        </div>

        <form @submit.prevent="submit">
            <div class="row g-4">
                <div class="col-lg-8">
                    <!-- Item Details -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h6 class="mb-0 fw-semibold">Item Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Item Name <span class="text-danger">*</span></label>
                                <input v-model="form.name" type="text" class="form-control"
                                    :class="{ 'is-invalid': form.errors.name }" required>
                                <div class="invalid-feedback">{{ form.errors.name }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea v-model="form.description" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                                    <select v-model="form.category_id" class="form-select" required>
                                        <option value="">Select category</option>
                                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Unit</label>
                                    <select v-model="form.unit" class="form-select">
                                        <option value="plate">Plate</option>
                                        <option value="bowl">Bowl</option>
                                        <option value="piece">Piece</option>
                                        <option value="serving">Serving</option>
                                        <option value="half_kg">Half Kg</option>
                                        <option value="kg">Kg</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h6 class="mb-0 fw-semibold">Pricing & Availability</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">Price (₹) <span class="text-danger">*</span></label>
                                    <input v-model="form.price" type="number" step="0.01" class="form-control" min="1"
                                        :class="{ 'is-invalid': form.errors.price }" required>
                                    <div class="invalid-feedback">{{ form.errors.price }}</div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">Discount Price (₹)</label>
                                    <input v-model="form.discount_price" type="number" step="0.01" class="form-control" min="0">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">Prep Time (min)</label>
                                    <input v-model="form.preparation_time_min" type="number" class="form-control" min="5">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Available From</label>
                                    <input v-model="form.available_from" type="time" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Available To</label>
                                    <input v-model="form.available_to" type="time" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dietary -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h6 class="mb-0 fw-semibold">Dietary Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">Spice Level</label>
                                    <select v-model="form.spice_level" class="form-select">
                                        <option value="mild">Mild 🌶️</option>
                                        <option value="medium">Medium 🌶️🌶️</option>
                                        <option value="spicy">Spicy 🌶️🌶️🌶️</option>
                                        <option value="extra_spicy">Extra Spicy 🌶️🌶️🌶️🌶️</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">Ingredients</label>
                                    <input v-model="form.ingredients" type="text" class="form-control"
                                        placeholder="Comma-separated">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">Allergens</label>
                                    <input v-model="form.allergens" type="text" class="form-control"
                                        placeholder="Comma-separated">
                                </div>
                            </div>
                            <div class="d-flex gap-4">
                                <div class="form-check">
                                    <input v-model="form.is_jain" class="form-check-input" type="checkbox" id="isJain">
                                    <label class="form-check-label fw-semibold" for="isJain">🧘 Jain Friendly</label>
                                </div>
                                <div class="form-check">
                                    <input v-model="form.is_vegan" class="form-check-input" type="checkbox" id="isVegan">
                                    <label class="form-check-label fw-semibold" for="isVegan">🌱 Vegan</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Status -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h6 class="mb-0 fw-semibold">Status</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-check form-switch mb-3">
                                <input v-model="form.is_available" class="form-check-input" type="checkbox" id="isAvailable">
                                <label class="form-check-label fw-semibold" for="isAvailable">Available for order</label>
                            </div>
                            <div class="form-check form-switch">
                                <input v-model="form.is_featured" class="form-check-input" type="checkbox" id="isFeatured">
                                <label class="form-check-label fw-semibold" for="isFeatured">Featured item</label>
                            </div>
                        </div>
                    </div>

                    <!-- Available Days -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h6 class="mb-0 fw-semibold">Available Days</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex flex-wrap gap-2">
                                <button v-for="day in ['mon','tue','wed','thu','fri','sat','sun']" :key="day"
                                    type="button" class="btn btn-sm rounded-pill"
                                    :class="form.available_days.includes(day) ? 'text-white' : 'btn-outline-secondary'"
                                    :style="form.available_days.includes(day) ? 'background-color: #2E7D5B;' : ''"
                                    @click="toggleDay(day)">
                                    {{ day.charAt(0).toUpperCase() + day.slice(1) }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-lg text-white" style="background-color: #2E7D5B;"
                            :disabled="form.processing">
                            <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                            <i v-else class="ri-save-line me-1"></i>
                            {{ form.processing ? 'Saving...' : 'Update Item' }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </PortalLayout>
</template>
