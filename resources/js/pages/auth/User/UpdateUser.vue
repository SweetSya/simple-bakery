<!-- filepath: d:\Programming\WebDev\simple-bakery\resources\js\pages\auth\User\UpdateUser.vue -->
<script setup lang="ts">
import VSelect from '@/components/VSelect.vue';
import { useUserTabs } from '@/composables/SubNavigation/useUserTabs';
import { useForm, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onMounted, ref } from 'vue';
import AuthLayout from '../../layout/AuthLayout.vue';
import ManageDataLayout from '../SubLayout/ManageDataLayout.vue';

defineOptions({ layout: AuthLayout });

const page = usePage();
const userId = computed(() => page.props.user?.id);
const user = computed(() => page.props.user as any);
const userTabs = useUserTabs(userId.value);

// Role management
const roleOptions = ref([]);
const loadingRoles = ref(false);

const fetchRoles = async () => {
    loadingRoles.value = true;
    try {
        const response = await axios.get('/admin-panel/role/all');
        roleOptions.value = response.data.data;
    } catch (error) {
        console.error('Error fetching roles:', error);
    } finally {
        loadingRoles.value = false;
    }
};

onMounted(() => {
    fetchRoles();
});

const form = useForm({
    name: user.value?.name || '',
    email: user.value?.email || '',
    role_id: user.value?.role_id || '',
});

const submit = () => {
    form.clearErrors();

    form.put(`${route('user.update')}?id=${userId.value}`, {
        onSuccess: () => {
            // Success handled by ManageDataLayout or flash messages
        },
        onError: (errors) => {
            // Errors handled automatically by Inertia
        },
    });
};
</script>

<template>
    <ManageDataLayout
        title="Update User"
        subtitle="Please fill out the form below to update user information."
        :entityId="userId"
        activeTab="update"
        :tabs="userTabs"
        baseUrl="/admin-panel/user"
    >
        <form @submit.prevent="submit">
            <div class="grid gap-4 sm:grid-cols-2 sm:gap-6">
                <!-- Name Field -->
                <div class="col-span-2 lg:col-span-1">
                    <label for="name" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white"> Name </label>
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
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500" v-if="form.errors.name">
                        {{ form.errors.name }}
                    </p>
                </div>

                <!-- Email Field -->
                <div class="col-span-2 lg:col-span-1">
                    <label for="email" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white"> Email </label>
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
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500" v-if="form.errors.email">
                        {{ form.errors.email }}
                    </p>
                </div>

                <!-- Role Field -->
                <div class="col-span-2">
                    <label for="role" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white"> Role </label>
                    <VSelect
                        v-model="form.role_id"
                        :options="roleOptions"
                        placeholder="Select a role"
                        option-label="name"
                        option-value="id"
                        :loading="loadingRoles"
                        :error="!!form.errors.role_id"
                        class="w-full"
                    />
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500" v-if="form.errors.role_id">
                        {{ form.errors.role_id }}
                    </p>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="mt-6 flex items-center justify-end">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="me-2 mb-2 rounded-lg bg-blue-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
                >
                    {{ form.processing ? 'Processing...' : 'Save Changes' }}
                </button>
            </div>
        </form>
    </ManageDataLayout>
</template>
