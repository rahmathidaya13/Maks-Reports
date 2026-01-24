<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { debounce } from "lodash";
import { highlight } from "@/helpers/highlight";
import { formatText } from "@/helpers/formatText";
import CancelledPreviewModal from "./Modal/CancelledPreviewModal.vue";
import TransactionDetailModal from "./Modal/TransactionDetailModal.vue";
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
    filters.keyword = "";
    filters.status = null;
    filters.date_filter = null;
    filters.limit = null;
    filters.order_by = null;

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
        label: "ID Transaksi",
        key: "transaction_id",
        attrs: {
            class: "text-center",

        }
    },
    {
        label: "Tgl.Transaksi",
        key: "transaction_date",
        attrs: {
            class: "text-center"
        }
    },
    {
        label: "Invoice",
        key: "invoice",
        attrs: {
            class: "text-center"
        }
    },
    {
        label: "Tgl.Pembayaran",
        key: "payment_date",
        attrs: {
            class: "text-center"
        }
    },
    {
        label: "Nama Pelanggan",
        key: "customer_id",
        attrs: {
            class: "text-center"
        }
    },
    {
        label: "Produk",
        key: "product_id",
        attrs: {
            class: "text-center"
        }
    },
    {
        label: "Harga Asli",
        key: "price_original",
        attrs: {
            class: "text-center"
        }
    },
    {
        label: "Diskon",
        key: "price_discount",
        attrs: {
            class: "text-center"
        }
    },
    {
        label: "Total Harga",
        key: "price_final",
        attrs: {
            class: "text-center"
        }
    },
    {
        label: "Sudah Bayar",
        key: "total_paid",
        attrs: {
            class: "text-center"
        }
    },
    {
        label: "Sisa Pembayaran",
        key: "-",
        attrs: {
            class: "text-center"
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
        label: "Aksi",
        key: "-",
        attrs: {
            class: "text-center",
            style: "width:100px"
        }
    }
];

// CRUD OPERATION
const loaderActive = ref(null);
const create = () => {
    loaderActive.value?.show("Memproses...");
    router.get(
        route("transaction.create"),
        {},
        {
            onFinish: () => {
                loaderActive.value?.hide();
            },
        }
    );
};

const edit = (id) => {
    loaderActive.value?.show("Sedang memuat data...");
    router.get(
        route("transaction.edit", id),
        {},
        {
            onFinish: () => loaderActive.value?.hide(),
        }
    );
};

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

