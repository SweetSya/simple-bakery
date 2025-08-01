<script setup>
import { initializeTheme, useAppearance } from '@/composables/useAppearance';
import { Link } from '@inertiajs/vue3';
import { Moon, Sun, UserCircle } from 'lucide-vue-next';

// Initialize the appearance system
initializeTheme();

// Get appearance state and setter
const { appearance, updateAppearance } = useAppearance();

// Toggle between light and dark modes
function toggleDarkMode() {
    const newAppearance = appearance.value === 'dark' ? 'light' : 'dark';
    updateAppearance(newAppearance);
}
</script>

<template>
    <nav class="border-gray-200 bg-white dark:bg-gray-900">
        <div class="mx-auto flex max-w-screen-xl flex-wrap items-center justify-between p-4">
            <Link href="/" class="flex items-center space-x-3 rtl:space-x-reverse">
                <img src="/assets/logo.png" class="h-8 rounded-full" alt="Logo" />
                <span class="self-center text-2xl font-semibold whitespace-nowrap dark:text-white">Rivies Bakery</span>
            </Link>
            <div class="flex items-center space-x-6 rtl:space-x-reverse">
                <label class="relative inline-flex cursor-pointer items-center">
                    <input @change="toggleDarkMode()" type="checkbox" :checked="appearance === 'dark'" class="peer sr-only" />
                    <div
                        class="peer relative h-6 w-11 rounded-full bg-gray-200 peer-checked:bg-blue-600 peer-focus:ring-4 peer-focus:ring-blue-300 peer-focus:outline-none after:absolute after:start-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:after:translate-x-full peer-checked:after:border-white rtl:peer-checked:after:-translate-x-full dark:border-gray-600 dark:bg-gray-700 dark:peer-checked:bg-blue-600 dark:peer-focus:ring-blue-800"
                    ></div>
                    <span class="absolute top-0 ms-3 text-sm font-medium text-gray-900">
                        <template v-if="appearance === 'dark'">
                            <Moon class="absolute top-0 left-3 w-4" />
                        </template>
                        <template v-else>
                            <Sun class="absolute top-0 -right-2 w-4" />
                        </template>
                    </span>
                </label>
                <Link
                    href="/login"
                    class="flex gap-2 rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-900 hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:hover:border-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-700"
                >
                    <UserCircle class="h-5" />Masuk
                </Link>
            </div>
        </div>
    </nav>
    <!-- Main Wrapper -->
    <main class="sticky z-10">
        <nav class="sticky top-0 z-50 bg-gray-50 dark:bg-gray-800">
            <div class="mx-auto max-w-screen-xl px-4 py-3">
                <div class="flex items-center">
                    <ul class="mt-0 flex flex-row space-x-8 text-sm font-medium">
                        <li>
                            <Link href="/#store" class="text-gray-900 hover:underline dark:text-white">Store</Link>
                        </li>
                        <li>
                            <Link href="/#about" class="text-gray-900 hover:underline dark:text-white">About</Link>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <!-- App Content -->
        <slot />
    </main>
</template>
