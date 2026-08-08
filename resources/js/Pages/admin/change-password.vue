<script>
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/admin.vue';

export default {
    components: { Head, AdminLayout },
    layout: AdminLayout,
    data() {
        return {
            form: useForm({
                current_password: '',
                password: '',
                password_confirmation: '',
            }),
        };
    },
    methods: {
        submit() {
            this.form.put('/user/password', {
                preserveScroll: true,
                onSuccess: () => {
                    this.form.reset();
                },
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
    <Head title="Change Password" />

    <div>
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-1 fw-bold">Change Password</h4>
                <p class="text-muted mb-0">Ensure your account is using a strong password</p>
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
                                <label for="current_password" class="form-label fw-semibold">Current Password</label>
                                <input
                                    id="current_password"
                                    v-model="form.current_password"
                                    type="password"
                                    class="form-control"
                                    :class="{ 'is-invalid': form.errors.current_password }"
                                    placeholder="Enter current password"
                                />
                                <div v-if="form.errors.current_password" class="invalid-feedback">
                                    {{ form.errors.current_password }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label fw-semibold">New Password</label>
                                <input
                                    id="password"
                                    v-model="form.password"
                                    type="password"
                                    class="form-control"
                                    :class="{ 'is-invalid': form.errors.password }"
                                    placeholder="Enter new password"
                                />
                                <div v-if="form.errors.password" class="invalid-feedback">
                                    {{ form.errors.password }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label fw-semibold">Confirm New Password</label>
                                <input
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    type="password"
                                    class="form-control"
                                    placeholder="Confirm new password"
                                />
                            </div>

                            <div class="d-flex justify-content-end">
                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                    :disabled="form.processing"
                                >
                                    <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                                    Update Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
