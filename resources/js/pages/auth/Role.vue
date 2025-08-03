p
<script setup lang="ts">
import { useNotyf } from '@/composables/useNotyf';
import { useSwal } from '@/composables/useSwal';
import { Link, router, usePage } from '@inertiajs/vue3';
import DataTablesLib from 'datatables.net';
import DataTable from 'datatables.net-vue3';
import { ref } from 'vue';
import AuthLayout from '../layout/AuthLayout.vue';

// Initialize Notyf and Swal
const notyf = useNotyf();
const swal = useSwal();

const page = usePage();
// Register DataTable with the library
const MainTable = DataTable;
MainTable.use(DataTablesLib);

defineOptions({ layout: AuthLayout });

const tableRef = ref();
const error = ref('');

// Define columns
const columns = [
    { data: 'id', title: 'ID' },
    { data: 'name', title: 'Role Name' },
    { data: 'description', title: 'Description' },
    {
        data: null,
        title: 'Actions',
        orderable: false,
        searchable: false,
        render: function (data: any, type: string, row: any) {
            return `
                <div class="flex gap-1">
                    <button
                        class="action-btn inline-flex items-center justify-center w-8 h-8 text-cyan-600 bg-cyan-100 border border-cyan-200 rounded-lg hover:bg-cyan-200 focus:ring-4 focus:ring-cyan-300 focus:outline-none dark:bg-cyan-800 dark:text-cyan-300 dark:border-cyan-600 dark:hover:bg-cyan-700 dark:focus:ring-cyan-900 transition-all duration-200"
                        data-action="edit"
                        data-id="${row.id}"
                        title="Edit User"
                        type="button"
                    >
                        <svg class="w-4 h-4 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </button>
                    <button
                        class="action-btn inline-flex items-center justify-center w-8 h-8 text-red-600 bg-red-100 border border-red-200 rounded-lg hover:bg-red-200 focus:ring-4 focus:ring-red-300 focus:outline-none dark:bg-red-800 dark:text-red-300 dark:border-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900 transition-all duration-200"
                        data-action="delete"
                        data-id="${row.id}"
                        title="Delete User"
                        type="button"
                    >
                        <svg class="w-4 h-4 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
            `;
        },
    },
];

// DataTable options with server-side processing
const options = {
    responsive: true,
    pageLength: 10,
    processing: true,
    serverSide: true,
    ajax: {
        url: `${page.props.ziggy.location}/all`,
        type: 'GET',
        data: function (d: any) {
            return {
                draw: d.draw,
                start: d.start,
                length: d.length,
                search: d.search.value,
                order_column: d.columns[d.order[0]?.column]?.data || 'id',
                order_dir: d.order[0]?.dir || 'asc',
            };
        },
        dataSrc: function (json: any) {
            return json.data;
        },
        error: function (xhr: any, errorMsg: string, code: string) {
            console.error('DataTable AJAX error:', errorMsg, code);
            notyf.error('Failed to load data');
            error.value = 'Failed to load data';
        },
    },
    language: {
        processing: '',
        search: 'Cari:',
        lengthMenu: 'Tampilkan _MENU_ data per halaman',
        info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
        paginate: {
            first: '|<',
            last: '>|',
            next: '>',
            previous: '<',
        },
        emptyTable: 'No data available in table',
        zeroRecords: 'No matching records found',
    },
};

// Handle table actions
const handleTableClick = (event: Event) => {
    const target = event.target as HTMLElement;
    const action = target.getAttribute('data-action');
    const id = target.getAttribute('data-id');

    if (action && id) {
        if (action === 'edit') {
            editData(id);
        } else if (action === 'delete') {
            deleteData(id);
        }
    }
};

const editData = (id: string) => {
    router.visit(`${page.props.ziggy.location}/update?id=${id}`, {
        preserveState: true,
        preserveScroll: true,
    });
};

const deleteData = async (id: string) => {
    const confirm = await swal.confirmation();
    if (confirm) {
        try {
            await router.delete(`${page.props.ziggy.location}/delete`, {
                data: { ids: [id] },
            });

            // Reload DataTable
            if (tableRef.value && tableRef.value.dt) {
                tableRef.value.dt.ajax.reload(
                    null,
                    false, // Keep the current page
                );
            }
        } catch (error) {
            console.error('Error deleting role:', error);
            notyf.error(error instanceof Error ? error.message : 'Failed to delete role');
        }
    }
};
</script>

<template>
    <div class="relative z-20">
        <div class="mb-4">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Role Management</h1>
            <p class="text-gray-600 dark:text-gray-400">Manage user roles and permissions</p>
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow-md dark:bg-gray-800">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 p-4 dark:border-gray-700">
                <div class="flex gap-3">
                    <button
                        type="button"
                        class="rounded-lg border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-900 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white dark:focus:ring-gray-700"
                    >
                        Export
                    </button>
                    <button
                        type="button"
                        class="rounded-lg border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-900 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white dark:focus:ring-gray-700"
                    >
                        Import
                    </button>
                </div>
                <Link
                    :href="`${page.props.ziggy.location}/create`"
                    class="rounded-lg border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-900 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white dark:focus:ring-gray-700"
                >
                    Create
                </Link>
            </div>

            <div class="p-4">
                <div class="mb-4 text-red-500">
                    {{ error }}
                </div>

                <div class="overflow-x-auto">
                    <MainTable ref="tableRef" :columns="columns" :options="options" @click="handleTableClick" class="display w-full" />
                </div>
            </div>
        </div>
    </div>
</template>
