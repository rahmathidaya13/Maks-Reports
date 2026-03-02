<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { debounce } from "lodash";
import { highlight } from "@/helpers/highlight";
import { formatText } from "@/helpers/formatText";
import CancelledPreviewModal from "./Modal/CancelledPreviewModal.vue";
import TransactionDetailModal from "./Modal/TransactionDetailModal.vue";
import ModalExport from "./Modal/ModalExport.vue";
import { hasRole, hasPermission } from "@/composables/useAuth";
import moment from "moment";
moment.locale("id");

import { useConfirm } from "@/helpers/useConfirm.js"
const confirm = useConfirm(); // Memanggil fungsi confirm untuk alert delete

const props = defineProps({
    transaction: Object,
    filters: Object,
});
const filters = reactive({
    date_filter: props.filters.date_filter ?? null,
    status: props.filters.status ?? null,
    keyword: props.filters.keyword ?? "",
    limit: props.filters.limit ?? null,
    order_by: props.filters.order_by ?? null,
    page: props.filters?.page ?? 1,
});

const isLoading = ref(false);
const liveSearch = debounce((e) => {
    isLoading.value = true;
    router.get(route("transaction"), filters, {
        preserveScroll: true,
        replace: true,
        preserveState: true,
        only: ["transaction", "filters"],
        onFinish: () => (isLoading.value = false),
    });
}, 500);
watch(
    () => filters.keyword,
    () => {
        liveSearch();
    }
);
const handleApply = () => {
    liveSearch();
};
const handleReset = () => {
    // Reset object filters secara clean
    Object.assign(filters, {
        keyword: "",
        status: null,
        date_filter: null,
        limit: null,
        order_by: null
    });

    // Langsung cari data bersih
    liveSearch();
};


const header = [
    {
        label: "No",
        key: "__index",
        attrs: {
            class: "text-center",
            style: "width:50px"
        }
    },
    {
        label: "Invoice",
        key: "invoice",
        attrs: {
            class: "text-center",

        }
    },
    {
        label: "Pelanggan",
        key: "customers",
        attrs: {
            class: "text-start"
        }
    },
    {
        label: "Produk",
        key: "products",
        attrs: {
            class: "text-center"
        }
    },
    {
        label: "Total Tagihan",
        key: "payment",
        attrs: {
            class: "text-end"
        }
    },
    {
        label: "Status",
        key: "status",
        attrs: {
            class: "text-center"
        }
    },
    {
        label: "",
        key: "-",
        attrs: {
            class: "text-center",
        }
    }
];

// CRUD OPERATION
const loaderActive = ref(null);
const navigateTo = (routeName, params = {}, message = "Sedang membuka...") => {
    if (message) loaderActive.value?.show(message);
    router.get(route(routeName, params), {}, {
        onFinish: () => message && loaderActive.value?.hide(),
        preserveScroll: false,
        replace: true,
    });

}
const deleted = async (nameRoute, data) => {
    const setConfirm = await confirm.ask({
        title: 'Hapus',
        message: `Kamu ingin menghapus Data Transaksi dari pelanggan ${formatText(
            data.customer.customer_name
        )} ?`,
        confirmText: 'Ya, Hapus',
        variant: 'danger' // Memberikan warna merah pada tombol konfirmasi
    });

    if (setConfirm) {
        loaderActive.value?.show("Sedang menghapus data...");
        router.delete(route(nameRoute, data.transaction_id), {
            onFinish: () => loaderActive.value?.hide(),
            preserveScroll: false,
            replace: true
        });
    }
};
// end CRUD OPERATION

