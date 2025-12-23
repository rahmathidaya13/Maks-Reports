<script setup>
import { computed, nextTick, onMounted, reactive, ref, watch } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { debounce } from "lodash";
import moment from "moment";
import { swalAlert, swalConfirmDelete } from "@/helpers/swalHelpers";
import axios from "axios";
moment.locale('id');

const page = usePage();
const message = computed(() => page.props.flash.message || "");
const props = defineProps({
    dailyReport: Object,
    filters: Object,
});

const filters = reactive({
    limit: props.filters.limit ?? 1,
    order_by: props.filters.order_by ?? "desc",
    page: props.filters?.page ?? 1,
    start_date: props.filters.start_date ?? '',
    end_date: props.filters.end_date ?? '',
})


const selectedRow = ref([]);
const isVisible = ref(false);
function deleteSelected() {
    if (!selectedRow.value.length) {
        return swalAlert('Peringatan', 'Tidak ada data yang dipilih.', 'warning');
    }
    swalConfirmDelete({
        title: 'Hapus Data Terpilih',
        text: `Yakin ingin menghapus ${selectedRow.value.length} data terpilih?`,
        confirmText: 'Ya, Hapus Semua!',
        onConfirm: () => {
            router.post(route('daily_report.destroy_all'), { all_id: selectedRow.value }, {
                preserveScroll: true,
                preserveState: false,
                replace: true
            })
        },
    })
}

watch(selectedRow, (val) => {
    if (val.length > 0) {
        isVisible.value = true
    } else {
        isVisible.value = false
    }
})
// atur warna badge sesuai jenis permission

const deleted = (nameRoute, data) => {
    swalConfirmDelete({
        title: 'Hapus',
        text: `Kamu yakin ingin menghapus laporan leads ini?`,
        confirmText: 'Ya, Hapus!',
        onConfirm: () => {
            router.delete(route(nameRoute, data.daily_report_id), { preserveScroll: false, replace: true });
        },
    })
}


const handleDownload = (type) => {
    if (type === "pdf") {
        router.get(route("users", { format: "pdf" }));
    } else if (type === "excel") {
        router.get(route("users", { format: "excel" }));
    }
};


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

// tutup modal SETELAH Bootstrap selesai animasi
function closeModal() {
    showModal.value = false
    form.start_date_dw = '';
    form.end_date_dw = '';
}

// form untuk kirim berdasarkan tanggal
const form = reactive({
    start_date_dw: '',
    end_date_dw: '',
})
function downloadPdf() {
    window.open(
        route('daily_report.print_to_pdf', {
            start_date: form.start_date_dw,
            end_date: form.end_date_dw
        }),
        '_blank'
    )
}
function downloadExcel() {
    window.open(
        route('daily_report.print_to_excel', {
            start_date: form.start_date_dw,
            end_date: form.end_date_dw
        }),
        '_self'
    )
}

