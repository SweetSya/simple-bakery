<script setup lang="ts">
import { useNotyf } from '@/composables/useNotyf';
import { Link, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import GuestLayout from './layout/GuestLayout.vue';

const notyf = useNotyf();

defineOptions({ layout: GuestLayout });

// Use Inertia form
const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const handleLogin = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
        onSuccess: () => {
            window.location.reload(); // This refreshes everything and updates CSRF
        },
        onError: (errors) => {
            if (errors.email) {
                notyf.error(errors.email);
            }
            if (errors.password) {
                notyf.error(errors.password);
            }
        },
    });
};
</script>

<template>
    <section>
        <div
            class="fixed top-0 left-0 h-screen w-screen !bg-gray-900/30 !bg-cover !bg-blend-darken dark:!bg-gray-900/60"
            style="background: url('/assets/images/curved-images/curved1.jpg') center center no-repeat"
        ></div>

        <div class="fixed top-[180px] left-1/2 min-w-sm -translate-x-1/2 lg:w-1/3 lg:py-0">
            <div
                class="flex h-full w-full items-center justify-center rounded-lg bg-white/80 shadow backdrop-blur-lg md:mt-0 xl:p-0 dark:border dark:border-gray-700 dark:bg-gray-800/80"
            >
                <div class="w-full space-y-4 p-6 sm:p-8 md:space-y-6">
                    <h1 class="mb-0 text-xl leading-tight font-bold tracking-tight text-gray-900 md:text-2xl dark:text-white">Masuk Bakery</h1>
                    <p class="mb-5 font-normal text-gray-700 dark:text-gray-400">Harap isi menggunakan kredensial yang kamu miliki</p>

                    <form class="space-y-4 md:space-y-6" @submit.prevent="handleLogin">
                        <div>
                            <label for="email" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Email</label>
                            <input
                                type="email"
                                id="email"
                                v-model="form.email"
                                :class="{ 'border-red-500': form.errors.email }"
                                class="focus:ring-primary-600 focus:border-primary-600 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-gray-900 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                                placeholder="name@bakery.com"
                                required
                            />
                            <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</div>
                        </div>

                        <div>
                            <label for="password" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Password</label>
                            <input
                                type="password"
                                id="password"
                                v-model="form.password"
                                :class="{ 'border-red-500': form.errors.password }"
                                class="focus:ring-primary-600 focus:border-primary-600 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-gray-900 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                                placeholder="••••••••"
                                required
                            />
                            <div v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</div>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-start">
                                <div class="flex h-5 items-center">
                                    <input
                                        id="remember"
                                        type="checkbox"
                                        v-model="form.remember"
                                        class="h-4 w-4 rounded border border-gray-300 bg-gray-50 focus:ring-3"
                                    />
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="remember" class="text-gray-500 dark:text-gray-300">Ingat perangkat</label>
                                </div>
                            </div>
                            <a href="#" class="text-sm font-medium hover:underline">Lupa password?</a>
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex w-full justify-center gap-2 rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-900 hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 disabled:opacity-50"
                        >
                            {{ form.processing ? 'Memproses...' : 'Masuk' }}
                        </button>

                        <p class="text-sm font-light text-gray-500 dark:text-gray-400">
                            Belum memiliki akun?
                            <Link href="/register" class="font-medium hover:underline">Daftar disini</Link>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </section>
</template>