// MULTIPLE DELETE
const selectedRow = ref([]);
const isVisible = ref(false);
const deleteSelected = async () => {
    // 1. Kondisi Tidak Ada Data (Berfungsi sebagai Alert)
    if (!selectedRow.value.length) {
        return await confirm.ask({
            title: 'Perhatian',
            message: 'Silakan pilih minimal satu data untuk dihapus.',
            cancelText: 'Mengerti', // Ubah teks tombol tutup
            showButtonConfirm: false,
            variant: 'warning' // Gunakan warna kuning/orange untuk warning
        });
    }

    // 2. Kondisi Konfirmasi Hapus
    const setConfirm = await confirm.ask({
        title: 'Konfirmasi Hapus',
        message: `Apakah Anda yakin ingin menghapus ${selectedRow.value.length} data terpilih?`,
        confirmText: 'Ya, Hapus',
        cancelText: 'Batal',
        variant: 'danger'
    });

    // 3. Eksekusi
    if (setConfirm) {
        loaderActive.value?.show("Sedang menghapus data...");
        router.post(route("transaction.destroy_all"), {
            all_id: selectedRow.value
        }, {
            onFinish: () => {
                loaderActive.value?.hide();
                selectedRow.value = []; // Bersihkan pilihan setelah sukses
            },
            preserveScroll: true,
            preserveState: false,
        });
    }
}

watch(selectedRow, (val) => {
    if (val.length > 0) {
        isVisible.value = true;
    } else {
        isVisible.value = false;
    }
});
// END MULTIPLE DELETE


function formatCurrency(value) {
    if (!value) return "0";
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value);
}

// SETUP DATE
const getPaymentDate = (payments, type) => {
    if (!payments) return "-";
    const p = payments.find((p) => p.payment_type === type);
    return p ? moment(p.payment_date).format("DD/MM/YYYY") : "-";
};
function daysTranslate(dayValue) {
    const dayConvert = {
        Sunday: "Minggu",
        Monday: "Senin",
        Tuesday: "Selasa",
        Wednesday: "Rabu",
        Thursday: "Kamis",
        Friday: "Jumat",
        Saturday: "Sabtu",
    };
    const dayName = moment(dayValue).format("dddd");
    const dateFormat = moment(dayValue).format("DD/MM/YYYY");
    return dayConvert[dayName] + ", " + dateFormat ?? dayName;
}
// END DATE

// cek apakah ada filter yang terpilih
const hasActiveFilter = computed(() => {
    return (
        filters.keyword !== "" ||
        filters.status !== null ||
        filters.date_filter !== null ||
        filters.limit !== null ||
        filters.order_by !== null
    );
});
const filterFields = computed(() => [
    {
        key: "keyword",
        label: "Pencarian",
        col: "col-xl-8 col-12",
        type: "search",
        icon: "fas fa-search",
        autofocus: true,
        props: {
            placeholder: "Masukan invoice dan nama pelanggan...",
            inputClass: "border-start-0 ps-2 shadow-none",
        },
    },
    {
        key: "status",
        label: "Status",
        type: "select",
        col: "col-xl-4 col-md-6 col-6",
        icon: "fas fa-filter",
        props: {
            selectClass: "border-start-0 ps-2 shadow-none",
        },
        options: [
            { value: null, label: "Pilih Status Pembayaran" },
            { value: "all", label: "Semua Pembayaran" },
            { value: "repayment", label: "Lunas" },
            { value: "payment", label: "Belum Lunas" },
            { value: "cancelled", label: "Dibatalkan" },
        ],
    },
    {
        key: "date_filter",
        label: "Filter Tanggal",
        type: "date",
        col: "col-xl-4 col-md-6 col-6",
        icon: "fas fa-calendar-alt",
        props: {
            inputClass: "border-start-0 ps-2 shadow-none",
        },
    },
    {
        key: "limit",
        label: "Batas",
        type: "select",
        col: "col-xl-4 col-md-6 col-6",
        icon: "fas fa-list-ul",
        props: {
            selectClass: "border-start-0 ps-2 shadow-none",
        },
        options: [
            { value: null, label: "Pilih Batas Data" },
            { value: 10, label: "10 Baris" },
            { value: 20, label: "20 Baris" },
            { value: 30, label: "30 Baris" },
            { value: 50, label: "50 Baris" },
            { value: 100, label: "100 Baris" },
        ],
    },
    {
        key: "order_by",
        label: "Urutan",
        type: "select",
        col: "col-xl-4 col-md-6 col-6",
        icon: "fas fa-sort",
        props: {
            selectClass: "border-start-0 ps-2 shadow-none",
        },
        options: [
            { value: null, label: "Pilih Urutan" },
            { value: "desc", label: "Terbaru" },
            { value: "asc", label: "Terlama" },
        ],
    },
    //  button trigger
    {
        key: "reset",
        label: "Bersihkan",
        type: "button",
        name: "reset",
        icon: "fas fa-sync-alt",
        class: [!hasActiveFilter.value ? "btn-secondary" : "btn-outline-danger"],
        disabled: !hasActiveFilter.value,
        handler: () => handleReset(),
    },
    {
        key: "apply",
        label: "Terapkan",
        type: "button",
        name: "apply",
        icon: "fas fa-check",
        class: [!hasActiveFilter.value ? "btn-secondary" : "btn-primary"],
        disabled: !hasActiveFilter.value,
        handler: () => handleApply(),
    },
]);

