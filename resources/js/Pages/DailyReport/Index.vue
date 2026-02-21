<script setup>
import { computed, nextTick, onMounted, reactive, ref, watch } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { debounce } from "lodash";
import moment from "moment";
import { reportDailyLeads } from "@/helpers/reportDailyLeads";
import { formatDate } from "@/helpers/formatDate";
import { formatText } from "@/helpers/formatText";
import { hasRole, hasPermission } from "@/composables/useAuth";
import axios from "axios";
import ModalExport from "./ModalExport.vue";
import ShareModal from "./ShareModal.vue";
moment.locale('id');

import { useConfirm } from "@/helpers/useConfirm.js"
const confirm = useConfirm(); // Memanggil fungsi confirm untuk alert delete

const props = defineProps({
    dailyReport: Object,
    filters: Object,
});

const filters = reactive({
    limit: props.filters.limit ?? 1,
    order_by: props.filters.order_by ?? null,
    page: props.filters?.page ?? 1,
    start_date: props.filters.start_date ?? '',
    end_date: props.filters.end_date ?? '',
})


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
        router.post(route('daily_report.destroy_all'), {
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
        isVisible.value = true
    } else {
        isVisible.value = false
    }
})
// atur warna badge sesuai jenis permission

const deleted = async (nameRoute, data) => {
    const setConfirm = await confirm.ask({
        title: 'Hapus',
        message: `Kamu ingin menghapus laporan leads tanggal ${moment(data.daily_report_date).format("DD-MM-YYYY")} ?`,
        confirmText: 'Ya, Hapus',
        variant: 'danger' // Memberikan warna merah pada tombol konfirmasi
    });

    if (setConfirm) {
        loaderActive.value?.show("Sedang menghapus data...");
        router.delete(route(nameRoute, data.daily_report_id), {
            onFinish: () => loaderActive.value?.hide(),
            preserveScroll: false,
            replace: true
        });
    }
}

function daysOnlyConvert(dayValue) {
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
    const dateFormat = moment(dayValue).format('DD-MM-YYYY');
    return dayConvert[dayName] + ", " + dateFormat ?? dayName;
}

// trigger button bila tanggal diisi
const isDisableBtnDatePicker = computed(() => {
    return !(filters.start_date && filters.end_date);
})
const isLoading = ref(false)
const searchByDate = debounce(() => {
    isLoading.value = true
    router.get(route("daily_report"), filters, {
        preserveScroll: true,
        replace: true,
        preserveState: true,
        only: ["dailyReport", "filters"], // optional: lebih hemat bandwidth jika kamu pakai Inertia partial reload
        onFinish: () => {
            // Selesai apapun hasilnya → loader hilang
            isLoading.value = false
        }
    });
}, 500);

// trigger button untuk melakukan pencarian berdasarkan tanggal
const applyDateRange = () => {
    isLoading.value = true
    if (filters.start_date && filters.end_date) {
        filters.page = 1;
        searchByDate()
        return
    }
    filters.page = 1;
    searchByDate()
}

// Watcher untuk tanggal saja bila tanggal direset dari field
watch(
    [() => filters.start_date, () => filters.end_date],
    () => {
        if (!filters.start_date && !filters.end_date) {
            filters.page = 1;
            isLoading.value = true
            searchByDate();
        }

    }, {
    deep: true
}
);
// Watcher untuk limit dan order_by saja
watch([() => filters.limit, () => filters.order_by], () => {
    filters.page = 1;
    searchByDate()
}, {
    deep: true
})

const loaderActive = ref(null)

const goToEdit = (id) => {
    loaderActive.value?.show("Sedang memuat data...");
    router.get(route("daily_report.edit", id), {}, {
        onFinish: () => loaderActive.value?.hide()
    });
}
const goToCreate = () => {
    loaderActive.value?.show("Memproses...");
    router.get(route("daily_report.create"), {}, {
        onFinish: () => {
            loaderActive.value?.hide()
        }
    });
}

