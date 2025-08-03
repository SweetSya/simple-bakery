<!-- filepath: d:\Programming\WebDev\simple-bakery\resources\js\components\Breadcrumb.vue -->
<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronRight, Home } from 'lucide-vue-next';
import { computed } from 'vue';

interface BreadcrumbItem {
    name: string;
    url: string;
    isLast: boolean;
}

interface Props {
    items?: BreadcrumbItem[];
    homeUrl?: string;
    homeLabel?: string;
}

const props = withDefaults(defineProps<Props>(), {
    homeUrl: '/admin-panel/dashboard',
    homeLabel: 'Home',
});

const page = usePage();

// Auto-generate breadcrumb if items not provided
const breadcrumbItems = computed(() => {
    if (props.items && props.items.length > 0) {
        return props.items;
    }

    // Auto-generate from current URL
    const url = page.url;
    const [pathname, queryString] = url.split('?');
    const segments = pathname.split('/').filter((segment) => segment !== '');
    const filteredSegments = segments.filter((segment) => segment !== 'admin-panel');

    const breadcrumbItems: BreadcrumbItem[] = [];

    filteredSegments.forEach((segment, index) => {
        const segmentPath = '/admin-panel/' + filteredSegments.slice(0, index + 1).join('/');
        let displayName = getBreadcrumbDisplayName(segment);

        // If it's the last segment and has query parameters, enhance the display name
        if (index === filteredSegments.length - 1 && queryString) {
            const urlParams = new URLSearchParams(queryString);
            const id = urlParams.get('id');

            if (id) {
                // Show first 8 characters of ID for readability
                displayName = `${displayName} (${id})`;
            }
        }

        breadcrumbItems.push({
            name: displayName,
            url: segmentPath,
            isLast: index === filteredSegments.length - 1,
        });
    });

    return breadcrumbItems;
});

const getBreadcrumbDisplayName = (segment: string): string => {
    const displayNames: Record<string, string> = {
        dashboard: 'Dashboard',
        role: 'Role Management',
        user: 'User Management',
        transaction: 'Transactions',
        purchasing: 'Purchasing',
        product: 'Products',
        create: 'Create',
        update: 'Update',
        edit: 'Edit',
        view: 'View',
        settings: 'Settings',
        profile: 'Profile',
    };

    return displayNames[segment] || segment.charAt(0).toUpperCase() + segment.slice(1);
};
</script>

<template>
    <nav
        class="flex rounded-lg border border-gray-200 bg-gray-50 px-5 py-3 text-gray-700 dark:border-gray-700 dark:bg-gray-800"
        aria-label="Breadcrumb"
    >
        <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">
            <!-- Home breadcrumb -->
            <li class="inline-flex items-center">
                <Link
                    :href="homeUrl"
                    class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white"
                >
                    <Home class="mr-2 h-4 w-4" />
                    {{ homeLabel }}
                </Link>
            </li>

            <!-- Dynamic breadcrumb items -->
            <li v-for="(crumb, index) in breadcrumbItems" :key="index">
                <div class="flex items-center">
                    <ChevronRight class="mx-1 h-4 w-4 text-gray-400" />

                    <span v-if="crumb.isLast" class="text-sm font-medium text-gray-500 dark:text-gray-400" aria-current="page">
                        {{ crumb.name }}
                    </span>

                    <Link
                        v-else
                        :href="crumb.url"
                        class="text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white"
                    >
                        {{ crumb.name }}
                    </Link>
                </div>
            </li>
        </ol>
    </nav>
</template>
