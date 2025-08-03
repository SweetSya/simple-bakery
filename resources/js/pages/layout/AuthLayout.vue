<script setup lang="ts">
import Breadcrumb from '@/components/Breadcrumb.vue';
import { useAppearance } from '@/composables/useAppearance';
import { useFlashMessages } from '@/composables/useFlashMessages';
import { Link, usePage } from '@inertiajs/vue3';
import { Briefcase, Coins, DoorOpen, LayoutDashboard, Menu, Moon, Shield, Sun, Truck, Users, X } from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage();

const currentRoute = computed(() => {
    const route = String(page.props.current_route);
    return route?.split('.')[0] || '';
});
// Initialize flash messages
useFlashMessages();

const breadCrumb = computed(() => {
    // Get current url and split by dot to get the segments
    const segments = page.url.split('/');
    // Remove the first empty segment if it exists
    if (segments[0] === '') {
        segments.shift();
    }
    if (segments[0] === 'admin-panel') {
        segments.shift();
    }
    // Get the last segment as the current route
    const route = String(page.props.current_route);
    return {
        routes: route?.split('.'),
        segments: segments,
    };
});
console.log('Breadcrumb:', breadCrumb.value);

// Get appearance state and setter
const { appearance, updateAppearance } = useAppearance();

// Toggle between light and dark modes
function toggleDarkMode() {
    const newAppearance = appearance.value === 'dark' ? 'light' : 'dark';
    updateAppearance(newAppearance);
}
</script>

