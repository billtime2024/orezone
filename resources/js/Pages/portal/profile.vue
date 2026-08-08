<script>
import { Link, Head, useForm } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/portal-layout.vue';

export default {
    components: { Link, Head, PortalLayout },
    props: {
        user: Object,
        success: String,
    },
    data() {
        return {
            form: useForm({
                name: this.user.name || '',
                email: this.user.email || '',
            }),
        };
    },
    methods: {
        submit() {
            this.form.put('/user/profile-information');
        },
    },
};
</script>

<template>
    <Head title="Profile - orezone" />

    <PortalLayout>
        <div class="mb-4">
            <h4 class="fw-bold mb-1">Profile</h4>
            <p class="text-muted mb-0">Update your profile information</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Success Message -->
                <div v-if="success" class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ri-check-line me-1"></i> {{ success }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <!-- Profile Header -->
                        <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 64px; height: 64px; font-size: 24px;">
                                {{ user.name ? user.name.charAt(0).toUpperCase() : 'U' }}
                            </div>
                            <div>
                                <h5 class="fw-bold text-dark mb-0">{{ user.name }}</h5>
                                <small class="text-muted">{{ user.email }}</small>
                            </div>
                        </div>

                        <form @submit.prevent="submit">
                            <!-- Name -->
                            <div class="mb-3">
                                <label for="name" class="form-label fw-semibold">Name</label>
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    class="form-control"
                                    :class="{ 'is-invalid': form.errors.name }"
                                    required
                                />
                                <div class="invalid-feedback">{{ form.errors.name }}</div>
                            </div>

                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold">Email</label>
                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    class="form-control"
                                    :class="{ 'is-invalid': form.errors.email }"
                                    required
                                />
                                <div class="invalid-feedback">{{ form.errors.email }}</div>
                            </div>

                            <!-- Actions -->
                            <div class="d-flex justify-content-end gap-2 mt-4">
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
