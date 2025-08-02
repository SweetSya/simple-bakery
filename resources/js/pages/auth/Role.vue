<script setup lang="ts">
import { useNotyf } from '@/composables/useNotyf';
import { useSwal } from '@/composables/useSwal';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
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
const loading = ref(false);
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
                <button
                    class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded mr-2"
                    data-action="edit"
                    data-id="${row.id}"
                >
                    Edit
                </button>
                <button
                    class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded"
                    data-action="delete"
                    data-id="${row.id}"
                >
                    Delete
                </button>
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
            first: 'Pertama',
            last: 'Terakhir',
            next: 'Selanjutnya',
            previous: 'Sebelumnya',
        },
        emptyTable: 'Tidak ada data tersedia',
        zeroRecords: 'Tidak ditemukan data yang sesuai',
    },
};

// Handle table actions
const handleTableClick = (event: Event) => {
    const target = event.target as HTMLElement;
    const action = target.getAttribute('data-action');
    const id = target.getAttribute('data-id');

    if (action && id) {
        if (action === 'edit') {
            editRole(id);
        } else if (action === 'delete') {
            deleteRole(id);
        }
    }
};

const editRole = (id: string) => {
    console.log('Edit role:', id);
    // Your edit logic here
};

const deleteRole = async (id: string) => {
    const confirm = await swal.confirmation();
    if (confirm) {
        try {
            console.log(page.props.csrfToken);
            // Send CSRF token in headers, not in data
            axios.delete(`${page.props.ziggy.location}/delete`, {
                data: { ids: [id] },
            });

            notyf.success('Role deleted successfully');

            // Reload DataTable
            if (tableRef.value && tableRef.value.dt) {
                tableRef.value.dt.ajax.reload();
            }
        } catch (error) {
            console.error('Error deleting role:', error);
            notyf.error(error.message || 'Failed to delete role');
        }
    }
};
</script>

<template>
    <div class="p-6">
        <div class="mb-4">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Role Management</h1>
            <p class="text-gray-600 dark:text-gray-400">Manage user roles and permissions</p>
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow-md dark:bg-gray-800">
            <div class="flex flex-wrap items-center justify-start gap-3 border-b border-gray-200 p-4 dark:border-gray-700">
                <button
                    type="button"
                    class="rounded-lg border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-900 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white dark:focus:ring-gray-700"
                >
                    Create
                </button>
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

            <div class="p-4">
                <div class="mb-4 text-red-500">
                    {{ error }}
                </div>

                <MainTable ref="tableRef" :columns="columns" :options="options" @click="handleTableClick" class="display w-full" />
            </div>
        </div>
    </div>
</template>