const statusConfig = {
    payment: {
        label: "Belum Lunas",
        badge: "bg-warning bg-opacity-75 text-white",
        icon: "fas fa-clock",
    },
    repayment: {
        label: "Lunas",
        badge: "bg-success bg-opacity-75 text-white",
        icon: "fas fa-check-circle",
    },
    cancelled: {
        label: "Dibatalkan",
        badge: "bg-danger bg-opacity-75 text-white",
        icon: "fas fa-ban",
    },
};
const status = (status) => {
    return (
        statusConfig[status] ?? {
            label: "Unknown",
            badge: "bg-secondary",
            icon: "fas fa-question-circle",
        }
    );
};

const selectedData = ref(null);
const modals = reactive({
    cancelledPreview: false,
    transactionDetail: false,
    export: false,
});
const openModal = (type, data) => {
    selectedData.value = data;
    if (type === 'cancelled') modals.cancelledPreview = true;
    if (type === 'detail') modals.transactionDetail = true;
    if (type === 'export') modals.export = true;
}

// function for sub string
function subString(strValue, length) {
    const clean = strValue.replace(/-/g, "")
    return clean.length > length
        ? clean.substring(0, length)
        : clean
}
const reset = () => {
    isLoading.value = true;
    navigateTo('transaction.reset', {}, false);
}
const customClassTable = (item) => {
    return {
        'bg-danger bg-opacity-10': item.status === 'cancelled', // Dibatalkan
        'bg-warning bg-opacity-10': item.status === 'payment', // Dp
    }
}