const isDisableBtnDownload = computed(() => {
    return !(form.start_date_dw && form.end_date_dw);
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
const resetField = () => {
    form.start_date_dw = '';
    form.end_date_dw = '';
}
// =========Batas Fungsi untuk Tampilkan Modal========== //

// permissions
const perm = page.props.auth.user;

// autofocus
const inputRef = ref(null);
onMounted(() => {
    inputRef.value.focus();
});
</script>
<template>

    <Head title="Halaman Laporan Harian" />
    <app-layout>
        <template #content>
            <loader-page ref="loaderActive" />
            <bread-crumbs :home="false" icon="fas fa-clipboard" title="Rekap Laporan Leads Harian"
                :items="[{ text: 'Laporan Harian' }]" />
            <callout type="success" :duration="10" :message="message" />
            <div class="row justify-content-center pb-3">

                <!-- filter -->
                <div class="col-12">
                    <div class="card mb-4 border-0 custom-filter-card">

                        <div class="card-header py-3 px-4 border-bottom ">
                            <h5 class="card-title fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                                <span
                                    class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 d-flex align-items-center justify-content-center"
                                    style="width: 32px; height: 32px;">
                                    <i class="fas fa-filter fa-sm"></i>
                                </span>
                                Filter
                            </h5>
                        </div>

                        <div class="card-body p-4">
                            <div class="row g-3 align-items-end">

                                <div class="col-xl-4 col-md-6">
                                    <input-label class="custom-label mb-2" for="start_date" value="TANGGAL AWAL" />
                                    <div class="position-relative">
                                        <i class="bi bi-calendar3 input-icon-left"></i>
                                        <text-input ref="inputRef" name="start_date" v-model="filters.start_date"
                                            type="date" :is-valid="false"
                                            input-class="input-fixed-height pe-3 border-0 input-height-1" />
                                    </div>
                                </div>

                                <div class="col-xl-4 col-md-6">
                                    <input-label class="custom-label mb-2" for="end_date" value="TANGGAL AKHIR" />
                                    <div class="input-group">
                                        <i class="bi bi-calendar3 input-icon-left"></i>
                                        <text-input name="end_date" v-model="filters.end_date" type="date"
                                            :is-valid="false"
                                            input-class="input-fixed-height pe-3 rounded-0 rounded-start border-0 input-height-1" />
                                        <base-button :disabled="isDisableBtnDatePicker" @click="applyDateRange"
                                            button-class="btn-filter-action"
                                            :variant="isDisableBtnDatePicker ? 'secondary' : 'primary'" name="set"
                                            label="Terapkan" />
                                    </div>
                                </div>

                                <div class="col-xl-2 col-md-6">
                                    <input-label class="custom-label mb-2" for="limit" value="BATAS DATA" />
                                    <div class="input-group">
                                        <select-input :is-valid="false" v-model="filters.limit" name="limit"
                                            select-class="border-0 border input-height-1" :options="[
                                                { value: 1, label: 'Default' },
                                                { value: 5, label: '5 Baris' },
                                                { value: 10, label: '10 Baris' },
                                                { value: 20, label: '20 Baris' },
                                                { value: 50, label: '50 Baris' },
                                                { value: 100, label: '100 Baris' },
                                            ]" />
                                    </div>
                                </div>

                                <div class="col-xl-2 col-md-6">
                                    <input-label class="custom-label mb-2" for="order_by" value="URUTKAN" />
                                    <div class="input-group">
                                        <select-input :is-valid="false" v-model="filters.order_by" name="order_by"
                                            select-class="border-0 border input-height-1" :options="[
                                                { value: 'desc', label: 'Terbaru' },
                                                { value: 'asc', label: 'Terlama' },
                                            ]" />
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>


                <div class="col-12">

                    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                        <div
                            class="card-header bg-white py-3 px-4 border-bottom-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                                    <i class="fas fa-calendar-day fs-4"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0 text-dark">Laporan Leads Harian</h5>
                                    <p class="text-muted small mb-0">Monitor aktivitas leads dan closing harian.</p>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button class="btn btn-light text-secondary border shadow-sm" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#infoCollapse" aria-expanded="false">
                                    <i class="fas fa-info-circle me-1"></i> Info Istilah
                                </button>

                                <button-delete-all v-if="perm.permissions.includes('daily.report.leads.delete')"
                                    text="Hapus Data" class="shadow-sm" :isVisible="isVisible"
                                    :deleted="deleteSelected" />

                                <base-button v-if="perm.permissions.includes('daily.report.leads.export')"
                                    variant="success" button-class="shadow-sm" icon="fas fa-download" @click="openModal"
                                    name="unduh" label="Unduh" />

                                <base-button v-if="perm.permissions.includes('daily.report.leads.create')"
                                    @click="goToCreate" button-class="shadow-sm" name="create" label="Buat Laporan"
                                    icon="fas fa-plus" />
                            </div>
                        </div>

                        <div class="collapse" id="infoCollapse">
                            <div class="card-body bg-info bg-opacity-10 border-top border-info border-opacity-25">
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
                        class="card border-0 shadow-sm rounded-4 py-5 text-center mb-4">
                        <div v-if="isLoading">
                            <loader-horizontal message="Memuat data..." />
                        </div>
                        <div class="card-body" :class="['blur-area', isLoading ? 'is-blurred' : '']">
                            <div class="opacity-25 mb-3">
                                <i class="fas fa-clipboard-list fa-4x text-muted"></i>
                            </div>
                            <h6 class="text-muted fw-bold">Belum ada laporan leads.</h6>
                            <p class="small text-muted">Silakan buat laporan baru untuk hari ini.</p>
                        </div>
                    </div>

                    <div v-else class="row g-4">

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

                                    <div class="dropdown">
                                        <button class="btn btn-light border shadow-sm px-3 fs-6" type="button"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v text-muted"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                                            <li>
                                                <button v-if="perm.permissions.includes('daily.report.leads.edit')"
                                                    @click="goToEdit(row.daily_report_id)"
                                                    class="dropdown-item py-2 d-flex align-items-center gap-2">
                                                    <i class="fas fa-pencil-alt text-info"></i> Ubah Data
                                                </button>
                                            </li>
                                            <li>
                                                <button v-if="perm.permissions.includes('daily.report.leads.delete')"
                                                    @click="deleted('daily_report.deleted', row)"
                                                    class="dropdown-item py-2 d-flex align-items-center gap-2 text-danger">
                                                    <i class="fas fa-trash-alt"></i> Hapus
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div v-if="isLoading"
                                    class="position-absolute w-100 h-100 bg-white opacity-75 d-flex align-items-center justify-content-center"
                                    style="z-index: 10;">
                                    <div class="text-center">
                                        <div class="spinner-border text-primary mb-2" role="status"></div>
                                        <p class="fw-bold text-dark">Memproses...</p>
                                    </div>
                                </div>

                                <div class="card-body p-0" :class="['blur-area', isLoading ? 'is-blurred' : '']">
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
                                                        <i class="fas fa-handshake text-info me-2"></i> Engage Pelanggan
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
                                            <tfoot class="bg-light border-top">
                                                <tr class="fw-bold">
                                                    <td class="ps-4 text-end text-uppercase small py-3">Total Harian :
                                                    </td>
                                                    <td class="text-center py-3 fs-6 text-dark">
                                                        {{ (row.leads ?? 0) + (row.fu_yesterday ?? 0) +
                                                            (row.fu_before_yesterday ?? 0) + (row.fu_last_week ?? 0) +
                                                            (row.engage_old_customer ?? 0) }}
                                                    </td>
                                                    <td
                                                        class="text-center py-3 fs-6 bg-success bg-opacity-25 text-success-emphasis border-start border-success border-opacity-25">
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

                        <div class="col-12" v-if="props.dailyReport?.data.length > 0">
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

            <!-- modal open -->
            <div class="row" v-if="showModal">
                <div class="col-xl-12 col-sm-12">
                    <modal @opened="openModal" size="modal-lg" :footer="false" icon="fas fa-download" v-if="showModal"
                        :show="showModal" title="Unduh Laporan" @update:show="showModal = $event" @closed="closeModal">
                        <template #body>

                            <div class="callout callout-info shadow-sm">
                                <h5><i class="fas fa-bullhorn"></i> Informasi</h5>
                                <ul class="small ps-3 mb-0">
                                    <li>Pastikan rentang tanggal valid untuk mengunduh laporan.</li>
                                    <li>Data laporan akan difilter sesuai <b>Tanggal Awal</b> dan <b>Tanggal Akhir</b>
                                        yang
                                        pilih.</li>
                                    <li>Jika tidak ada data pada periode tersebut, laporan tetap dapat diunduh namun
                                        berisi
                                        informasi kosong.</li>
                                    <li>Laporan dapat diunduh dalam format <b>PDF</b> atau <b>Excel</b>.</li>

                                    <li>Untuk mencetak laporan pada hari ini masukan <b>Tanggal Awal</b> dan
                                        <b>Tanggal Akhir</b> sesuai dengan Tanggal hari ini.
                                    </li>
                                </ul>
                            </div>
                            <div class="card text-bg-grey">
                                <div class="card-body">
                                    <div class="row g-2">
                                        <div class="col-xl-6 col-sm-6 col-md-6">
                                            <input-label ref="start_dateRef" class="fw-semibold" for="start_date_dw"
                                                value="Tanggal Awal" />
                                            <text-input type="date" name="start_date_dw" v-model="form.start_date_dw" />
                                        </div>
                                        <div class="col-xl-6 col-md-6 col-sm-6">
                                            <input-label class="fw-semibold" for="end_date_dw" value="Tanggal Akhir" />
                                            <text-input type="date" name="end_date_dw" v-model="form.end_date_dw" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr />
                            <div v-if="information" class="text-bg-grey border rounded-3 p-3 mb-4 shadow-sm">
                                <ul class="list-unstyled small mb-0">
                                    <li><b>Periode:</b>
                                        {{ information.first_date }} s/d {{ information.last_date }}
                                    </li>
                                    <li>
                                        <b>Minggu Ke:</b>
                                        {{ information.week_start }}
                                        <template v-if="information.week_start !== information.week_end">
                                            s/d {{ information.week_end }}
                                        </template>
                                    </li>
                                    <li><b>Total Baris Data:</b> {{ information.total_rows }}</li>
                                    <li><b>Total Leads:</b> {{ information.total_leads ?? '-' }}</li>
                                    <li><b>Total Closing:</b> {{ information.total_closing ?? '-' }}</li>
                                    <li><b>Total Followup:</b> {{ information.total_fu ?? '-' }}</li>
                                    <li><b>Total Followup Closing:</b> {{ information.total_fu_closing ?? '-' }}</li>
                                    <li><b>Total Engage Old Customer:</b> {{ information.total_engage_old_customer ??
                                        '-' }}</li>
                                    <li><b>Total Engage Closing:</b> {{ information.total_engage_closing ?? '-' }}</li>
                                </ul>

                            </div>

                            <div class="d-flex justify-content-between ">
                                <base-button class="mb-2" @click="resetField" type="button" name="cancel" label="Batal"
                                    variant="outline-danger" />
                                <div class="d-flex gap-2">
                                    <base-button :disabled="isDisableBtnDownload" @click="downloadPdf" type="button"
                                        icon="fas fa-file-pdf" :variant="!isDisableBtnDownload ? 'danger' : 'secondary'"
                                        class="bg-gradient" name="print_pdf" label="Unduh PDF" />
                                    <base-button :disabled="isDisableBtnDownload" @click="downloadExcel" type="button"
                                        icon="fas fa-file-excel"
                                        :variant="!isDisableBtnDownload ? 'success' : 'secondary'" class="bg-gradient"
                                        name="print_excel" label="Unduh Excel" />
                                </div>
                            </div>

                        </template>
                    </modal>
                </div>
            </div>

        </template>
    </app-layout>
