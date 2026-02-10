<script setup>
import { computed, nextTick, onMounted, reactive, ref, watch } from "vue";
import { Head, Link, router, useForm, usePage } from "@inertiajs/vue3";
import { debounce } from "lodash";
import { hasRole, hasPermission } from "@/composables/useAuth";
import { highlight } from "@/helpers/highlight";
import axios from "axios";
import ModalExport from "./ModalExport.vue";

import moment from "moment";
moment.locale('id');

import { useConfirm } from "@/helpers/useConfirm.js"
const confirm = useConfirm(); // Memanggil fungsi confirm untuk alert delete

const props = defineProps({
    storyReport: Object,
    filters: Object,
    totalToday: Number,
});

const filters = reactive({
    keyword: props.filters.keyword ?? '',
    limit: props.filters.limit ?? null,
    order_by: props.filters.order_by ?? null,
    start_date: props.filters.start_date ?? null,
    end_date: props.filters.end_date ?? null,
    page: props.filters?.page ?? 1,
})

const deleted = async (nameRoute, data) => {
    const setConfirm = await confirm.ask({
        title: 'Hapus',
        message: `Kamu ingin menghapus laporan ID ${data.report_code} ?`,
        confirmText: 'Ya, Hapus',
        variant: 'danger' // Memberikan warna merah pada tombol konfirmasi
    });

    if (setConfirm) {
        loaderActive.value?.show("Sedang menghapus data...");
        router.delete(route(nameRoute, data.story_status_id), {
            onFinish: () => loaderActive.value?.hide(),
            preserveScroll: false,
            replace: true
        });
    }
}


function daysTranslate(dayValue) {
    const dayConvert = {
        "Sunday": "Minggu",
        "Monday": "Senin",
        "Tuesday": "Selasa",
        "Wednesday": "Rabu",
        "Thursday": "Kamis",
        "Friday": "Jumat",
        "Saturday": "Sabtu",
    };
    const dayName = moment(dayValue).format('dddd');
    const dateFormat = moment(dayValue).format('DD/MM/YYYY');
    return dayConvert[dayName] + ", " + dateFormat ?? dayName;
}

const isLoading = ref(false)
const searchByDate = debounce((e) => {
    isLoading.value = true
    router.get(route("story_report"), filters, {
        preserveScroll: true,
        replace: true,
        preserveState: true,
        only: ["storyReport", "filters", "totalToday"], // lebih hemat bandwidth jika pakai Inertia partial reload
        onFinish: () => {
            // Selesai apapun hasilnya → loader hilang
            isLoading.value = false
        }
    });
}, 500);

// trigger button untuk melakukan pencarian berdasarkan tanggal
const applyDateRange = () => {
    if (filters.start_date && filters.end_date) {
        isLoading.value = true
        filters.page = 1;
        searchByDate()
        return
    }
    filters.page = 1;
    searchByDate()
}

const handleReset = () => {
    Object.assign(filters, {
        keyword: "",
        limit: null,
        order_by: null,
        start_date: null,
        end_date: null
    });
    // Langsung cari data bersih
    applyDateRange()
}

// Watcher untuk tanggal saja bila tanggal direset dari field

// watch(
//     [() => filters.start_date, () => filters.end_date],
//     () => {
//         if (!filters.start_date && !filters.end_date) {
//             filters.page = 1;
//             isLoading.value = true
//             searchByDate();
//         }

//     }, {
//     deep: true
// }
// );

// Watcher untuk trigger pencarian
watch([
    () => filters.keyword,
], () => {
    filters.page = 1;
    searchByDate()
}, {
    deep: true
})

