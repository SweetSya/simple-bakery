<script setup lang="ts">
import { initializeTheme, useAppearance } from '@/composables/useAppearance';
import { useCart } from '@/composables/useCheckout';
import { useFlashMessages } from '@/composables/useFlashMessages';
import { Link } from '@inertiajs/vue3';
import { Moon, ShoppingCart, Sun, UserCircle } from 'lucide-vue-next';

const { cartItemsCount } = useCart();

// Initialize flash messages
useFlashMessages();
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
    <nav class="relative z-20 border-gray-200 bg-slate-100 dark:bg-gray-900">
        <div class="mx-auto flex max-w-screen-xl flex-wrap items-center justify-between p-4">
            <Link href="/" class="flex items-center space-x-3 rtl:space-x-reverse">
                <img src="/assets/logo.png" class="h-8 rounded-full" alt="Logo" />
                <span class="self-center text-2xl font-semibold whitespace-nowrap dark:text-white">Bakery</span>
            </Link>
            <div class="flex items-center space-x-6 rtl:space-x-reverse">
                <label class="relative inline-flex cursor-pointer items-center">
                    <input @change="toggleDarkMode()" type="checkbox" :checked="appearance === 'dark'" class="peer sr-only" />
                    <div
                        class="peer relative h-6 w-11 rounded-full bg-gray-200 peer-checked:bg-cyan-600 peer-focus:ring-4 peer-focus:ring-cyan-300 peer-focus:outline-none after:absolute after:start-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:after:translate-x-full peer-checked:after:border-white rtl:peer-checked:after:-translate-x-full dark:border-gray-600 dark:bg-gray-700 dark:peer-checked:bg-cyan-600 dark:peer-focus:ring-cyan-800"
                    ></div>
                    <span class="absolute top-0 ms-3 text-sm font-medium text-gray-900">
                        <template v-if="appearance === 'dark'">
                            <Moon class="absolute top-0 left-[13px] w-4" />
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
        <nav class="sticky top-0 z-50 border-b border-gray-300 bg-gray-50 dark:border-gray-600 dark:bg-gray-800">
            <div class="mx-auto flex w-full max-w-screen-xl justify-between px-4 py-3">
                <div class="flex items-center">
                    <ul class="mt-0 flex flex-row space-x-8 text-sm font-medium">
                        <li>
                            <Link href="/" class="text-gray-900 hover:underline dark:text-white">Store</Link>
                        </li>
                        <li>
                            <Link href="/#about" class="text-gray-900 hover:underline dark:text-white">About</Link>
                        </li>
                    </ul>
                </div>
                <div>
                    <button
                        type="button"
                        data-drawer-target="drawer-right-example"
                        data-drawer-show="drawer-right-example"
                        data-drawer-placement="right"
                        class="relative inline-flex cursor-pointer items-center rounded-lg bg-cyan-700 p-3 text-center text-sm font-medium text-white hover:bg-cyan-800 focus:ring-4 focus:ring-cyan-300 focus:outline-none dark:bg-cyan-600 dark:hover:bg-cyan-700 dark:focus:ring-cyan-800"
                    >
                        <ShoppingCart class="h-5 w-5" />
                        <span class="sr-only">Notifications</span>
                        <div
                            class="absolute -end-2 -top-2 inline-flex h-6 w-6 items-center justify-center rounded-full border-2 border-white bg-red-500 text-xs font-bold text-white dark:border-gray-900"
                        >
                            {{ cartItemsCount }}
                        </div>
                    </button>
                </div>
            </div>
        </nav>
        <!-- App Content -->
        <slot />
    </main>
    <div
        id="drawer-right-example"
        class="fixed top-0 right-0 z-40 h-screen w-80 translate-x-full overflow-y-auto bg-white p-4 transition-transform dark:bg-gray-800"
        tabindex="-1"
        aria-labelledby="drawer-right-label"
    >
        <h5 id="drawer-right-label" class="mb-4 inline-flex items-center text-base font-semibold text-gray-500 dark:text-gray-400">Right drawer</h5>
        <button
            type="button"
            data-drawer-hide="drawer-right-example"
            aria-controls="drawer-right-example"
            class="absolute end-2.5 top-2.5 inline-flex h-8 w-8 items-center justify-center rounded-lg bg-transparent text-sm text-gray-400 hover:bg-gray-200 hover:text-gray-900 dark:hover:bg-gray-600 dark:hover:text-white"
        >
            <span class="sr-only">Close menu</span>
        </button>
        <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">
            Supercharge your hiring by taking advantage of our
            <a href="#" class="font-medium text-blue-600 underline hover:no-underline dark:text-blue-500">limited-time sale</a> for Flowbite Docs +
            Job Board. Unlimited access to over 190K top-ranked candidates and the #1 design job board.
        </p>
        <div class="grid grid-cols-2 gap-4">
            <a
                href="#"
                class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-center text-sm font-medium text-gray-900 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white dark:focus:ring-gray-700"
                >Learn more</a
            >
            <a
                href="#"
                class="inline-flex items-center rounded-lg bg-blue-700 px-4 py-2 text-center text-sm font-medium text-white hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 focus:outline-none dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
                >Get access ></a
            >
        </div>
    </div>
</template>