const repayment = (id) => {
    loaderActive.value?.show("Sedang memuat data...");
    router.get(
        route("transaction.show", id),
        {},
        {
            onFinish: () => loaderActive.value?.hide(),
        }
    );
};
const cancelled = (id) => {
    loaderActive.value?.show("Sedang memuat data...");
    router.get(
        route("transaction.cancelled", id),
        {},
        {
            onFinish: () => loaderActive.value?.hide(),
        }
    );
};
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
            placeholder: "Masukan Id,nama pelanggan dan produk...",
            inputClass: "border-start-0 ps-2 shadow-none",
            isValid: false,
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
            isValid: false,
        },
        options: [
            { value: null, label: "Pilih Status Pembayaran" },
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
            isValid: false,
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
            isValid: false,
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
            isValid: false,
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
const showModalCancelInfo = ref(false);
const showModaDetailInfo = ref(false);
const openModalCancelled = (data) => {
    selectedData.value = data;
    showModalCancelInfo.value = true;
    // console.log(data);
};
const openModalDetail = (data) => {
    selectedData.value = data;
    showModaDetailInfo.value = true;
}
// function for sub string
function subString(strValue, length) {
    const clean = strValue.replace(/-/g, "")
    return clean.length > length
        ? clean.substring(0, length)
        : clean
}
const reset = () => {
    isLoading.value = true
    router.get(route("transaction.reset"), {}, {
        preserveScroll: false,
        replace: true,
        onFinish: () => isLoading.value = false
    });
}
const customClassTable = (item) => {
    return {
        'bg-danger bg-opacity-10': item.status === 'cancelled', // Dibatalkan
        'bg-warning bg-opacity-10': item.status === 'payment', // Dp
    }
}

const toolbarActions = computed(() => [
    {
        label: `Hapus(${selectedRow.value.length})`,
        icon: 'fas fa-trash-alt',
        iconColor: 'text-danger',
        show: hasPermission('transaction.delete') && selectedRow.value.length > 0,
        click: deleteSelected
    },
    {
        label: 'Transaksi Baru',
        icon: 'fas fa-plus-circle',
        isPrimary: true, // Prioritas Utama
        show: hasPermission('transaction.create'),
        click: create
    },
    {
        label: 'Segarkan',
        icon: 'fas fa-redo-alt',
        iconColor: 'text-primary',
        loading: isLoading.value,
        click: reset
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
                <div class="col-xl-12 col-12 mb-3">
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
                            <base-table :loader="isLoading" loaderText="Sedang memuat data..." :headers="header"
                                :items="transaction.data" row-key="transaction_id" :row-class="customClassTable">

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
                                        <button v-if="item.status === 'cancelled'"
                                            class="btn btn-link text-decoration-none text-capitalize"
                                            @click="openModalCancelled(item)">
                                            <span
                                                v-html="highlight(subString(item.transaction_id, 8), filters.keyword)"></span>
                                        </button>
                                        <button v-else-if="item.status !== 'cancelled'"
                                            class="btn btn-link text-decoration-none text-capitalize"
                                            @click="openModalDetail(item)">
                                            <span
                                                v-html="highlight(subString(item.transaction_id, 8), filters.keyword)"></span>
                                        </button>
                                    </td>

                                    <td class="text-center">
                                        <div class="fw-semibold text-dark">
                                            {{ daysTranslate(item.transaction_date).split(",")[0] }}
                                        </div>
                                        <div class="small text-muted" style="font-size: 0.9rem">
                                            {{ daysTranslate(item.transaction_date).split(",")[1] }}
                                        </div>
                                    </td>
                                    <td class="text-start">
                                        <span
                                            class="badge text-bg-info bg-opacity-10 border border-secondary border-opacity-10 fw-normal text-capitalize"
                                            style="font-size: 0.9rem">
                                            <span v-html="highlight(item.invoice, filters.keyword)"></span>
                                        </span>
                                    </td>

                                    <td class="text-start" style="min-width: 200px">
                                        <div class="d-flex flex-column gap-2">
                                            <div
                                                class="d-flex justify-content-between align-items-center border rounded-3 p-1 px-2 bg-light bg-opacity-50">
                                                <span class="badge bg-warning text-dark bg-opacity-25 rounded-pill"
                                                    style="font-size: 0.65rem">
                                                    DP 50%
                                                </span>
                                                <span class="small fw-bold text-dark">{{
                                                    getPaymentDate(item.payments, "payment") }}
                                                </span>
                                            </div>
                                            <div
                                                class="d-flex justify-content-between align-items-center border rounded-3 p-1 px-2 bg-success bg-opacity-10">
                                                <span class="badge bg-success text-success bg-opacity-25 rounded-pill"
                                                    style="font-size: 0.65rem">Lunas</span>
                                                <span class="small fw-bold text-success">{{
                                                    getPaymentDate(item.payments, "repayment")
                                                    }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="text-center">
                                        <div class="d-flex align-items-center justify-content-center gap-2">
                                            <div class="avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center"
                                                style="width: 30px; height: 30px">
                                                <i class="fas fa-user-circle"></i>
                                            </div>
                                            <span class="fw-semibold text-capitalize text-dark" v-html="highlight(item.customer?.customer_name, filters.keyword) ??
                                                '-'
                                                "></span>
                                        </div>
                                    </td>

                                    <td class="text-start">
                                        <span
                                            class="badge bg-secondary bg-opacity-25 text-dark border border-secondary border-opacity-10 fw-normal text-capitalize"
                                            style="font-size: 0.9rem">
                                            <span v-html="highlight(item.product?.name, filters.keyword)"></span>
                                        </span>
                                    </td>

                                    <td class="text-end pe-4">
                                        <div class="text-dark fw-bold">
                                            {{ formatCurrency(item.price_original) }}
                                        </div>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="text-danger text-decoration-line-through">
                                            {{ formatCurrency(item.price_discount) }}
                                        </div>
                                    </td>

                                    <td class="text-end fw-bold text-primary pe-4" style="font-size: 1rem">
                                        {{ formatCurrency(item.price_final) }}
                                    </td>

                                    <td class="text-end fw-bold text-success pe-4">
                                        {{ formatCurrency(item.total_paid) }}
                                    </td>

                                    <td class="text-end pe-4">
                                        <span class="fw-bold" :class="item.price_final - item.total_paid > 0
                                            ? 'text-danger'
                                            : 'text-muted'
                                            ">
                                            {{ formatCurrency(item.price_final - item.total_paid) }}
                                        </span>
                                    </td>

                                    <td class="text-center">
                                        <span class="badge rounded-pill px-3 py-2 fw-bold"
                                            :class="status(item.status).badge">
                                            <i :class="['me-1', status(item.status).icon]"></i>
                                            {{ status(item.status).label }}
                                        </span>
                                    </td>

                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <button v-if="item.status !== 'cancelled'"
                                                @click.prevent="cancelled(item.transaction_id)"
                                                class="btn btn-sm d-flex align-items-center px-2 rounded-pill" :class="[
                                                    item.status === 'cancelled'
                                                        ? 'btn-secondary'
                                                        : 'btn-danger',
                                                ]" title="Pelunasan">
                                                <i :class="[
                                                    'me-0 me-xl-2',
                                                    item.status === 'cancelled'
                                                        ? 'fas fa-check'
                                                        : 'fas fa-times',
                                                ]"></i>
                                                <span class="d-none d-xl-inline">{{
                                                    item.status === "cancelled" ? "Dibatalkan" : "Batalkan"
                                                    }}</span>
                                            </button>


                                            <button :disabled="item.status !== 'payment'"
                                                @click.prevent="repayment(item.transaction_id)"
                                                class="btn btn-sm d-flex align-items-center px-3 rounded-pill" :class="[
                                                    item.status === 'payment'
                                                        ? 'btn-success text-white'
                                                        : 'btn-light text-muted border',
                                                ]" title="Pelunasan">
                                                <i class="fas fa-cash-register me-0 me-xl-2"></i>
                                                <span class="d-none d-xl-inline">Bayar</span>
                                            </button>
                                            <dropdown-action :item="item" :actions="[
                                                {
                                                    label: 'Ubah Transaksi',
                                                    icon: 'bi bi-pencil-square fs-6',
                                                    color_icon: 'success',
                                                    action: 'edit',
                                                    permission: 'transaction.edit',
                                                },
                                                {
                                                    label: 'Hapus',
                                                    icon: 'bi bi-trash fs-6',
                                                    color: 'danger',
                                                    action: 'delete',
                                                    permission: 'transaction.delete',
                                                },
                                            ]" @edit="edit(item.transaction_id)"
                                                @delete="deleted('transaction.deleted', item)" />
                                        </div>
                                    </td>
                                </template>
                            </base-table>

                        </div>

                        <div class="card-footer bg-white border-top py-3" v-if="transaction?.data.length">
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
            <TransactionDetailModal :show="showModaDetailInfo" :transaction="selectedData"
                @update:show="showModaDetailInfo = $event" />

            <CancelledPreviewModal :show="showModalCancelInfo" :transaction="selectedData"
                @update:show="showModalCancelInfo = $event" />
        </template>
    </app-layout>
</template>
