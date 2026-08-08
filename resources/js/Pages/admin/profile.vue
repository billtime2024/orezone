<script>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/admin.vue';

export default {
    components: { Head, AdminLayout },
    layout: AdminLayout,
    data() {
        const user = usePage().props.auth.user;
        return {
            form: useForm({
                name: user.name || '',
                email: user.email || '',
            }),
        };
    },
    methods: {
        submit() {
            this.form.put('/user/profile-information', {
                preserveScroll: true,
            });
        },
    },
    computed: {
        flash() {
            return this.$page.props.flash || {};
        },
    },
};
</script>

<template>
    <Head title="Admin Profile" />

    <div>
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-1 fw-bold">My Profile</h4>
                <p class="text-muted mb-0">Manage your account information</p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div v-if="flash.status" class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ flash.status }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>

                        <form @submit.prevent="submit">
                            <div class="mb-3">
                                <label for="name" class="form-label fw-semibold">Name</label>
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    class="form-control"
                                    :class="{ 'is-invalid': form.errors.name }"
                                    placeholder="Enter your name"
                                />
                                <div v-if="form.errors.name" class="invalid-feedback">
                                    {{ form.errors.name }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold">Email</label>
                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    class="form-control"
                                    :class="{ 'is-invalid': form.errors.email }"
                                    placeholder="Enter your email"
                                />
                                <div v-if="form.errors.email" class="invalid-feedback">
                                    {{ form.errors.email }}
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                    :disabled="form.processing"
                                >
                                    <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                                    Update Profile
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
