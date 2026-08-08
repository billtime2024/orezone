<script>
import { Link, Head, useForm } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/portal-layout.vue';

export default {
    components: { Link, Head, PortalLayout },
    props: {
        user: Object,
        vehicle: Object,
        categories: Array,
    },
    data() {
        return {
            form: useForm({
                vehicle_category_id: this.vehicle.vehicle_category_id || '',
                registration_number: this.vehicle.registration_number || '',
                brand: this.vehicle.brand || '',
                model: this.vehicle.model || '',
                year: this.vehicle.year || '',
                color: this.vehicle.color || '',
                seating_capacity: this.vehicle.seating_capacity || 4,
            }),
        };
    },
    methods: {
        submit() {
            this.form.put(`/portal/vehicles/${this.vehicle.id}`, {
                onSuccess: () => {
                    this.$inertia.visit(`/portal/vehicles/${this.vehicle.id}`);
                },
            });
        },
    },
};
</script>

<template>
    <Head :title="`Edit ${vehicle.brand} ${vehicle.model} - orezone`" />

    <PortalLayout>
        <div class="mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><Link href="/portal/vehicles">My Vehicles</Link></li>
                    <li class="breadcrumb-item"><Link :href="`/portal/vehicles/${vehicle.id}`">{{ vehicle.brand }} {{ vehicle.model }}</Link></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-1">Edit Vehicle</h4>
            <p class="text-muted mb-0">Update your vehicle information</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <form @submit.prevent="submit">
                            <!-- Category -->
                            <div class="mb-3">
                                <label for="vehicle_category_id" class="form-label fw-semibold">Vehicle Category</label>
                                <select
                                    id="vehicle_category_id"
                                    v-model="form.vehicle_category_id"
                                    class="form-select"
                                    :class="{ 'is-invalid': form.errors.vehicle_category_id }"
                                    required
                                >
                                    <option value="">Select a category</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                        {{ cat.name }}
                                    </option>
                                </select>
                                <div class="invalid-feedback">{{ form.errors.vehicle_category_id }}</div>
                            </div>

                            <!-- Registration Number -->
                            <div class="mb-3">
                                <label for="registration_number" class="form-label fw-semibold">Registration Number</label>
                                <input
                                    id="registration_number"
                                    v-model="form.registration_number"
                                    type="text"
                                    class="form-control"
                                    :class="{ 'is-invalid': form.errors.registration_number }"
                                    required
                                />
                                <div class="invalid-feedback">{{ form.errors.registration_number }}</div>
                            </div>

                            <!-- Brand & Model -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="brand" class="form-label fw-semibold">Brand</label>
                                    <input
                                        id="brand"
                                        v-model="form.brand"
                                        type="text"
                                        class="form-control"
                                        :class="{ 'is-invalid': form.errors.brand }"
                                        required
                                    />
                                    <div class="invalid-feedback">{{ form.errors.brand }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="model" class="form-label fw-semibold">Model</label>
                                    <input
                                        id="model"
                                        v-model="form.model"
                                        type="text"
                                        class="form-control"
                                        :class="{ 'is-invalid': form.errors.model }"
                                        required
                                    />
                                    <div class="invalid-feedback">{{ form.errors.model }}</div>
                                </div>
                            </div>

                            <!-- Year, Color, Seats -->
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="year" class="form-label fw-semibold">Year</label>
                                    <input
                                        id="year"
                                        v-model="form.year"
                                        type="number"
                                        class="form-control"
                                        :class="{ 'is-invalid': form.errors.year }"
                                        min="1900"
                                        :max="new Date().getFullYear()"
                                        required
                                    />
                                    <div class="invalid-feedback">{{ form.errors.year }}</div>
                                </div>
                                <div class="col-md-4">
                                    <label for="color" class="form-label fw-semibold">Color</label>
                                    <input
                                        id="color"
                                        v-model="form.color"
                                        type="text"
                                        class="form-control"
                                        :class="{ 'is-invalid': form.errors.color }"
                                    />
                                    <div class="invalid-feedback">{{ form.errors.color }}</div>
                                </div>
                                <div class="col-md-4">
                                    <label for="seating_capacity" class="form-label fw-semibold">Seating Capacity</label>
                                    <input
                                        id="seating_capacity"
                                        v-model="form.seating_capacity"
                                        type="number"
                                        class="form-control"
                                        :class="{ 'is-invalid': form.errors.seating_capacity }"
                                        min="1"
                                        max="20"
                                        required
                                    />
                                    <div class="invalid-feedback">{{ form.errors.seating_capacity }}</div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <Link :href="`/portal/vehicles/${vehicle.id}`" class="btn btn-outline-secondary">
                                    Cancel
                                </Link>
                                <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                    <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                                    <i v-else class="ri-save-line me-1"></i>
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>