// =========Tampilkan Modal========== //
const showModal = ref(false);
const start_dateRef = ref(null)
function openModal() {
    showModal.value = true
    nextTick(() => {
        start_dateRef.value?.$el?.focus?.(); // untuk custom component
        start_dateRef.value?.focus?.();      // fallback jika native input
    });
}

// form untuk kirim berdasarkan tanggal
const form = reactive({
    start_date_dw: '',
    end_date_dw: '',
})

const information = ref(null);
watch(() => [form.start_date_dw, form.end_date_dw],
    async ([start_date, end_date]) => {
        if (!start_date || !end_date) {
            information.value = null;
            return;
        }
        const { data } = await axios.get(route('daily_report.information'), {
            params: {
                start_date: start_date,
                end_date: end_date
            }
        })
        if (data.status) {
            information.value = data;
        }
    })
// =========Batas Fungsi untuk Tampilkan Modal========== //


const handleReset = () => {
    filters.limit = 1
    filters.order_by = null
    filters.start_date = ''
    filters.end_date = ''

    // Langsung cari data bersih
    searchByDate()
}

const filterFields = computed(() => [
    {
        key: 'start_date',
        label: 'Tanggal Awal',
        type: 'date',
        col: 'col-xl-4 col-md-6',
        autofocus: true,
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
        col: 'col-xl-4 col-md-6',
        autofocus: true,
        icon: 'fas fa-calendar-alt',
        props: {
            inputClass: 'border-start-0 ps-2 shadow-none',
            isValid: false,
        }
    },
    {
        key: 'limit',
        label: 'Batas',
        type: 'select',
        col: 'col-xl-2 col-md-6',
        icon: 'fas fa-list-ul',
        props: {
            selectClass: 'border-start-0 ps-2 shadow-none',
            isValid: false,
        },
        options: [
            { value: 1, label: 'Default' },
            { value: 5, label: '5 Baris' },
            { value: 10, label: '10 Baris' },
            { value: 20, label: '20 Baris' },
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
        key: 'reset',
        label: 'Bersihkan',
        type: 'button',
        name: 'reset',
        class: isDisableBtnDatePicker.value ? 'btn-secondary' : 'btn-outline-danger',
        icon: 'fas fa-sync-alt',
        disabled: isDisableBtnDatePicker.value,
        handler: () => handleReset()
    },
    {
        key: 'apply',
        label: 'Terapkan',
        type: 'button',
        name: 'apply',
        class: isDisableBtnDatePicker.value ? 'btn-secondary' : 'btn-primary',
        icon: 'fas fa-check',
        disabled: isDisableBtnDatePicker.value,
        handler: () => applyDateRange()
    },

]);

// collapse action
const showInfo = ref(false);
const previewText = ref('');
const showShareModal = ref(false);
const selectedReport = ref(null);
const shareTo = (report) => {
    selectedReport.value = {
        name: report.creator.name,
        branch: formatText(usePage().props.auth.user.profile.branch.name),
        date: formatDate(report.date),
        leads: report.leads,
        closing: report.closing,
        fu_yesterday: report.fu_yesterday,
        fu_yesterday_closing: report.fu_yesterday_closing,
        fu_before_yesterday: report.fu_before_yesterday,
        fu_before_yesterday_closing: report.fu_before_yesterday_closing,
        fu_last_week: report.fu_last_week,
        fu_last_week_closing: report.fu_last_week_closing,
        engage_old_customer: report.engage_old_customer,
        engage_closing: report.engage_closing,
    }
    showShareModal.value = true;
    previewText.value = reportDailyLeads(selectedReport.value)

}

const reset = () => {
    isLoading.value = true
    router.get(route("daily_report.reset"), {}, {
        preserveScroll: true,
        replace: true,
        onFinish: () => isLoading.value = false
    });
}

const toolbarActions = computed((e) => [
    {
        label: 'Info',
        icon: 'fas fa-info-circle',
        iconColor: 'text-info',
        click: () => showInfo.value = !showInfo.value
    },
    {
        label: 'Unduh',
        icon: 'fas fa-download',
        iconColor: 'text-success',
        show: hasPermission('daily.report.leads.export'),
        click: openModal
    },
    {
        label: 'Buat Laporan',
        icon: 'fas fa-plus-circle',
        isPrimary: true, // Prioritas Utama
        show: hasPermission('daily.report.leads.create'),
        click: goToCreate
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

    <Head title="Halaman Laporan Harian" />
    <app-layout>
        <template #content>
            <loader-page ref="loaderActive" />
            <bread-crumbs :home="false" icon="fas fa-calendar-check" title="Rekap Laporan Leads Harian"
                :items="[{ text: 'Laporan Harian' }]" />
            <callout />
            <div class="row pb-3">

                <div class="col-xl-12 col-12">
                    <base-filters title="Filter" v-model="filters" :fields="filterFields" />
                </div>


                <div class="col-12">

                    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                        <div
                            class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">

                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3 me-3">
                                    <i class="fas fa-calendar-check fs-5"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0 text-dark">Laporan Leads Harian</h5>
                                    <p class="text-muted small mb-0">Monitor aktivitas leads dan closing harian.</p>
                                </div>
                            </div>

                            <action-toolbar :actions="toolbarActions" />
                        </div>

                        <div class="collapse" id="infoCollapse" :class="{ show: showInfo }">
                            <div class=" card-body bg-info bg-opacity-10 border-top border-info border-opacity-25">
                                <div class="row">
                                    <div class="col-12">
                                        <h6 class="fw-bold text-info mb-2"><i class="fas fa-book me-2"></i>Kamus Laporan
                                        </h6>
                                        <ul class="small text-muted mb-0 ps-3 columns-md-2">
                                            <li>
                                                <strong>Leads</strong>
                                                adalah calon konsumen baru yang pertama kali dihubungi pada hari ini.
                                            </li>
                                            <li><strong>FU (Follow Up)</strong> adalah tindak lanjut yang dilakukan
                                                kepada konsumen yang sebelumnya sudah pernah dihubungi.
                                            </li>
                                            <li><strong>FU Kemarin (H-1)</strong> adalah follow up untuk konsumen yang
                                                dihubungi sehari sebelumnya.
                                            </li>
                                            <li><strong>FU Kemarennya (H-2)</strong> adalah follow up untuk konsumen
                                                yang dihubungi dua hari sebelumnya.
                                            </li>
                                            <li><strong>FU Minggu Kemarennya</strong> adalah follow up untuk konsumen
                                                yang dihubungi pada minggu lalu.
                                            </li>
                                            <li><strong>Engage Pelanggan Lama</strong> adalah interaksi dengan pelanggan
                                                lama untuk menjaga hubungan dan menawarkan kebutuhan baru.
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div v-if="!dailyReport.data.length"
                        class="card border-0 shadow-sm rounded-4 py-5 text-center mb-4 overflow-hidden">
                        <loading-overlay :show="isLoading" />
                        <div class="card-body">
                            <div class="opacity-25 mb-3">
                                <i class="fas fa-clipboard-list fa-4x text-muted"></i>
                            </div>
                            <h6 class="text-muted fw-bold">Belum ada laporan leads.</h6>
                            <p class="small text-muted">Silakan buat laporan baru untuk hari ini.</p>
                        </div>
                    </div>

                    <div class="row g-2 gap-3">

                        <div class="col-12" :id="row.daily_report_id" v-for="(row, rowIndex) in dailyReport.data"
                            :key="rowIndex">

                            <div class="card border-0 shadow-sm rounded-4 overflow-hidden transition-hover h-100">
                                <div
                                    class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-2">
                                        <span
                                            class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10 rounded-pill px-3 py-2 fs-6">
                                            <i class="far fa-calendar-alt me-2"></i>
                                            {{ daysOnlyConvert(row.date) }}
                                        </span>
                                    </div>

                                    <dropdown-action :item="row" :actions="[
                                        {
                                            label: 'Ubah Data',
                                            icon: 'fas fa-pen',
                                            color_icon: 'success',
                                            action: 'edit',
                                            permission: 'daily.report.leads.edit'
                                        },
                                        {
                                            label: 'Bagikan',
                                            icon: 'fab fa-whatsapp',
                                            color: 'success',
                                            action: 'share',
                                            permission: 'daily.report.leads.share'
                                        },
                                        {
                                            type: 'divider'
                                        },
                                        {
                                            label: 'Hapus',
                                            icon: 'fas fa-trash',
                                            color: 'danger',
                                            action: 'delete',
                                            permission: 'daily.report.leads.delete'

                                        },
                                    ]" @edit="goToEdit(row.daily_report_id)"
                                        @delete="deleted('daily_report.deleted', row)" @share="shareTo(row)" />
                                </div>

                                <div class="card-body p-0 position-relative">
                                    <loading-overlay :show="isLoading" />
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="bg-light text-secondary small text-uppercase">
                                                <tr>
                                                    <th class="ps-4 py-3" style="width: 50%;">Kategori Aktivitas</th>
                                                    <th class="text-center py-3">Jumlah (Qty)</th>
                                                    <th class="text-center py-3">Closing (Deal)</th>
                                                </tr>
                                            </thead>
                                            <tbody class="border-top-0">
                                                <tr>

                                                    <td class="ps-4"
                                                        :class="row.closing > 0 || row.leads > 0 ? 'fw-semibold text-dark' : 'text-muted'">
                                                        <i class="fas fa-bullhorn me-2"
                                                            :class="row.closing > 0 || row.leads > 0 ? 'text-info' : ' text-secondary'"></i>
                                                        Leads
                                                    </td>

                                                    <td class="text-center bg-light bg-opacity-50 fw-bold"
                                                        :class="row.leads > 0 ? 'text-dark' : 'text-muted'">
                                                        {{ row.leads }}
                                                    </td>

                                                    <td class="text-center fw-bold"
                                                        :class="row.closing > 0 ? 'text-success bg-success bg-opacity-10' : 'text-muted'">
                                                        {{ row.closing }}
                                                    </td>

                                                </tr>

                                                <tr>
                                                    <td class="ps-4"
                                                        :class="row.fu_yesterday > 0 || row.fu_yesterday_closing > 0 ? 'fw-semibold text-dark' : 'text-muted'">
                                                        <i class="fas fa-history me-2"
                                                            :class="row.fu_yesterday > 0 || row.fu_yesterday_closing > 0 ? 'text-info' : 'text-secondary'"></i>
                                                        FU Konsumen
                                                        Kemarin (H-1)
                                                    </td>

                                                    <td class="text-center bg-light bg-opacity-50 fw-bold"
                                                        :class="row.fu_yesterday > 0 ? 'text-dark' : 'text-muted'">
                                                        {{ row.fu_yesterday }}
                                                    </td>

                                                    <td class="text-center fw-bold"
                                                        :class="row.fu_yesterday_closing > 0 ? 'text-success bg-success bg-opacity-10' : 'text-muted'">
                                                        {{ row.fu_yesterday_closing }}
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td class="ps-4"
                                                        :class="row.fu_before_yesterday > 0 || row.fu_before_yesterday_closing > 0 ? 'fw-semibold text-dark' : 'text-muted'">
                                                        <i class="fas fa-history me-2"
                                                            :class="row.fu_before_yesterday > 0 || row.fu_before_yesterday_closing > 0 ? 'text-info' : 'text-secondary'">
                                                        </i>
                                                        FU Konsumen
                                                        Kemarennya (H-2)
                                                    </td>

                                                    <td class="text-center bg-light bg-opacity-50 fw-bold"
                                                        :class="row.fu_before_yesterday > 0 ? 'text-dark' : 'text-muted'">
                                                        {{ row.fu_before_yesterday }}
                                                    </td>

                                                    <td class="text-center fw-bold"
                                                        :class="row.fu_before_yesterday_closing > 0 ? 'text-success bg-success bg-opacity-10' : 'text-muted'">
                                                        {{ row.fu_before_yesterday_closing }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="ps-4"
                                                        :class="row.fu_last_week > 0 || row.fu_last_week_closing > 0 ? 'fw-semibold text-dark' : 'text-muted'">
                                                        <i class="fas fa-calendar-week me-2"
                                                            :class="row.fu_last_week > 0 || row.fu_last_week_closing > 0 ? 'text-info' : 'text-secondary'">
                                                        </i>
                                                        FU
                                                        Konsumen Minggu Lalu
                                                    </td>

                                                    <td class="text-center bg-light bg-opacity-50 fw-bold"
                                                        :class="row.fu_last_week > 0 ? 'text-dark' : 'text-muted'">
                                                        {{ row.fu_last_week }}
                                                    </td>

                                                    <td class="text-center fw-bold"
                                                        :class="row.fu_last_week_closing > 0 ? 'text-success bg-success bg-opacity-10' : 'text-muted'">
                                                        {{ row.fu_last_week_closing }}
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td class="ps-4"
                                                        :class="row.engage_old_customer > 0 || row.engage_closing > 0 ? 'fw-semibold text-dark' : 'text-muted'">
                                                        <i class="fas fa-handshake me-2"
                                                            :class="row.engage_old_customer > 0 || row.engage_closing > 0 ? 'text-info' : 'text-secondary'"></i>
                                                        Engage Pelanggan
                                                        Lama
                                                    </td>
                                                    <td class="text-center bg-light bg-opacity-50 fw-bold"
                                                        :class="row.engage_old_customer > 0 ? 'text-dark' : 'text-muted'">
                                                        {{ row.engage_old_customer }}
                                                    </td>
                                                    <td class="text-center fw-bold"
                                                        :class="row.engage_closing > 0 ? 'text-success bg-success bg-opacity-10' : 'text-muted'">
                                                        {{ row.engage_closing }}
                                                    </td>
                                                </tr>

                                            </tbody>
                                            <tfoot class="bg-light border-top border">
                                                <tr class="fw-bold">
                                                    <td class="ps-4 text-end text-uppercase small py-3">Total Harian :
                                                    </td>
                                                    <td class="text-center py-3 fs-6 text-dark">
                                                        {{ (row.leads ?? 0) + (row.fu_yesterday ?? 0) +
                                                            (row.fu_before_yesterday ?? 0) + (row.fu_last_week ?? 0) +
                                                            (row.engage_old_customer ?? 0) }}
                                                    </td>
                                                    <td
                                                        class="text-center py-3 fs-6 bg-success bg-opacity-25 text-success-emphasis">
                                                        {{ (row.closing ?? 0) + (row.fu_yesterday_closing ?? 0) +
                                                            (row.fu_before_yesterday_closing ?? 0) +
                                                            (row.fu_last_week_closing ?? 0) + (row.engage_closing ?? 0) }}
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="card border-0 shadow-sm rounded-4 overflow-hidden p-3 pb-0">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                                    <div class="text-muted pb-3">
                                        Menampilkan <strong>{{ props.dailyReport?.from ?? 0 }}</strong> -
                                        <strong>{{ props.dailyReport?.to ?? 0 }}</strong> dari
                                        <strong>{{ props.dailyReport?.total ?? 0 }}</strong> data
                                    </div>
                                    <pagination size="pagination-sm" :links="props.dailyReport?.links"
                                        routeName="daily_report" :additionalQuery="{
                                            limit: filters.limit,
                                            order_by: filters.order_by,
                                            start_date: filters.start_date,
                                            end_date: filters.end_date,
                                        }" />
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <ShareModal :previewText="previewText" :show="showShareModal" @update:show="showShareModal = $event"
                :report="selectedReport" />
            <ModalExport :form="form" :information="information" :show="showModal" @update:show="showModal = $event" />
        </template>
    </app-layout>
</template>
<style scoped>
.table.table-striped tbody tr:nth-of-type(odd) {
    background-color: #c2dff731;
}

.table.table-striped tbody tr:nth-of-type(even) {
    background-color: #ffffff;
}

.table.table-hover tbody tr:hover {
    background-color: rgba(0, 183, 255, 0.171);
    transition: all 0.15s ease-in-out;
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
