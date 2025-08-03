<script setup lang="ts">
import VSelect from '@/components/VSelect.vue';
import { useNotyf } from '@/composables/useNotyf';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onMounted, ref } from 'vue';
import AuthLayout from '../../layout/AuthLayout.vue';

defineOptions({ layout: AuthLayout });

// Add role management
const roleOptions = ref([]);
const loadingRoles = ref(false);

// Fetch roles for the select dropdown
const fetchRoles = async () => {
    loadingRoles.value = true;
    try {
        // You can use axios or Inertia to fetch roles
        const response = await axios.get('/admin-panel/role/all');
        roleOptions.value = response.data.data;
    } catch (error) {
        console.error('Error fetching roles:', error);
    } finally {
        loadingRoles.value = false;
    }
};

// Load roles when component mounts
onMounted(() => {
    fetchRoles();
});

const form = useForm({
    name: '',
    email: '',
    role_id: '',
    password: '',
    confirm_password: '',
});
const passwordMatch = computed(() => {
    return form.password === form.confirm_password;
});
const submit = () => {
    form.clearErrors();
    form.processing = true;
    // Validate password match
    if (!passwordMatch.value) {
        form.setError('confirm_password', 'Passwords do not match');
        form.processing = false;
        return;
    }

    try {
        form.post(route('user.create'), {
            onSuccess: () => {
                form.reset();
                form.processing = false;
            },
            onError: (errors) => {
                form.processing = false;
                if (errors.name) {
                    form.setError('name', errors.name);
                }
                if (errors.email) {
                    form.setError('email', errors.email);
                }
                if (errors.role_id) {
                    form.setError('role_id', errors.role_id);
                }
                if (errors.password) {
                    form.setError('password', errors.password);
                }
                if (errors.confirm_password) {
                    form.setError('confirm_password', errors.confirm_password);
                }
                useNotyf().error('Error while creating user. Please check the form and try again.');
                form.reset('password', 'confirm_password');
            },
        });
    } catch (error) {
        form.processing = false;
        console.error('Submission error:', error);
    }
};
</script>

<template>
    <div class="mx-auto rounded-lg bg-gray-50 p-6 shadow-md dark:bg-gray-800">
        <h2 class="mb-4 text-xl font-bold text-gray-900 dark:text-white">Add New User</h2>
        <form @submit.prevent="submit">
            <div class="grid gap-4 sm:grid-cols-2 sm:gap-6">
                <div class="col-span-2 md:col-span-1">
                    <label for="name" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Name</label>
                    <input
                        type="text"
                        name="name"
                        v-model="form.name"
                        id="name"
                        :class="{ '!border-red-500': form.errors.name }"
                        class="focus:ring-primary-600 focus:border-primary-600 dark:focus:ring-primary-500 dark:focus:border-primary-500 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                        placeholder="User name.."
                        required
                    />
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500" v-if="form.errors.name">{{ form.errors.name }}</p>
                </div>
                <div class="col-span-2 md:col-span-1">
                    <label for="email" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Email</label>
                    <input
                        type="email"
                        name="email"
                        v-model="form.email"
                        id="email"
                        :class="{ '!border-red-500': form.errors.email }"
                        class="focus:ring-primary-600 focus:border-primary-600 dark:focus:ring-primary-500 dark:focus:border-primary-500 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                        placeholder="example@email.com"
                        required
                    />
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500" v-if="form.errors.email">{{ form.errors.email }}</p>
                </div>
                <div class="col-span-2">
                    <label for="role" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Role</label>
                    <VSelect
                        v-model="form.role_id"
                        :options="roleOptions"
                        placeholder="Select a role"
                        option-label="name"
                        option-value="id"
                        :loading="loadingRoles"
                        class="w-full"
                    />
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500" v-if="form.errors.role_id">{{ form.errors.role_id }}</p>
                </div>
                <div class="col-span-2">
                    <label for="password" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Password</label>
                    <input
                        type="password"
                        id="password"
                        v-model="form.password"
                        :class="{ '!border-red-500': form.errors.password }"
                        class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                        placeholder="•••••••••"
                        required
                    />
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500" v-if="form.errors.password">{{ form.errors.password }}</p>
                </div>
                <div class="col-span-2">
                    <label for="confirm_password" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Confirm password</label>
                    <input
                        type="password"
                        id="confirm_password"
                        v-model="form.confirm_password"
                        :class="{ '!border-red-500': !passwordMatch }"
                        class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                        placeholder="•••••••••"
                        required
                    />
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500" v-if="form.errors.confirm_password">{{ form.errors.confirm_password }}</p>
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500" v-if="!passwordMatch">Password does not match</p>
                </div>
            </div>
            <div class="mt-6 flex items-center justify-end">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="me-2 mb-2 rounded-lg bg-blue-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 focus:outline-none dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
                >
                    {{ form.processing ? 'Processing..' : 'Save' }}
                </button>
            </div>
        </form>
    </div>
</template>
