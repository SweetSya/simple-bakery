<!-- Advanced reusable UserSubNav.vue -->
<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed, type Component } from 'vue';

interface NavItem {
    name: string;
    icon: Component;
    href: string;
    active: boolean;
}

interface Props {
    userId: string;
    active: string;
    baseUrl?: string;
    items?: Record<string, Omit<NavItem, 'href' | 'active'>>;
}

const props = withDefaults(defineProps<Props>(), {
    baseUrl: '/admin-panel/user',
    items: () => ({}),
});

const navItems = computed(() => {
    const result: Record<string, NavItem> = {};

    Object.entries(props.items).forEach(([key, item]) => {
        result[key] = {
            ...item,
            href: `${props.baseUrl}/${key === 'view' ? 'detail' : key}?id=${props.userId}`,
            active: props.active === key,
        };
    });

    return result;
});
</script>

<template>
    <ul class="flex-column space-y mb-4 space-y-4 text-sm font-medium text-gray-500 md:me-4 md:mb-0 dark:text-gray-400">
        <li v-for="(item, key) in navItems" :key="key">
            <Link
                :href="item.href"
                :class="item.active ? 'active !bg-blue-600 !text-white' : ''"
                class="inline-flex w-full cursor-pointer items-center rounded-lg bg-gray-50 px-4 py-3 hover:bg-gray-100 hover:text-gray-900 dark:bg-gray-800 dark:hover:bg-gray-700 dark:hover:text-white"
            >
                <component :is="item.icon" class="me-2 h-5 w-5" />
                {{ item.name }}
            </Link>
        </li>
    </ul>
</template>