//hapus semua
const selected = ref([]);
const isVisibleButton = ref(false)
const deletedAll = async () => {
    // 1. Kondisi Tidak Ada Data (Berfungsi sebagai Alert)
    if (!selected.value.length) {
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
        message: `Apakah Anda yakin ingin menghapus ${selected.value.length} data terpilih?`,
        confirmText: 'Ya, Hapus',
        cancelText: 'Batal',
        variant: 'danger'
    });

    // 3. Eksekusi
    if (setConfirm) {
        loaderActive.value?.show("Sedang menghapus data...");
        router.post(route('story_report.destroy_all'), {
            ids: selected.value
        }, {
            onFinish: () => {
                loaderActive.value?.hide();
                selected.value = []; // Bersihkan pilihan setelah sukses
            },
            preserveScroll: true,
            preserveState: false,
        });
    }
}
watch(selected, (val) => {
    if (val.length > 0) {
        isVisibleButton.value = true;
    } else {
        isVisibleButton.value = false
    }
})

const loaderActive = ref(null)
const navigateTo = (routeName, params = {}, message = "Sedang membuka...") => {
    if (message) loaderActive.value?.show(message);
    router.get(route(routeName, params), {}, {
        onFinish: () => message && loaderActive.value?.hide(),
        preserveScroll: false,
        replace: true,
    });

}


// =========Tampilkan Modal========== //
const modals = reactive({
    export: false,
})
const openModal = (type, data) => {
    if (type === 'export') modals.export = true;
}

// =========Batas Fungsi untuk Tampilkan Modal========== //


const hasActiveFilter = computed(() => {
    return (
        filters.keyword !== '' ||
        filters.start_date !== null ||
        filters.end_date !== null ||
        filters.limit !== null ||
        filters.order_by !== null
    )
})
const header = [
    {
        label: "No",
        key: "__index",
        attrs: {
            class: "text-center",
            style: "width:50px",
        },
    },
    {
        label: "ID Report",
        key: "status_report_id",
        attrs: {
            class: "text-center",
        },
    },
    {
        label: "Tanggal & Info",
        key: "report_date",
        attrs: {
            class: "text-center",
        },
    },
    {
        label: "Jam",
        key: "report_time",
        attrs: {
            class: "text-center",
        },
    },
    {
        label: "Jumlah Status",
        key: "count_status",
        attrs: {
            class: "text-center",
        },
    },
    {
        label: "Dibuat",
        key: "created_at",
        attrs: {
            class: "text-center",
        },
    },
    {
        label: "Diperbarui",
        key: "updated_at",
        attrs: {
            class: "text-center",
        },
    },
    {
        label: "Aksi",
        key: "action",
        attrs: {
            class: "text-center",
        },
    },
];
const filterFields = computed(() => [
    {
        key: 'keyword',
        label: 'Pencarian',
        col: 'col-xl-8 col-12',
        type: 'search',
        icon: 'fas fa-search',
        autofocus: true,
        props: {
            placeholder: 'Masukan ID Report',
            inputClass: 'border-start-0 ps-2 shadow-none',
            isValid: false,
        }
    },
    {
        key: 'limit',
        label: 'Batas',
        type: 'select',
        col: 'col-xl-2 col-md-6 col-6',
        icon: 'fas fa-list-ul',
        props: {
            selectClass: 'border-start-0 ps-2 shadow-none',
            isValid: false,
        },
        options: [
            { value: null, label: 'Pilih Batas Data' },
            { value: 10, label: '10 Baris' },
            { value: 20, label: '20 Baris' },
            { value: 30, label: '30 Baris' },
            { value: 50, label: '50 Baris' },
            { value: 100, label: '100 Baris' },
        ]
    },
    {
        key: 'order_by',
        label: 'Urutan',
        type: 'select',
        col: 'col-xl-2 col-md-6 col-6',
        icon: 'fas fa-sort',
        props: {
            selectClass: 'border-start-0 ps-2 shadow-none',
            isValid: false,
        },
        options: [
            { value: null, label: 'Pilih Urutan' },
            { value: 'desc', label: 'Terbaru' },
            { value: 'asc', label: 'Terlama' },
        ]
    },
    {
        key: 'start_date',
        label: 'Tanggal Awal',
        type: 'date',
        col: 'col-xl-6 col-md-6 col-6',
        icon: 'fas fa-calendar-alt',
        props: {
            inputClass: 'border-start-0 ps-2 shadow-none',
            isValid: false,
        }
    },
    {
        key: 'end_date',
        label: 'Tanggal Akhir',
        type: 'date',
        col: 'col-xl-6 col-md-6 col-6',
        icon: 'fas fa-calendar-alt',
        props: {
            inputClass: 'border-start-0 ps-2 shadow-none',
            isValid: false,
        }
    },

    //  button trigger
    {
        key: 'reset',
        label: 'Bersihkan',
        type: 'button',
        name: 'reset',
        icon: 'fas fa-undo',
        class: !hasActiveFilter.value ? 'btn-secondary' : 'btn-outline-danger',
        disabled: !hasActiveFilter.value,
        handler: () => handleReset()

    },
    {
        key: 'apply',
        label: 'Terapkan',
        type: 'button',
        name: 'apply',
        icon: 'fas fa-filter',
        class: !hasActiveFilter.value ? 'btn-secondary' : 'btn-primary',
        disabled: !hasActiveFilter.value,
        handler: () => applyDateRange()
    },

]);

