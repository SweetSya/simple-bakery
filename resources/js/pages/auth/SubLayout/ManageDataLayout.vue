<!-- filepath: d:\Programming\WebDev\simple-bakery\resources\js\components\ManageDataLayout.vue -->
<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed, type Component } from 'vue';
import TabsLayout from './TabsLayout.vue';

interface PageProps {
    user?: { id: string };
    role?: { id: string };
    entity?: { id: string };
    [key: string]: any;
}

interface TabItem {
    name: string;
    icon: Component;
    href?: string;
}

interface Props {
    title: string;
    subtitle?: string;
    entityId?: string;
    activeTab: string;
    tabs: Record<string, TabItem>;
    baseUrl?: string;
    showTabs?: boolean;
    contentClass?: string;
    headerClass?: string;
}

const props = withDefaults(defineProps<Props>(), {
    subtitle: '',
    baseUrl: '',
    showTabs: true,
    contentClass: '',
    headerClass: '',
});

const page = usePage();
// Auto-generate tab hrefs if not provided
const processedTabs = computed(() => {
    const result: Record<string, TabItem> = {};

    Object.entries(props.tabs).forEach(([key, tab]) => {
        result[key] = {
            ...tab,
            href: tab.href || `${props.baseUrl}/${key === 'view' || key === 'detail' ? 'detail' : key}?id=${props.entityId}`,
        };
    });

    return result;
});
const sourceProps = page.props as PageProps;
// Auto-detect entity ID from different sources
const detectedEntityId = computed(() => {
    return (
        props.entityId ||
        sourceProps.user?.id ||
        sourceProps.role?.id ||
        sourceProps.entity?.id ||
        new URLSearchParams(window.location.search).get('id') ||
        ''
    );
});
</script>

<template>
    <div class="manage-data-layout">
        <!-- Main Content with Tabs -->
        <div class="md:flex">
            <!-- Side Tabs Navigation -->
            <TabsLayout v-if="showTabs" :userId="detectedEntityId" :active="activeTab" :items="processedTabs" class="mb-4" />

            <!-- Main Content Area -->
            <div :class="['text-medium w-full rounded-lg bg-gray-50 p-6 text-gray-500 dark:bg-gray-800 dark:text-gray-400', contentClass]">
                <!-- Header Section -->
                <div :class="['mb-6', headerClass]">
                    <div class="mb-4">
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ title }}</h1>
                        <p v-if="subtitle" class="text-gray-600 dark:text-gray-400">{{ subtitle }}</p>
                    </div>
                </div>

                <slot />
            </div>
        </div>
    </div>
</template>

<style scoped>
.manage-data-layout {
    @apply w-full;
}

/* Add responsive adjustments */
@media (max-width: 768px) {
    .manage-data-layout .md:flex {
        @apply block;
    }
}
</style>
