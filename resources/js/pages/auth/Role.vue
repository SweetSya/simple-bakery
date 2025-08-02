<script setup lang="ts">
import { useNotyf } from '@/composables/useNotyf';
import { useSwal } from '@/composables/useSwal';
import { useForm, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import DataTablesLib from 'datatables.net';
import DataTable from 'datatables.net-vue3';
import { PlusSquare, X } from 'lucide-vue-next';
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
const create = useForm({
    name: '',
    description: '',
});

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

const createData = async () => {
    create.clearErrors();
    create.processing = true;

    try {
        await create.post(`${page.props.ziggy.location}/create`, {
            onSuccess: () => {
                if (tableRef.value && tableRef.value.dt) {
                    tableRef.value.dt.ajax.reload(
                        null,
                        false, // Keep the current page
                    );
                }
                create.reset();
            },
            onError: (errors) => {
                if (errors.name) {
                    notyf.error(errors.name);
                }
                if (errors.description) {
                    notyf.error(errors.description);
                }
            },
        });
    } catch (error) {
        console.error('Error creating role:', error);
        notyf.error(error.message || 'Failed to create role');
    } finally {
        create.processing = false;
    }
};
const editData = (id: string) => {
    console.log('Edit role:', id);
    // Your edit logic here
};

const deleteData = async (id: string) => {
    const confirm = await swal.confirmation();
    if (confirm) {
        try {
            console.log(page.props.csrfToken);
            // Send CSRF token in headers, not in data
            axios.delete(`${page.props.ziggy.location}/delete`, {
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
            notyf.error(error.message || 'Failed to delete role');
        }
    }
};
</script>

<template>
    <div class="relative z-20 p-6">
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
                <button
                    type="button"
                    data-drawer-target="drawer-create"
                    data-drawer-show="drawer-create"
                    data-drawer-placement="right"
                    aria-controls="drawer-create"
                    class="rounded-lg border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-900 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white dark:focus:ring-gray-700"
                >
                    Create
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
    <div
        id="drawer-create"
        class="fixed top-0 right-0 z-40 h-screen w-80 translate-x-full overflow-y-auto bg-white p-4 transition-transform dark:bg-gray-800"
        tabindex="-1"
        aria-labelledby="drawer-create-label"
    >
        <h5 id="drawer-label" class="mb-6 inline-flex items-center text-base font-semibold text-gray-500 uppercase dark:text-gray-400">
            <PlusSquare class="mr-2 h-5 w-5 text-gray-500" />Create
        </h5>
        <button
            type="button"
            data-drawer-hide="drawer-form"
            aria-controls="drawer-form"
            class="absolute end-2.5 top-2.5 inline-flex h-8 w-8 items-center justify-center rounded-lg bg-transparent text-sm text-gray-400 hover:bg-gray-200 hover:text-gray-900 dark:hover:bg-gray-600 dark:hover:text-white"
        >
            <X class="h-5 w-5 text-gray-500" />
            <span class="sr-only">Close menu</span>
        </button>
        <form class="mb-6" @submit.prevent="createData()">
            <div class="mb-6">
                <label for="name" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Name</label>
                <input
                    type="text"
                    id="name"
                    v-model="create.name"
                    class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                    placeholder="Apple Keynote"
                    required
                />
                <div v-if="create.errors.name" class="mt-1 text-sm text-red-600">{{ create.errors.name }}</div>
            </div>
            <div class="mb-6">
                <label for="description" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Description</label>
                <textarea
                    id="description"
                    v-model="create.description"
                    rows="4"
                    class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                    placeholder="Write event description..."
                ></textarea>
                <div v-if="create.errors.description" class="mt-1 text-sm text-red-600">{{ create.errors.description }}</div>
            </div>

            <div class="absolute right-0 bottom-0 left-0 p-4">
                <button
                    type="submit"
                    :disabled="create.processing"
                    class="mb-2 flex w-full items-center justify-center rounded-lg bg-blue-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 focus:outline-none dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
                >
                    {{ create.processing ? 'Processing...' : 'Save' }}
                </button>
            </div>
        </form>
    </div>
</template>
