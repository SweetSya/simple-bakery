<!-- filepath: d:\Programming\WebDev\simple-bakery\resources\js\pages\auth\User.vue -->
<script setup lang="ts">
import { useNotyf } from '@/composables/useNotyf';
import { useSwal } from '@/composables/useSwal';
import { Link, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import DataTablesLib from 'datatables.net';
import DataTable from 'datatables.net-vue3';
import type { InstanceOptions, ModalInterface, ModalOptions } from 'flowbite';
import { Modal } from 'flowbite';
import { X } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, reactive, ref } from 'vue';
import AuthLayout from '../layout/AuthLayout.vue';

// Initialize Notyf and Swal
const notyf = useNotyf();
const swal = useSwal();

const page = usePage();
const MainTable = DataTable;
MainTable.use(DataTablesLib);

defineOptions({ layout: AuthLayout });

const tableRef = ref();
const error = ref('');
const selectedRows = ref<string[]>([]);
const selectAll = ref(false);

// Define columns with checkbox
const columns = [
    {
        data: null,
        title: `<input type="checkbox" id="select-all" class="select-all-checkbox">`,
        orderable: false,
        searchable: false,
        width: '20px',
        render: function (data: any, type: string, row: any) {
            return `<input type="checkbox" class="row-checkbox " data-id="${row.id}" value="${row.id}">`;
        },
    },
    { data: 'id', title: 'ID' },
    { data: 'name', title: 'User Name' },
    { data: 'email', title: 'Email' },
    {
        data: 'role',
        title: 'Role',
        render: function (data: any) {
            return data ? data.name : '<span class="text-gray-400">No Role</span>';
        },
    },
    {
        data: 'email_verified_at',
        title: 'Email Verified',
        render: function (data: any) {
            if (data) {
                return `<span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-300">
                    Verified
                </span>`;
            } else {
                return `<span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900 dark:text-red-300">
                    Unverified
                </span>`;
            }
        },
    },
    {
        data: 'created_at',
        title: 'Created At',
        render: function (data: any) {
            return data ? new Date(data).toLocaleDateString() : '';
        },
    },
    {
        data: null,
        title: 'Actions',
        orderable: false,
        searchable: false,
        render: function (data: any, type: string, row: any) {
            return `
                <div class="flex gap-1">
                    <button
                        class="action-btn inline-flex items-center justify-center w-8 h-8 text-blue-600 bg-blue-100 border border-blue-200 rounded-lg hover:bg-blue-200 focus:ring-4 focus:ring-blue-300 focus:outline-none dark:bg-blue-800 dark:text-blue-300 dark:border-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-900 transition-all duration-200"
                        data-action="view"
                        data-id="${row.id}"
                        title="View User"
                        type="button"
                    >
                        <svg class="w-4 h-4 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </button>
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

// DataTable options
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
    drawCallback: function () {
        // Re-attach event listeners after each redraw
        attachCheckboxListeners();
        maintainSelections();
    },
};
// Function to maintain selections across pagination
const maintainSelections = () => {
    setTimeout(() => {
        const rowCheckboxes = document.querySelectorAll('.row-checkbox') as NodeListOf<HTMLInputElement>;

        rowCheckboxes.forEach((checkbox) => {
            const id = checkbox.getAttribute('data-id');
            if (id && selectedRows.value.includes(id)) {
                checkbox.checked = true;
            }
        });

        updateSelectAllState();
    }, 100);
};
// Checkbox management functions
const attachCheckboxListeners = () => {
    // Handle select all checkbox
    const selectAllCheckbox = document.getElementById('select-all') as HTMLInputElement;
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', handleSelectAll);
    }

    // Handle individual row checkboxes
    const rowCheckboxes = document.querySelectorAll('.row-checkbox') as NodeListOf<HTMLInputElement>;
    rowCheckboxes.forEach((checkbox) => {
        checkbox.addEventListener('change', handleRowSelection);
    });

    // Update select all state based on current selections
    updateSelectAllState();
};
const handleSelectAll = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const isChecked = target.checked;

    const rowCheckboxes = document.querySelectorAll('.row-checkbox') as NodeListOf<HTMLInputElement>;
    rowCheckboxes.forEach((checkbox) => {
        checkbox.checked = isChecked;
        const id = checkbox.getAttribute('data-id');
        if (id) {
            if (isChecked && !selectedRows.value.includes(id)) {
                selectedRows.value.push(id);
            } else if (!isChecked) {
                const index = selectedRows.value.indexOf(id);
                if (index > -1) {
                    selectedRows.value.splice(index, 1);
                }
            }
        }
    });

    selectAll.value = isChecked;
};

const handleRowSelection = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const id = target.getAttribute('data-id');

    if (id) {
        if (target.checked && !selectedRows.value.includes(id)) {
            selectedRows.value.push(id);
        } else if (!target.checked) {
            const index = selectedRows.value.indexOf(id);
            if (index > -1) {
                selectedRows.value.splice(index, 1);
            }
        }
    }

    updateSelectAllState();
};

const updateSelectAllState = () => {
    const selectAllCheckbox = document.getElementById('select-all') as HTMLInputElement;
    const rowCheckboxes = document.querySelectorAll('.row-checkbox') as NodeListOf<HTMLInputElement>;

    if (selectAllCheckbox && rowCheckboxes.length > 0) {
        const checkedCount = Array.from(rowCheckboxes).filter((cb) => cb.checked).length;
        selectAllCheckbox.checked = checkedCount === rowCheckboxes.length;
        selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < rowCheckboxes.length;
        selectAll.value = selectAllCheckbox.checked;
    }
};

// Action handlers
const handleTableClick = (event: Event) => {
    const target = event.target as HTMLElement;
    const action = target.getAttribute('data-action');
    const id = target.getAttribute('data-id');

    if (action && id) {
        if (action === 'view') {
            viewData(id);
        } else if (action === 'edit') {
            editData(id);
        } else if (action === 'delete') {
            deleteData(id);
        }
    }
};

const viewData = (id: string) => {
    router.visit(`${page.props.ziggy.location}/detail?id=${id}`, {
        preserveState: true,
        preserveScroll: true,
    });
};

const editData = (id: string) => {
    router.visit(`${page.props.ziggy.location}/update?id=${id}`, {
        preserveState: true,
        preserveScroll: true,
    });
};

const deleteData = async (id: string) => {
    const confirm = await swal.confirmation({
        title: 'Delete User',
        text: 'Are you sure you want to delete this user?',
        icon: 'warning',
    });

    if (confirm) {
        try {
            await router.delete(`${page.props.ziggy.location}/delete`, {
                data: { ids: [id] },
                onSuccess: () => {
                    if (tableRef.value && tableRef.value.dt) {
                        tableRef.value.dt.ajax.reload(null, false);
                    }
                    // Remove from selected rows
                    const index = selectedRows.value.indexOf(id);
                    if (index > -1) {
                        selectedRows.value.splice(index, 1);
                    }
                },
            });
        } catch (error) {
            console.error('Error deleting user:', error);
            notyf.error('Failed to delete user');
        }
    }
};

// Bulk actions
const deleteSelected = async () => {
    if (selectedRows.value.length === 0) {
        notyf.error('Please select users to delete');
        return;
    }

    const confirm = await swal.confirmation({
        title: 'Delete Selected Users',
        text: `Are you sure you want to delete ${selectedRows.value.length} selected user(s)?`,
        icon: 'warning',
    });

    if (confirm) {
        try {
            await router.delete(`${page.props.ziggy.location}/delete`, {
                data: { ids: selectedRows.value },
                onSuccess: () => {
                    if (tableRef.value && tableRef.value.dt) {
                        tableRef.value.dt.ajax.reload(null, false);
                    }
                },
            });
        } catch (error) {
            console.error('Error deleting users:', error);
            notyf.error('Failed to delete selected users');
        }
    }
};
// Modal options
const modalOptions: ModalOptions = {
    placement: 'center',
    backdrop: 'dynamic',
    backdropClasses: 'bg-gray-900/50 dark:bg-gray-900/80 fixed inset-0 z-40',
    closable: true,
    onHide: () => {
        console.log('modal is hidden');
    },
    onShow: () => {
        console.log('modal is shown');
    },
    onToggle: () => {
        console.log('modal has been toggled');
    },
};
// Reactive modal state
const modalExportState = reactive({
    isOpen: false,
    isAll: false,
    exportFormat: 'csv',
    exportType: 'selected', // 'selected' or 'all'
    exportFields: [],
    isLoading: false,
    title: 'Export Users',
});

// Reactive modal instance
const modalInstance = ref<ModalInterface | null>(null);

// Computed properties for modal
const selectedCount = computed(() => selectedRows.value.length);
const hasSelectedItems = computed(() => selectedRows.value.length > 0);
const columnsToExport = computed(() => [
    { label: 'ID', value: 'id' },
    { label: 'Name', value: 'name' },
    { label: 'Email', value: 'email' },
    { label: 'Role', value: 'role.name' },
    { label: 'Email Verified', value: 'email_verified_at' },
    { label: 'Created At', value: 'created_at' },
]);

// Export format options
const exportFormats = reactive([
    { value: 'csv', label: 'CSV (.csv)', description: 'Comma-separated values file' },
    { value: 'excel', label: 'Excel (.xlsx)', description: 'Microsoft Excel spreadsheet' },
    { value: 'pdf', label: 'PDF (.pdf)', description: 'Portable document format' },
]);

// Modal functions
const openExportModal = (type: 'selected' | 'all') => {
    modalExportState.exportType = type;
    modalExportState.title = type === 'selected' ? `Export ${selectedCount.value} Selected Users` : 'Export All Users';

    if (modalInstance.value) {
        modalInstance.value.show();
        modalExportState.isOpen = true;
    }
};

const closeExportModal = () => {
    if (modalInstance.value) {
        modalInstance.value.hide();
        modalExportState.isOpen = false;
        // Reset form
        modalExportState.exportFormat = 'csv';
        modalExportState.isLoading = false;
    }
};

const handleExport = async () => {
    if (modalExportState.exportType === 'selected' && selectedRows.value.length === 0) {
        notyf.error('Please select users to export');
        return;
    }

    modalExportState.isLoading = true;

    try {
        const requestData = {
            all: modalExportState.isAll,
            format: modalExportState.exportFormat,
            fields: modalExportState.exportFields,
            ...(modalExportState.exportType === 'selected' ? { ids: selectedRows.value } : { export_all: true }),
        };

        const response = await axios.post(`${page.props.ziggy.location}/export`, requestData);

        if (response.status === 200) {
            const successMessage = `${response.data.message}( ${selectedCount.value} rows )`;

            notyf.success(successMessage);
            closeExportModal();
        } else {
            throw new Error(response.data.message || 'Failed to export users');
        }
    } catch (error) {
        console.error('Error exporting users:', error);
        notyf.error(error.message || 'Failed to export users');
    } finally {
        modalExportState.isLoading = false;
    }
};
const handleClearAll = () => {
    selectedRows.value = [];
    const rowCheckboxes = document.querySelectorAll('.row-checkbox') as NodeListOf<HTMLInputElement>;
    rowCheckboxes.forEach((checkbox) => {
        checkbox.checked = false;
    });
    updateSelectAllState();
};

// Updated export functions to use modal
const exportSelected = () => {
    if (selectedRows.value.length === 0) {
        notyf.error('Please select users to export');
        return;
    }
    openExportModal('selected');
};

const exportAll = () => {
    modalExportState.isAll = true;
    openExportModal('all');
};
// Lifecycle hooks
onMounted(() => {
    const modalExportElement = document.querySelector('#modal-export') as HTMLElement;

    if (modalExportElement) {
        const instanceOptions: InstanceOptions = {
            id: 'modal-export',
            override: true,
        };

        modalInstance.value = new Modal(modalExportElement, modalOptions, instanceOptions);
    } else {
        console.error('Modal element not found');
    }

    setTimeout(() => {
        attachCheckboxListeners();
    }, 500);
});

onUnmounted(() => {
    // Cleanup
    selectedRows.value = [];
});
</script>

<template>
    <div class="relative z-20">
        <div class="mb-4">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">User Management</h1>
            <p class="text-gray-600 dark:text-gray-400">Manage user accounts and permissions</p>
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow-md dark:bg-gray-800">
            <!-- Action Bar -->
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 p-4 dark:border-gray-700">
                <div class="flex gap-3">
                    <!-- Bulk Actions (show when items are selected) -->
                    <div v-if="selectedRows.length > 0" class="flex gap-2">
                        <span
                            class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-sm font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-300"
                        >
                            {{ selectedRows.length }} selected <X class="ml-4 cursor-pointer" @click="handleClearAll" />
                        </span>
                        <button
                            @click="exportSelected"
                            type="button"
                            class="rounded-lg border border-green-200 bg-green-50 px-4 py-2 text-sm font-medium text-green-700 hover:bg-green-100 focus:ring-4 focus:ring-green-300 focus:outline-none dark:border-green-600 dark:bg-green-800 dark:text-green-300 dark:hover:bg-green-700 dark:focus:ring-green-900"
                        >
                            Export Selected
                        </button>
                        <button
                            @click="deleteSelected"
                            type="button"
                            class="rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-100 focus:ring-4 focus:ring-red-300 focus:outline-none dark:border-red-600 dark:bg-red-800 dark:text-red-300 dark:hover:bg-red-700 dark:focus:ring-red-900"
                        >
                            Delete Selected
                        </button>
                    </div>

                    <!-- Regular Actions -->
                    <div v-else class="flex gap-2">
                        <button
                            @click="exportAll"
                            type="button"
                            class="rounded-lg border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-900 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white dark:focus:ring-gray-700"
                        >
                            Export All
                        </button>
                        <button
                            type="button"
                            class="rounded-lg border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-900 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white dark:focus:ring-gray-700"
                        >
                            Import
                        </button>
                    </div>
                </div>

                <Link
                    :href="`${page.props.ziggy.location}/create`"
                    class="rounded-lg bg-blue-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 focus:outline-none dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
                >
                    Create User
                </Link>
            </div>

            <!-- Table -->
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
    <div
        id="modal-export"
        class="fixed top-0 right-0 left-0 z-50 hidden h-[calc(100%-1rem)] max-h-full w-full items-center justify-center overflow-x-hidden overflow-y-auto md:inset-0"
    >
        <div class="relative max-h-full w-full max-w-md p-4">
            <!-- Modal content -->
            <div class="relative rounded-lg bg-white shadow-lg dark:bg-gray-700">
                <!-- Modal header -->
                <div class="flex items-center justify-between rounded-t border-b border-gray-200 p-4 dark:border-gray-600">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ modalExportState.title }}
                    </h3>
                    <button
                        type="button"
                        @click="closeExportModal"
                        class="ms-auto inline-flex h-8 w-8 items-center justify-center rounded-lg bg-transparent text-sm text-gray-400 hover:bg-gray-200 hover:text-gray-900 dark:hover:bg-gray-600 dark:hover:text-white"
                    >
                        <X class="h-4 w-4" />
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>

                <!-- Modal body -->
                <div class="space-y-4 p-4">
                    <div v-if="modalExportState.exportType === 'selected'" class="rounded-lg bg-blue-50 p-3 dark:bg-blue-900/20">
                        <p class="text-sm text-blue-800 dark:text-blue-300">
                            You are about to export <strong>{{ selectedCount }}</strong> selected user(s).
                        </p>
                    </div>

                    <div v-else class="rounded-lg bg-amber-50 p-3 dark:bg-amber-900/20">
                        <p class="text-sm text-amber-800 dark:text-amber-300">
                            You are about to export <strong>all users</strong> from the database.
                        </p>
                    </div>

                    <!-- Format Selection -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white"> Choose Export Format </label>
                        <div class="mb-3 space-y-2">
                            <div
                                v-for="format in exportFormats"
                                :key="format.value"
                                class="flex w-full items-center rounded-lg border border-gray-200 bg-white p-2 shadow-sm dark:border-gray-700 dark:bg-gray-800"
                            >
                                <input
                                    :id="`format-${format.value}`"
                                    v-model="modalExportState.exportFormat"
                                    :value="format.value"
                                    type="radio"
                                    class="h-4 w-4 border-gray-300 bg-gray-100 text-blue-600 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:ring-offset-gray-800 dark:focus:ring-blue-600"
                                />
                                <label :for="`format-${format.value}`" class="ml-2 block">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-300">
                                        {{ format.label }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ format.description }}
                                    </div>
                                </label>
                            </div>
                        </div>
                        <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white"> Choose Field to Export</label>
                        <div class="flex flex-wrap gap-1">
                            <div
                                v-for="column in columnsToExport"
                                :key="column.value"
                                class="flex grow items-center rounded-lg border border-gray-200 bg-white p-2 shadow-sm dark:border-gray-700 dark:bg-gray-800"
                            >
                                <input
                                    :id="`checkbox-${column.value}`"
                                    type="checkbox"
                                    v-model="modalExportState.exportFields"
                                    :value="column.value"
                                    class="h-4 w-4 rounded-sm border-gray-300 bg-gray-100 text-blue-600 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:ring-offset-gray-800 dark:focus:ring-blue-600"
                                />
                                <label :for="`checkbox-${column.value}`" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">{{
                                    column.label
                                }}</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal footer -->
                <div class="flex items-center justify-end gap-3 rounded-b border-t border-gray-200 p-4 dark:border-gray-600">
                    <button
                        @click="closeExportModal"
                        type="button"
                        :disabled="modalExportState.isLoading"
                        class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-900 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 focus:outline-none disabled:opacity-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white dark:focus:ring-gray-700"
                    >
                        Cancel
                    </button>
                    <button
                        @click="handleExport"
                        type="button"
                        :disabled="modalExportState.isLoading"
                        class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-medium text-white hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 focus:outline-none disabled:opacity-50 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
                    >
                        <span v-if="modalExportState.isLoading" class="flex items-center">
                            <svg
                                class="mr-2 -ml-1 h-4 w-4 animate-spin text-white"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                ></path>
                            </svg>
                            Exporting...
                        </span>
                        <span v-else>Export {{ modalExportState.exportFormat.toUpperCase() }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
