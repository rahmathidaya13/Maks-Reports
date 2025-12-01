<script setup>
import { computed, reactive, ref, watch } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { debounce } from "lodash";
import moment from "moment";
import { swalAlert, swalConfirmDelete } from "@/helpers/swalHelpers";
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

const header = [
    { label: "No", key: "__index" },
    { label: "Tanggal", key: "date" },
    { label: "Leads", key: "leads" },
    { label: "FU Kemarin", key: "fu_yesterday" },
    { label: "FU Kemarinnya", key: "fu_before_yesterday" },
    { label: "FU Minggu Kemarinnya", key: "fu_last_week" },
    { label: "Engage Konsumen Lama", key: "engage_old_customer" },
    { label: "Catatan", key: "notes" },
    { label: "Aksi", key: "-" },
];


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
    const dateFormat = moment(dayValue).format('DD/MM/YYYY');
    return dayConvert[dayName] + ", " + dateFormat ?? dayName;
}

// trigger button bila tanggal diisi
const isDisableBtnDatePicker = computed(() => {
    return !(filters.start_date && filters.end_date);
})
const isLoading = ref(false)
const searchByDate = debounce(() => {
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
}, 1000);

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

</script>
<template>

    <Head title="Halaman Laporan Harian" />
    <app-layout>
        <template #content>
            <loader-page ref="loaderActive" />
            <bread-crumbs :home="false" icon="fas fa-clipboard" title="Laporan Leads Harian"
                :items="[{ text: 'Laporan Harian' }]" />
            <alert :duration="10" :message="message" />
            <div class="row">
                <div class="col-xl-12 col-sm-12">

                    <div class="callout callout-info">
                        <h5 class="fw-bold"><i class="fas fa-bullhorn me-2"></i>Informasi Laporan Leads Harian</h5>
                        <ul class="mb-0 ps-3">
                            <li><strong>Leads</strong> adalah calon konsumen baru yang pertama kali dihubungi pada hari
                                ini.
                            </li>
                            <li><strong>FU (Follow Up)</strong> adalah tindak lanjut yang dilakukan kepada konsumen yang
                                sebelumnya sudah pernah dihubungi.</li>
                            <li><strong>FU Kemarin (H-1)</strong> adalah follow up untuk konsumen yang dihubungi sehari
                                sebelumnya.</li>
                            <li><strong>FU Kemarennya (H-2)</strong> adalah follow up untuk konsumen yang dihubungi dua
                                hari
                                sebelumnya.</li>
                            <li><strong>FU Minggu Kemarennya</strong> adalah follow up untuk konsumen yang dihubungi
                                pada
                                minggu
                                lalu.</li>
                            <li><strong>Engage Pelanggan Lama</strong> adalah interaksi dengan pelanggan lama untuk
                                menjaga
                                hubungan dan menawarkan kebutuhan baru.</li>
                        </ul>
                    </div>

                    <div class="card mb-4 rounded-3 p-1 bg-light overflow-hidden shadow-sm">
                        <div class="row align-items-center p-2 g-2 pb-3">
                            <div class="col-xl-4 col-sm-6 col-md-3">
                                <input-label class="fw-bold mb-1" for="start_date" value="Tanggal Awal:" />
                                <div class="input-group">
                                    <text-input autofocus name="start_date" v-model="filters.start_date" type="date"
                                        :is-valid="false" />
                                </div>
                            </div>
                            <div class="col-xl-4 col-sm-6 col-md-3">
                                <input-label class="fw-bold mb-1" for="end_date" value="Tanggal Akhir:" />
                                <div class="input-group">
                                    <text-input name="end_date" v-model="filters.end_date" type="date"
                                        :is-valid="false" />
                                    <base-button :disabled="isDisableBtnDatePicker" @click="applyDateRange"
                                        class="bg-gradient" :variant="isDisableBtnDatePicker ? 'secondary' : 'primary'"
                                        name="set" label="Atur" />
                                </div>
                            </div>
                            <div class="col-xl-2 col-sm-6 col-md-3">
                                <input-label class="fw-bold mb-1" for="limit" value="Batas:" />
                                <div class="input-group">
                                    <select-input :is-valid="false" v-model="filters.limit" name="limit" :options="[
                                        { value: 1, label: 'Default' },
                                        { value: 5, label: '5' },
                                        { value: 10, label: '10' },
                                        { value: 20, label: '20' },
                                        { value: 50, label: '50' },
                                        { value: 100, label: '100' },
                                    ]" />
                                </div>
                            </div>
                            <div class="col-xl-2 col-sm-6 col-md-3">
                                <input-label class="fw-bold mb-1" for="order_by" value="Urutkan:" />
                                <div class="input-group">
                                    <select-input :is-valid="false" v-model="filters.order_by" name="order_by" :options="[
                                        { value: 'desc', label: 'Terbaru' },
                                        { value: 'asc', label: 'Terlama' },
                                    ]" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-2 d-flex justify-content-between flex-wrap gap-2 align-items-center">
                        <button-delete-all text="Hapus" :isVisible="isVisible" :deleted="deleteSelected" />
                        <div class="d-inline-flex ms-auto gap-1">
                            <!-- <drop-down @download="handleDownload" /> -->
                            <div class="position-relative">
                                <base-button @click="goToCreate" class="bg-gradient" name="create" label="Buat Laporan"
                                    icon="fas fa-plus" />
                            </div>
                        </div>
                    </div>

                    <div class="table-overlay">

                        <div v-if="!dailyReport.data.length"
                            class="card overflow-hidden rounded-3 shadow-sm py-5 text-center text-muted mb-4">
                            <div v-if="isLoading">
                                <loader-horizontal message="Sedang memperbarui data" />
                            </div>
                            <div class="card-body" :class="['blur-area', isLoading ? 'is-blurred' : '']">
                                <span>Tidak ada laporan leads ditemukan</span>
                            </div>
                        </div>

                        <div class="mb-3" :id="row.daily_report_id" v-for="(row, rowIndex) in dailyReport.data"
                            :key="rowIndex">

                            <div :class="['blur-area', isLoading ? 'is-blurred' : '']"
                                class="d-xl-flex d-md-flex d-sm-block align-items-center mb-2 mt-2 justify-content-between">
                                <h4 class="fw-bold">Laporan Leads:
                                    <span class="text-primary">
                                        {{ daysOnlyConvert(row.date) }}
                                    </span>
                                </h4>
                                <div class="dropdown">
                                    <button class="btn btn-danger bg-gradient" type="button" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        <i class="fas fa-eye"></i> Aksi
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <button @click="goToEdit(row.daily_report_id)"
                                                class="dropdown-item fw-semibold d-flex justify-content-between align-items-center">
                                                Ubah <i class="bi bi-pencil-square text-info fs-5"></i>
                                            </button>
                                        </li>
                                        <li>
                                            <button @click="deleted('daily_report.deleted', row)"
                                                class="dropdown-item fw-semibold d-flex justify-content-between align-items-center">
                                                Hapus <i class="bi bi-trash-fill text-danger fs-5"></i>
                                            </button>
                                        </li>
                                        <li>
                                            <button
                                                class="dropdown-item fw-semibold d-flex justify-content-between align-items-center">
                                                Bagikan <i class="bi bi-share-fill text-primary fs-5"></i>
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="card mb-0 overflow-hidden rounded-3 shadow-sm">
                                <div v-if="isLoading">
                                    <loader-horizontal message="Sedang memproses data" />
                                </div>
                                <div class="card-body p-0" :class="['blur-area', isLoading ? 'is-blurred' : '']">
                                    <div class="table-responsive">
                                        <table class="table align-middle mb-0 table-striped text-nowrap table-hover">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th class="text-start">Kategori</th>
                                                    <th class="text-center">Jumlah</th>
                                                    <th class="text-center">Closing</th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                                <tr>
                                                    <td class="fw-semibold">Leads</td>
                                                    <td class="text-center fw-semibold">{{ row.leads }}</td>
                                                    <td class="text-center fw-semibold">{{ row.closing }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-semibold">FU Konsumen Kemarin (H-1)</td>
                                                    <td class="text-center fw-semibold">{{ row.fu_yesterday }}</td>
                                                    <td class="text-center fw-semibold">{{ row.fu_yesterday_closing }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-semibold">FU Konsumen Kemarennya (H-2)</td>
                                                    <td class="text-center fw-semibold">{{ row.fu_before_yesterday }}
                                                    </td>
                                                    <td class="text-center fw-semibold">{{
                                                        row.fu_before_yesterday_closing
                                                        }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-semibold">FU Konsumen Minggu Kemarennya</td>
                                                    <td class="text-center fw-semibold">{{ row.fu_last_week }}</td>
                                                    <td class="text-center fw-semibold">{{ row.fu_last_week_closing }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-semibold">Engage Konsumen Lama</td>
                                                    <td class="text-center fw-semibold">{{ row.engage_old_customer }}
                                                    </td>
                                                    <td class="text-center fw-semibold">{{ row.engage_closing }}</td>
                                                </tr>
                                            </tbody>
                                            <tfoot class="fw-bold table-dark">
                                                <tr>
                                                    <td class="border border-0">Total</td>
                                                    <td class="text-center border border-0">
                                                        {{
                                                            (row.leads ?? 0) +
                                                            (row.fu_yesterday ?? 0) +
                                                            (row.fu_before_yesterday ?? 0) +
                                                            (row.fu_last_week ?? 0) +
                                                            (row.engage_old_customer ?? 0)
                                                        }}
                                                    </td>
                                                    <td class="text-center border border-0"> {{
                                                        (row.closing ?? 0) +
                                                        (row.fu_yesterday_closing ?? 0) +
                                                        (row.fu_before_yesterday_closing ?? 0) +
                                                        (row.fu_last_week_closing ?? 0) +
                                                        (row.engage_closing ?? 0)
                                                        }}
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                        <div class="p-2 border rounded-0 bg-light">
                                            <strong>Catatan:</strong>
                                            <div class="mt-1 mb-3">
                                                <div v-if="row.notes !== null && row.notes.trim() !== '<p><br></p>'"
                                                    class="notes" v-html="row.notes">
                                                </div>
                                                <div v-else class="notes text-center align-middle">
                                                    Tidak ada catatan
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-if="props.dailyReport?.data.length > 0" :class="['blur-area', isLoading ? 'is-blurred' : '']"
                        class="mt-2 d-flex flex-wrap justify-content-lg-between align-items-center flex-column flex-lg-row">
                        <div class="mb-2 order-1 order-xl-0 order-lg-0 order-md-0">
                            Menampilkan <strong>{{ props.dailyReport?.from ?? 0 }}</strong> sampai
                            <strong>{{ props.dailyReport?.to ?? 0 }}</strong> dari total
                            <strong>{{ props.dailyReport?.total ?? 0 }}</strong> data
                        </div>
                        <pagination :links="props.dailyReport?.links" routeName="daily_report" :additionalQuery="{
                            limit: filters.limit,
                            order_by: filters.order_by,
                            start_date: filters.start_date,
                            end_date: filters.end_date,
                        }" />
                    </div>
                </div>
            </div>
        </template>
    </app-layout>
</template>
<style scoped>
.table-overlay {
    max-height: 60vh;
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
</style>