const download = async (format) => {
    // Cek apakah melebihi batas maksimal
    if (selectedRow.value.length > 500) {
        return await confirm.ask({
            title: 'Perhatian',
            message: 'Data terlalu banyak untuk format ' + format.toUpperCase() + ' (>500). Silakan kurangi data yang akan diexport.',
            cancelText: 'Mengerti', // Ubah teks tombol tutup
            showButtonConfirm: false,
            variant: 'warning' // Gunakan warna kuning/orange untuk warning
        });
    }

    // Cek apakah ada data yang dipilih
    if (!selectedRow.value.length) {
        return await confirm.ask({
            title: 'Perhatian',
            message: 'Silakan pilih minimal satu data untuk diexport.',
            cancelText: 'Mengerti', // Ubah teks tombol tutup
            showButtonConfirm: false,
            variant: 'warning' // Gunakan warna kuning/orange untuk warning
        });
    }

    // Siapkan URL
    const url = route('transaction.export', {
        format: format,
        all_id: selectedRow.value.length > 0 ? selectedRow.value : null
    });

    // Buka di tab baru
    window.open(url, '_blank');
}
const toolbarActions = computed(() => [
    {
        label: `Hapus(${selectedRow.value.length})`,
        icon: 'fas fa-trash-alt',
        iconColor: 'text-danger',
        show: hasPermission('transaction.delete') && selectedRow.value.length > 0 && hasRole(['developer']),
        click: deleteSelected
    },
    {
        label: `PDF (${selectedRow.value.length})`,
        icon: 'fas fa-file-pdf',
        iconColor: 'text-danger',
        labelColor: 'text-danger',
        show: hasPermission('transaction.export'),
        disabled: !selectedRow.value.length,
        click: () => download('pdf')
    },
    {
        label: `Excel (${selectedRow.value.length})`,
        icon: 'fas fa-file-excel',
        iconColor: 'text-success',
        labelColor: 'text-success',
        show: hasPermission('transaction.export'),
        disabled: !selectedRow.value.length,
        click: () => download('excel')
    },
    {
        label: 'Transaksi Baru',
        icon: 'fas fa-plus-circle',
        isPrimary: true, // Prioritas Utama
        show: hasPermission('transaction.create'),
        click: () => navigateTo('transaction.create')
    },
    {
        label: 'Segarkan',
        icon: 'fas fa-redo-alt',
        iconColor: 'text-primary',
        loading: isLoading.value,
        click: () => reset()
    },
]);
</script>
<template>

    <Head title="Halaman Transaksi" />
    <app-layout>
        <template #content>
            <loader-page ref="loaderActive" />
            <bread-crumbs :home="false" icon="fas fa-money-bill" title="Daftar Transaksi"
                :items="[{ text: 'Daftar Transaksi' }]" />

            <callout />

            <div class="row pb-3">
                <div class="col-xl-12 col-12">
                    <base-filters title="Filter" v-model="filters" :fields="filterFields" />
                </div>

                <div class="col-xl-12 col-12">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div
                            class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3 me-3">
                                    <i class="fas fa-money-bill fs-5"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0 text-dark">Data Transaksi</h5>
                                    <p class="text-muted small mb-0">
                                        Kelola riwayat transaksi dan pembayaran pelanggan.
                                    </p>
                                </div>
                            </div>

                            <action-toolbar :actions="toolbarActions" />

                        </div>

                        <div class="card-body p-0 position-relative">
                            <base-table :markAll="true" :loader="isLoading" loaderText="Sedang memuat data..."
                                :headers="header" :items="transaction.data" row-key="transaction_id"
                                :row-class="customClassTable" @update:selected="(val) => selectedRow = val">

                                <template #empty>
                                    <i class="fas fa-inbox fa-3x text-muted opacity-25 mb-3"></i>
                                    <p class="text-muted fw-semibold">Belum ada data transaksi.</p>
                                </template>

                                <template #row="{ item, index }">
                                    <td class="text-center text-muted fw-bold small">
                                        {{
                                            index +
                                            1 +
                                            (transaction?.current_page - 1) * transaction?.per_page
                                        }}
                                    </td>
                                    <td class="text-center">
                                        <div class="fw-bold text-primary">
                                            <a v-if="item.status === 'cancelled'" :href="'invoice/' + item.invoice"
                                                @click.prevent="openModal('cancelled', item)">
                                                <span v-html="highlight(item.invoice, filters.keyword)"></span>
                                            </a>
                                            <a v-else-if="item.status !== 'cancelled'" :href="'invoice/' + item.invoice"
                                                @click.prevent="openModal('detail', item)">
                                                <span v-html="highlight(item.invoice, filters.keyword)"></span>
                                            </a>
                                        </div>
                                        <div class="small text-muted">
                                            {{ daysTranslate(item.transaction_date) }}
                                        </div>
                                    </td>

                                    <td class="text-start">
                                        <div class="fw-semibold"
                                            v-html="highlight(item.customer?.customer_name, filters.keyword)"></div>
                                        <div class="small text-muted" style="font-size: 0.75rem;">
                                            Oleh: {{ item.creator?.name }}
                                        </div>
                                    </td>

                                    <td class="text-start">
                                        <div v-if="item.items && item.items.length > 0">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-box text-muted me-2"></i>
                                                <span class="fw-medium text-dark">
                                                    {{ item.items[0].product?.name }}
                                                </span>
                                            </div>

                                            <div v-if="item.items_count > 1" class="ms-4 mt-1">
                                                <span class="badge bg-light text-secondary border rounded-pill"
                                                    style="font-size: 0.7rem;">
                                                    + {{ item.items_count - 1 }} produk lainnya
                                                </span>
                                            </div>
                                        </div>

                                        <div v-else class="text-danger small fst-italic">
                                            Item kosong
                                        </div>
                                    </td>

                                    <td class="text-end fw-bold">
                                        {{ formatCurrency(item.grand_total) }}
                                    </td>

                                    <td class="text-center">
                                        <span v-if="item.status === 'repayment'"
                                            class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">
                                            <i class="fas fa-check-circle me-1"></i> Lunas
                                        </span>

                                        <span v-else-if="item.status === 'payment'"
                                            class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill border border-warning">
                                            <i class="fas fa-hourglass-half me-1"></i> Belum Lunas
                                        </span>

                                        <span v-else
                                            class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill">
                                            Belum Bayar
                                        </span>
                                    </td>

                                    <td class="text-center">
                                        <dropdown-action :item="item" :actions="[
                                            {
                                                label: 'Ubah Transaksi',
                                                icon: 'bi bi-pencil-square fs-6',
                                                color_icon: 'success',
                                                action: 'edit',
                                                permission: 'transaction.edit',
                                            },
                                            {
                                                label: 'Pelunasan',
                                                icon: 'bi bi-check-circle fs-6',
                                                color: 'success',
                                                action: 'repayment',
                                                permission: 'transaction.edit',
                                                show: item.status !== 'repayment' && item.status !== 'cancelled',
                                            },
                                            {
                                                label: 'Batalkan Transaksi',
                                                icon: 'bi bi-x-circle fs-6',
                                                color_icon: 'danger',
                                                action: 'cancelled',
                                                permission: 'transaction.edit',
                                                show: item.status !== 'cancelled',
                                            },
                                            {
                                                type: 'divider'
                                            },
                                            {
                                                label: 'Hapus',
                                                icon: 'bi bi-trash fs-6',
                                                color: 'danger',
                                                action: 'delete',
                                                permission: 'transaction.delete',
                                            },
                                        ]" @edit="navigateTo('transaction.edit', item.transaction_id)"
                                            @delete="deleted('transaction.deleted', item)"
                                            @repayment="navigateTo('transaction.show', item.transaction_id)"
                                            @cancelled="navigateTo('transaction.cancelled', item.transaction_id)" />
                                    </td>
                                </template>
                            </base-table>

                        </div>

                        <div class="card-footer bg-white border-top py-3">
                            <div class="d-flex flex-wrap justify-content-between align-items-center">
                                <div class="text-muted mb-2 mb-md-0">
                                    Menampilkan <strong>{{ props.transaction?.from ?? 0 }}</strong> -
                                    <strong>{{ props.transaction?.to ?? 0 }}</strong> dari
                                    <strong>{{ props.transaction?.total ?? 0 }}</strong> data
                                </div>
                                <pagination size="pagination-sm" :links="props.transaction?.links"
                                    routeName="transaction" :additionalQuery="{
                                        order_by: filters.order_by,
                                        limit: filters.limit,
                                        keyword: filters.keyword,
                                        status: filters.status,
                                        date_filter: filters.date_filter,
                                    }" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <TransactionDetailModal :show="modals.transactionDetail" :transaction="selectedData"
                @update:show="modals.transactionDetail = $event" />

            <CancelledPreviewModal :show="modals.cancelledPreview" :transaction="selectedData"
                @update:show="modals.cancelledPreview = $event" />

            <ModalExport :transaction="transaction?.data" :show="modals.export" @update:show="modals.export = $event" />
        </template>
    </app-layout>
</template>
