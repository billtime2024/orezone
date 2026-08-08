<script>
import { Link, Head, useForm } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/portal-layout.vue';

export default {
    components: { Link, Head, PortalLayout },
    props: {
        user: Object,
        categories: Array,
    },
    data() {
        return {
            form: useForm({
                vehicle_category_id: '',
                registration_number: '',
                brand: '',
                model: '',
                year: '',
                color: '',
                seating_capacity: 4,
            }),
        };
    },
    methods: {
        submit() {
            this.form.post('/portal/vehicles', {
                onSuccess: () => {
                    this.$inertia.visit('/portal/vehicles');
                },
            });
        },
    },
};
</script>

<template>
    <Head title="Add Vehicle - orezone" />

    <PortalLayout>
        <div class="mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><Link href="/portal/vehicles">My Vehicles</Link></li>
                    <li class="breadcrumb-item active">Add Vehicle</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-1">Add New Vehicle</h4>
            <p class="text-muted mb-0">Register a new vehicle for ride sharing</p>
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
                                    placeholder="e.g. MH 12 AB 1234"
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
                                        placeholder="e.g. Maruti Suzuki"
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
                                        placeholder="e.g. Swift Dzire"
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
                                        placeholder="e.g. White"
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
                                <Link href="/portal/vehicles" class="btn btn-outline-secondary">
                                    Cancel
                                </Link>
                                <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                    <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                                    <i v-else class="ri-add-line me-1"></i>
                                    Add Vehicle
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>