const reset = () => {
    isLoading.value = true
    navigateTo('story_report.reset', {}, false);
}


const toolbarActions = computed(() => [

    {
        label: `Hapus (${selected.value.length})`,
        icon: 'fas fa-trash-alt',
        iconColor: 'text-danger',
        labelColor: 'text-danger',
        disabled: !isVisibleButton.value,
        show: hasPermission('status.report.delete'),
        click: deletedAll
    },
    {
        label: 'Unduh',
        icon: 'fas fa-download',
        iconColor: 'text-success',
        show: hasPermission('status.report.export'),
        click: () => openModal('export')
    },
    {
        label: 'Buat Laporan',
        icon: 'fas fa-plus-circle',
        isPrimary: true, // Prioritas Utama
        show: hasPermission('status.report.create'),
        click: () => navigateTo('story_report.create')
    },
    {
        label: 'Segarkan',
        icon: 'fas fa-redo-alt',
        iconColor: 'text-primary',
        loading: isLoading.value,
        click: () => reset()
    }
]);
</script>
<template>

    <Head title="Halaman Laporan Update Status" />
    <app-layout>
        <template #content>
            <loader-page ref="loaderActive" />
            <bread-crumbs :home="false" icon="fas fa-sticky-note" title="Laporan Update Status"
                :items="[{ text: 'Laporan Update Status' }]" />
            <callout />

            <div class="row pb-3">
                <div class="col-xl-12 col-12 mb-3">
                    <base-filters title="Filter" v-model="filters" :fields="filterFields" />
                </div>
                <div class="col-12 col-xl-12">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">

                        <div
                            class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3 me-3">
                                    <i class="fas fa-sticky-note fs-5"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0 text-dark">Data Laporan Status</h5>
                                    <p class="text-muted small mb-0">
                                        Rekapitulasi update status harian.
                                    </p>
                                </div>
                            </div>

                            <action-toolbar :actions="toolbarActions" />

                        </div>
                        <div class="card-body p-0 position-relative">
                            <base-table :markAll="hasPermission('status.report.delete')" :loader="isLoading"
                                loaderText="Sedang memuat data..." :headers="header" :items="storyReport?.data"
                                row-key="story_status_id" @update:selected="(val) => selected = val">

                                <template #empty>
                                    <div class="py-4">
                                        <i class="fas fa-folder-open fa-3x text-muted opacity-25 mb-3"></i>
                                        <p class="text-muted">Tidak ada data laporan ditemukan.
                                        </p>
                                    </div>
                                </template>

                                <template #row="{ item, index }">

                                    <td class="text-center text-muted fw-bold">
                                        {{ index + 1 + (storyReport?.current_page - 1) *
                                            storyReport?.per_page }}
                                    </td>

                                    <td class="text-center">
                                        <span class="font-monospace bg-light border px-2 py-1 rounded text-dark"
                                            v-html="highlight(item.report_code, filters.keyword)">
                                        </span>
                                    </td>

                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-semibold text-dark">
                                                <i class="far fa-calendar-alt me-1 text-primary small"></i>
                                                {{ daysTranslate(item.report_date) }}
                                            </span>
                                            <small class="text-muted fst-italic mt-1" style="font-size: 0.75rem;">
                                                {{ item.informasi }}
                                            </small>
                                        </div>
                                    </td>

                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border fw-normal"
                                            style="font-size: 0.9rem;">
                                            <i class="far fa-clock me-1"></i> {{ item.report_time.slice(0, 5) }}
                                        </span>
                                    </td>

                                    <td class="text-center">
                                        <span class="fw-bold fs-6 text-primary">
                                            {{ item.count_status }}
                                        </span>
                                    </td>

                                    <td class="text-center text-muted">
                                        {{ moment(item.created_at).format('H:mm A') }}
                                    </td>
                                    <td class="text-center text-muted">
                                        {{ item.updated_at === item.created_at ? '-' :
                                            moment(item.updated_at).format('H:mm A') }}
                                    </td>

                                    <td class="text-center">
                                        <dropdown-action :item="item" :actions="[
                                            {
                                                label: 'Ubah',
                                                icon: 'bi bi-pencil-square fs-6',
                                                color_icon: 'success',
                                                action: 'edit',
                                                permission: 'status.report.edit'
                                            },
                                            {
                                                label: 'Bagikan',
                                                icon: 'bi bi-share-fill fs-6',
                                                color_icon: 'info',
                                                action: 'share',
                                                permission: 'status.report.share'
                                            },
                                            {
                                                type: 'divider'
                                            },
                                            {
                                                label: 'Hapus',
                                                icon: 'bi bi-trash-fill fs-6',
                                                color: 'danger',
                                                action: 'delete',
                                                permission: 'status.report.delete'
                                            }
                                        ]" @edit="navigateTo('story_report.edit', item.story_status_id)"
                                            @delete="deleted('story_report.deleted', item)" />
                                    </td>
                                </template>
                                <template #footer="{ headers }">
                                    <tr>
                                        <td :colspan="headers - 4"
                                            class="text-end fw-bold text-uppercase text-secondary py-3 pe-3">
                                            Total Keseluruhan :
                                        </td>

                                        <td
                                            class="text-center fw-bolder text-dark py-3 fs-6 bg-secondary bg-opacity-10 border-start border-end">
                                            {{ props.totalToday ?? props.totalWithFilter ?? '0' }}
                                        </td>
                                        <td :colspan="headers - 2"></td>
                                    </tr>
                                </template>
                            </base-table>
                        </div>

                        <div class="card-footer bg-white border-top py-3">
                            <div class="d-flex flex-wrap justify-content-between align-items-center">
                                <div class="text-muted mb-2 mb-md-0 small">
                                    Menampilkan <strong>{{ props.storyReport?.from ?? 0 }}</strong> -
                                    <strong>{{ props.storyReport?.to ?? 0 }}</strong> dari
                                    <strong>{{ props.storyReport?.total ?? 0 }}</strong> data
                                </div>
                                <pagination size="pagination-sm" :links="props.storyReport?.links"
                                    routeName="story_report" :additionalQuery="{
                                        order_by: filters.order_by,
                                        limit: filters.limit,
                                        start_date: filters.start_date,
                                        end_date: filters.end_date,
                                    }" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <ModalExport :show="modals.export" @update:show="modals.export = $event" />
        </template>
    </app-layout>