<template>
    <nav class="relative z-10 border-gray-200 bg-slate-100 dark:bg-gray-900">
        <div class="mx-auto flex flex-wrap items-center justify-between p-4">
            <button
                data-drawer-target="sidebar-menu"
                data-drawer-toggle="sidebar-menu"
                aria-controls="sidebar-menu"
                type="button"
                class="inline-flex items-center rounded-lg p-2 text-sm text-gray-500 hover:bg-gray-100 focus:ring-2 focus:ring-gray-200 focus:outline-none dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600"
            >
                <span class="sr-only">Open sidebar</span>
                <Menu class="h-5 w-5" />
            </button>
            <div class="flex items-center space-x-6 rtl:space-x-reverse">
                <label class="relative inline-flex cursor-pointer items-center">
                    <input @change="toggleDarkMode()" type="checkbox" :checked="appearance === 'dark'" class="peer sr-only" />
                    <div
                        class="peer relative h-6 w-11 rounded-full bg-gray-200 peer-checked:bg-cyan-600 peer-focus:ring-4 peer-focus:ring-cyan-300 peer-focus:outline-none after:absolute after:start-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:after:translate-x-full peer-checked:after:border-white rtl:peer-checked:after:-translate-x-full dark:border-gray-600 dark:bg-gray-700 dark:peer-checked:bg-cyan-600 dark:peer-focus:ring-cyan-800"
                    ></div>
                    <span class="absolute top-0 ms-3 text-sm font-normal text-gray-900">
                        <template v-if="appearance === 'dark'">
                            <Moon class="absolute top-0 left-[13px] w-4" />
                        </template>
                        <template v-else>
                            <Sun class="absolute top-0 -right-2 w-4" />
                        </template>
                    </span>
                </label>
                <Link
                    href="/logout"
                    class="flex gap-2 rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-normal text-gray-900 hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:hover:border-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-700"
                >
                    <DoorOpen class="h-5" />Keluar
                </Link>
            </div>
        </div>
    </nav>

    <aside
        id="sidebar-menu"
        class="fixed top-0 left-0 z-[31] h-screen w-64 -translate-x-full transition-transform sm:z-[11] sm:translate-x-0"
        aria-label="Sidebar"
    >
        <div class="h-full overflow-y-auto bg-gray-50 px-3 pt-4 pb-8 dark:bg-gray-800">
            <div class="flex w-full items-center space-x-3 border-b border-gray-200 pb-3 rtl:space-x-reverse dark:border-gray-700">
                <img src="/assets/logo.png" class="h-8 rounded-full" alt="Logo" />
                <span class="self-center text-base font-semibold whitespace-nowrap dark:text-white">Panel Bakery</span>
                <button
                    class="ms-auto me-2 inline-flex items-center rounded-lg p-2 text-sm text-gray-500 hover:bg-gray-100 focus:ring-2 focus:ring-gray-200 focus:outline-none sm:hidden dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600"
                >
                    <X
                        data-drawer-target="sidebar-menu"
                        data-drawer-toggle="sidebar-menu"
                        aria-controls="sidebar-menu"
                        class="h-5 w-5 text-gray-500"
                    />
                </button>
            </div>
            <ul class="mt-3 space-y-2 font-normal">
                <li>
                    <Link
                        href="/admin-panel/dashboard"
                        :class="currentRoute === 'dashboard' ? 'bg-gray-100 opacity-70 dark:bg-gray-700' : ''"
                        class="group flex items-center rounded-lg p-2 text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                    >
                        <LayoutDashboard class="h-5 w-5" />
                        <span class="ms-3">Dashboard</span>
                    </Link>
                </li>
                <li>
                    <Link
                        href="/admin-panel/role"
                        :class="currentRoute === 'role' ? 'bg-gray-100 opacity-70 dark:bg-gray-700' : ''"
                        class="group flex items-center rounded-lg p-2 text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                    >
                        <Shield class="h-5 w-5" />
                        <span class="ms-3 flex-1 whitespace-nowrap">Roles</span>
                    </Link>
                </li>
                <li>
                    <Link
                        href="/admin-panel/user"
                        :class="currentRoute === 'user' ? 'bg-gray-100 opacity-70 dark:bg-gray-700' : ''"
                        class="group flex items-center rounded-lg p-2 text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                    >
                        <Users class="h-5 w-5" />
                        <span class="ms-3 flex-1 whitespace-nowrap">User</span>
                    </Link>
                </li>
                <li>
                    <Link
                        href="/admin-panel/transaction"
                        :class="currentRoute === 'transaction' ? 'bg-gray-100 opacity-70 dark:bg-gray-700' : ''"
                        class="group flex items-center rounded-lg p-2 text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                    >
                        <Coins class="h-5 w-5" />
                        <span class="ms-3 flex-1 whitespace-nowrap">Transaction</span>
                        <span
                            class="ms-3 inline-flex h-3 w-3 items-center justify-center rounded-full bg-cyan-100 p-3 text-sm font-normal text-cyan-800 dark:bg-cyan-900 dark:text-cyan-300"
                            >3</span
                        >
                    </Link>
                </li>
                <li>
                    <Link
                        href="/admin-panel/purchasing"
                        :class="currentRoute === 'purchasing' ? 'bg-gray-100 opacity-70 dark:bg-gray-700' : ''"
                        class="group flex items-center rounded-lg p-2 text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                    >
                        <Truck class="h-5 w-5" />

                        <span class="ms-3 flex-1 whitespace-nowrap">Purchasing</span>
                    </Link>
                </li>
                <li>
                    <Link
                        href="/admin-panel/product"
                        :class="currentRoute === 'product' ? 'bg-gray-100 opacity-70 dark:bg-gray-700' : ''"
                        class="group flex items-center rounded-lg p-2 text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                    >
                        <Briefcase class="h-5 w-5" />
                        <span class="ms-3 flex-1 whitespace-nowrap">Products</span>
                    </Link>
                </li>
            </ul>
        </div>
    </aside>
    <!-- Main Wrapper -->
    <main>
        <!-- Breadcrumb -->

        <!-- Breadcrumb -->
        <div class="p-5">
            <div class="sm:ml-64">
                <Breadcrumb />
            </div>
        </div>

        <!-- App Content -->
        <div class="px-5 pb-5 sm:ml-64">
            <slot />
        </div>
    </main>
</template>
