<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import AuthLayout from '../../layout/AuthLayout.vue';

defineOptions({ layout: AuthLayout });

const page = usePage();
const form = useForm({
    name: page.props.role?.name || '',
    description: page.props.role?.description || '',
});

const submit = () => {
    form.clearErrors();
    form.processing = true;
    try {
        form.put(`${route('role.update')}?id=${page.props.role?.id}`, {
            onSuccess: () => {
                form.processing = false;
            },
            onError: (errors) => {
                form.processing = false;
                if (errors.name) {
                    form.setError('name', errors.name);
                }
                if (errors.description) {
                    form.setError('description', errors.description);
                }
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
        <h2 class="mb-4 text-xl font-bold text-gray-900 dark:text-white">Update Role</h2>
        <form @submit.prevent="submit">
            <div class="grid gap-4 sm:grid-cols-2 sm:gap-6">
                <div class="sm:col-span-2">
                    <label for="name" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Name</label>
                    <input
                        type="text"
                        name="name"
                        v-model="form.name"
                        id="name"
                        :class="{ '!border-red-500': form.errors.name }"
                        class="focus:ring-primary-600 focus:border-primary-600 dark:focus:ring-primary-500 dark:focus:border-primary-500 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                        placeholder="Role name.."
                        required
                    />
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500" v-if="form.errors.name">{{ form.errors.name }}</p>
                </div>

                <div class="sm:col-span-2">
                    <label for="description" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Description</label>
                    <textarea
                        id="description"
                        rows="8"
                        v-model="form.description"
                        name="description"
                        :class="{ '!border-red-500': form.errors.description }"
                        class="focus:ring-primary-500 focus:border-primary-500 dark:focus:ring-primary-500 dark:focus:border-primary-500 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                        placeholder="Role description.."
                    ></textarea>
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500" v-if="form.errors.description">{{ form.errors.description }}</p>
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