</template>
<style scoped>
.blur-area {
    transition: all 0.3s ease;
}

.blur-area.is-blurred {
    filter: blur(3px);
    pointer-events: none;
    user-select: none;
    opacity: 0.6;
}

.blink-green {
    animation: blinkGreen 1s ease-in-out 5;
}

.blink-blue {
    animation: blinkBlue 1s ease-in-out 5;
}

@keyframes blinkGreen {

    0% {
        background-color: #a4f5a4;
    }

    50% {
        background-color: #ffffff;
    }

    100% {
        background-color: #a4f5a4;
    }
}

@keyframes blinkBlue {

    0% {
        background-color: #a7c8ff;
    }

    50% {
        background-color: #ffffff;
    }

    100% {
        background-color: #a7c8ff;
    }
}

.description {
    width: 100%;
    /* Sesuaikan nilai ini sesuai kebutuhan Anda */
    text-align: left;
    /* 2. Pastikan konten yang panjang dipaksa untuk melipat */
    white-space: normal;
    /* 3. Gunakan properti untuk memecah kata panjang */
    overflow-wrap: break-word;
    word-break: break-word;
    /* Tambahan: Opsional jika ingin menghilangkan scrollbar horizontal */
    overflow-x: hidden;
    vertical-align: top;
    max-height: 150px;
    align-content: center;
    justify-content: center;
    display: flex;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s ease, transform 0.5s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(-5px);
}