</template>
<style scoped>
.table-overlay {
    max-height: 70vh;
    overflow-y: auto;
    padding-right: 6px;
    position: relative;
}


.blur-area {
    transition: all 0.3s ease;
}

.blur-area.is-blurred {
    filter: blur(3px);
    pointer-events: none;
    user-select: none;
    opacity: 0.6;
}

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

.line-table {
    height: 1px;
    width: 100%;
    background: rgba(0, 0, 0, 0.205);
    margin: 10px 0;
}

.notes {
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
    max-height: 300px;
}



/* Container Kartu Filter */
.custom-filter-card {
    background: #ffffff;
    border-radius: 12px;
    /* Sudut lebih bulat */
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.04);
    /* Shadow sangat halus */
    transition: all 0.3s ease;
}

.custom-filter-card:hover {
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
    /* Efek naik saat hover */
}

/* Label yang lebih rapi */
.custom-label {
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    color: #6c757d;
    /* Warna abu-abu profesional */
    text-transform: uppercase;
}

/* Styling Input & Select agar seragam */
/* Note: CSS ini menargetkan elemen input yang dirender oleh komponen Vue kamu */
.custom-filter-card input,
.custom-filter-card select,
.custom-filter-card .input-group-text {
    border-color: #e9ecef;
    padding-top: 0.6rem;
    padding-bottom: 0.6rem;
    font-size: 0.9rem;
}

/* Efek saat input diklik (Fokus) */
.custom-filter-card input:focus,
.custom-filter-card select:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 4px rgba(13, 109, 253, 0.185);
    /* Ring fokus yang lembut */
    background-color: #fff;
}

/* Tombol Terapkan (Filter Action) */
.btn-filter-action {
    border-top-right-radius: 6px !important;
    border-bottom-right-radius: 6px !important;
    font-weight: 600;
    font-size: 0.85rem;
    padding-left: 1.5rem;
    padding-right: 1.5rem;
    z-index: 5;
    /* Pastikan tombol di atas border input */
}
</style>
