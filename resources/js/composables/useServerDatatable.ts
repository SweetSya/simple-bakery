import DataTable from 'datatables.net-dt';
import 'datatables.net-dt/css/dataTables.dataTables.css';
import { nextTick, onMounted, onUnmounted, ref } from 'vue';

interface DatatableConfig {
    url?: string;
    columns: any[];
    options?: any;
    data?: any[];
    tableId: string;
}

export function useServerDatatable(config: DatatableConfig) {
    const tableRef = ref<HTMLTableElement>();
    const data = ref(config.data || []);
    const loading = ref(false);
    const error = ref('');
    let datatableInstance: DataTable | null = null;

    const defaultOptions = {
        responsive: true,
        pageLength: 10,
        processing: true,
        serverSide: !!config.url,
        language: {
            processing: 'Memproses...',
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
        layout: {
            topStart: 'pageLength',
            topEnd: 'search',
            bottomStart: 'info',
            bottomEnd: 'paging',
        },
        ...config.options,
    };

    // Server-side processing configuration
    if (config.url) {
        defaultOptions.ajax = {
            url: config.url,
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
                return json.data || [];
            },
            error: function (xhr: any, error: string, code: string) {
                console.error('DataTable AJAX error:', error, code);
                error.value = `Failed to load data: ${error}`;
            },
        };
    }

    const initializeDataTable = async () => {
        if (!tableRef.value) {
            console.warn('Table ref not available');
            return;
        }

        try {
            loading.value = true;
            error.value = '';

            await nextTick();

            // Destroy existing instance if exists
            if (datatableInstance) {
                datatableInstance.destroy();
                datatableInstance = null;
            }

            // Clear table content
            tableRef.value.innerHTML = '';

            // Initialize new DataTable
            const options = {
                ...defaultOptions,
                columns: config.columns,
                data: config.url ? undefined : data.value,
            };

            datatableInstance = new DataTable(tableRef.value, options);

            console.log(`DataTable ${config.tableId} initialized successfully`);
        } catch (err) {
            console.error(`Error initializing DataTable ${config.tableId}:`, err);
            error.value = `Failed to initialize table: ${err}`;
        } finally {
            loading.value = false;
        }
    };

    const refreshTable = () => {
        if (datatableInstance) {
            if (config.url) {
                // Server-side: reload from server
                datatableInstance.ajax.reload();
            } else {
                // Client-side: clear and add new data
                datatableInstance.clear();
                datatableInstance.rows.add(data.value);
                datatableInstance.draw();
            }
        }
    };

    const updateData = (newData: any[]) => {
        data.value = newData;
        if (!config.url && datatableInstance) {
            datatableInstance.clear();
            datatableInstance.rows.add(newData);
            datatableInstance.draw();
        }
    };

    const addRow = (rowData: any) => {
        if (datatableInstance) {
            datatableInstance.row.add(rowData).draw();
        }
        if (!config.url) {
            data.value.push(rowData);
        }
    };

    const removeRow = (selector: any) => {
        if (datatableInstance) {
            datatableInstance.row(selector).remove().draw();
        }
    };

    const removeRowByIndex = (index: number) => {
        if (datatableInstance) {
            datatableInstance.row(index).remove().draw();
        }
        if (!config.url && data.value[index]) {
            data.value.splice(index, 1);
        }
    };

    const getRowData = (selector: any) => {
        if (datatableInstance) {
            return datatableInstance.row(selector).data();
        }
        return null;
    };

    const search = (value: string) => {
        if (datatableInstance) {
            datatableInstance.search(value).draw();
        }
    };

    const destroy = () => {
        if (datatableInstance) {
            datatableInstance.destroy(true); // Remove from DOM
            datatableInstance = null;
        }
    };

    onMounted(() => {
        // Small delay to ensure DOM is ready
        setTimeout(() => {
            initializeDataTable();
        }, 100);
    });

    onUnmounted(() => {
        destroy();
    });

    return {
        tableRef,
        data,
        loading,
        error,
        initializeDataTable,
        refreshTable,
        updateData,
        addRow,
        removeRow,
        removeRowByIndex,
        getRowData,
        search,
        destroy,
        getInstance: () => datatableInstance,
    };
}
