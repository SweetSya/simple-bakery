<!-- filepath: d:\Programming\WebDev\simple-bakery\resources\js\pages\auth\User\DetailUser.vue -->
<script setup lang="ts">
import { useUserTabs } from '@/composables/SubNavigation/useUserTabs';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AuthLayout from '../../layout/AuthLayout.vue';
import ManageDataLayout from '../SubLayout/ManageDataLayout.vue';

defineOptions({ layout: AuthLayout });

const page = usePage();
const userId = computed(() => page.props.user?.id);
const user = computed(() => page.props.user as any);
const userTabs = useUserTabs(userId.value);
</script>

<template>
    <ManageDataLayout
        title="User Details"
        subtitle="View user details and roles"
        :entityId="userId"
        activeTab="detail"
        :tabs="userTabs"
        baseUrl="/admin-panel/user"
    >
        <div class="grid gap-4 sm:grid-cols-2 sm:gap-6">
            <!-- User Info Display -->
            <div class="col-span-2 lg:col-span-1">
                <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Name</label>
                <div
                    class="block w-full rounded-lg border border-gray-300 bg-gray-100 p-2.5 text-sm text-gray-900 dark:border-gray-600 dark:bg-gray-600 dark:text-white"
                >
                    {{ user?.name || 'N/A' }}
                </div>
            </div>

            <div class="col-span-2 lg:col-span-1">
                <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Email</label>
                <div
                    class="block w-full rounded-lg border border-gray-300 bg-gray-100 p-2.5 text-sm text-gray-900 dark:border-gray-600 dark:bg-gray-600 dark:text-white"
                >
                    {{ user?.email || 'N/A' }}
                </div>
            </div>

            <div class="col-span-2">
                <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Role</label>
                <div
                    class="block w-full rounded-lg border border-gray-300 bg-gray-100 p-2.5 text-sm text-gray-900 dark:border-gray-600 dark:bg-gray-600 dark:text-white"
                >
                    {{ user?.role?.name || 'No Role Assigned' }}
                </div>
            </div>

            <!-- Additional User Info -->
            <div class="col-span-2">
                <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Email Verified</label>
                <div class="block w-full rounded-lg border border-gray-300 bg-gray-100 p-2.5 text-sm dark:border-gray-600 dark:bg-gray-600">
                    <span
                        v-if="user?.email_verified_at"
                        class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-300"
                    >
                        ✓ Verified
                    </span>
                    <span
                        v-else
                        class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900 dark:text-red-300"
                    >
                        ✗ Not Verified
                    </span>
                </div>
            </div>

            <div class="col-span-2 lg:col-span-1">
                <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Created At</label>
                <div
                    class="block w-full rounded-lg border border-gray-300 bg-gray-100 p-2.5 text-sm text-gray-900 dark:border-gray-600 dark:bg-gray-600 dark:text-white"
                >
                    {{ user?.created_at ? new Date(user.created_at).toLocaleDateString() : 'N/A' }}
                </div>
            </div>

            <div class="col-span-2 lg:col-span-1">
                <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Last Updated</label>
                <div
                    class="block w-full rounded-lg border border-gray-300 bg-gray-100 p-2.5 text-sm text-gray-900 dark:border-gray-600 dark:bg-gray-600 dark:text-white"
                >
                    {{ user?.updated_at ? new Date(user.updated_at).toLocaleDateString() : 'N/A' }}
                </div>
            </div>
        </div>
    </ManageDataLayout>
</template>