.fade-enter-to,
.fade-leave-from {
    opacity: 1;
    transform: translateY(0);
}

.table.table-striped tbody tr:nth-of-type(odd) {
    background-color: #c2dff731;
}

.table.table-striped tbody tr:nth-of-type(even) {
    background-color: #ffffff;
}

/* Hover hanya untuk teks table, tapi TIDAK untuk dropdown */
.table.table-hover tbody tr:hover {
    background-color: rgba(0, 183, 255, 0.171);
    text-shadow: 0 0 0 rgba(0, 0, 0, 0.918);
    transition: all 0.15s ease-in-out;
}

.table-hover tbody tr:hover td .dropdown,
.table-hover tbody tr:hover td .dropdown * {
    text-shadow: none !important;
}


.card-stat {
    border-radius: 12px;
    transition: all .2s ease-in-out;
}

.card-stat:hover {
    transform: translateY(-4px);
}

.icon-box {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 22px;
}

.label {
    font-size: 14px;
    font-weight: 600;
    color: #666;
}

.value {
    font-size: 26px;
    font-weight: 700;
    margin-top: -4px;
}


/* Toolbar Container */
.action-toolbar {
    width: fit-content;
}

/* Base Button Style */
.btn-action-soft,
.btn-action-primary {
    border: none;
    padding: 6px 10px;
    font-size: 0.875rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    transition: all 0.2s ease;
}

/* Soft Button (Info, Unduh, Segarkan) */
.btn-action-soft {
    background-color: transparent;
    color: #475569;
}

.btn-action-soft:hover {
    background-color: #f1f5f9;
    color: #1e293b;
}

.btn-action-soft i {
    font-size: 1rem;
}

.btn-action-danger {
    background-color: #f87171;
    color: white;
    border-radius: 10px !important;
    /* Membuatnya sedikit menonjol */
    box-shadow: 0 4px 6px -1px rgba(13, 110, 253, 0.2);
}

/* Primary Button (Buat Laporan) */
.btn-action-primary {
    background-color: #0d6efd;
    color: white;
    border-radius: 10px !important;
    /* Membuatnya sedikit menonjol */
    box-shadow: 0 4px 6px -1px rgba(13, 110, 253, 0.2);
}

.btn-action-primary:hover {
    background-color: #0b5ed7;
    transform: translateY(-1px);
    box-shadow: 0 6px 10px -1px rgba(13, 110, 253, 0.3);
}

/* Styling Khusus untuk btn-group agar tidak kaku */
.btn-group .btn:not(:last-child) {
    border-right: 1px solid #f1f5f9;
}

.btn-group {
    background: #f8fafc;
    border-radius: 10px;
    padding: 2px;
}

/* Animasi Spinner */
.fa-spin {
    animation: fa-spin 1s infinite linear;
}
</style>
