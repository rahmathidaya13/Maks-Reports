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
    storyReport: Object,
    filters: Object,
});

const filters = reactive({
    limit: props.filters.limit ?? 10,
    order_by: props.filters.order_by ?? "desc",
    page: props.filters?.page ?? 1,
    start_date: props.filters.start_date ?? '',
    end_date: props.filters.end_date ?? '',
})

const header = [
    { label: "No", key: "__index" },
    { label: "Tanggal", key: "report_date" },
    { label: "Jam", key: "report_time" },
    { label: "Jumlah", key: "count_status" },
    { label: "Catatan", key: "description" },
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
            router.post(route('story_report.destroy_all'), { all_id: selectedRow.value }, {
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
        text: `Kamu yakin ingin menghapus laporan ini?`,
        confirmText: 'Ya, Hapus!',
        onConfirm: () => {
            router.delete(route(nameRoute, data.story_status_id), { preserveScroll: false, replace: true });
        },
    })
}


// trigger button bila tanggal diisi
const isDisableBtnDatePicker = computed(() => {
    return !(filters.start_date && filters.end_date);
})

const handleDownload = (type) => {
    if (type === "pdf") {
        router.get(route("storyReport", { format: "pdf" }));
    } else if (type === "excel") {
        router.get(route("storyReport", { format: "excel" }));
    }
};


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
    const dateFormat = moment(dayValue).format('L');
    return dayConvert[dayName] + ", " + dateFormat ?? dayName;
}
function dateFormat(date, format) {
    const dates = moment(date).format(format);
    return dates;
}

const isLoading = ref(false)
const searchByDate = debounce((e) => {
    router.get(route("story_report"), filters, {
        preserveScroll: true,
        replace: true,
        preserveState: true,
        only: ["storyReport", "filters"], // optional: lebih hemat bandwidth jika kamu pakai Inertia partial reload
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


</script>
<template>

    <Head title="Halaman Laporan Update Status" />
    <app-layout>
        <template #content>
            <bread-crumbs :home="false" icon="fas fa-sticky-note" title="Laporan Update Status"
                :items="[{ text: 'Laporan Update Status' }]" />
            <alert :duration="10" :message="message" />
            <div class="row">
                <div class="col-xl-12 col-sm-12">

                    <!-- <div class="callout callout-info">
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
                    </div> -->

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
                                        { value: 10, label: 'default' },
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
                            <drop-down @download="handleDownload" />
                            <div class="position-relative">
                                <Link :href="route('story_report.create')" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Buat Laporan
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4 overflow-hidden rounded-3" :class="{ 'h-100': isLoading }">
                        <div v-if="isLoading">
                            <loader-horizontal />
                        </div>
                        <div class="table-responsive" v-else>
                            <table class="table align-middle table-hover text-wrap">
                                <thead class="table-dark">
                                    <tr>
                                        <th>
                                            <div class="form-check d-flex justify-content-center gap-2">
                                                <input type="checkbox" class="form-check-input"
                                                    @change="toggleSelectAll($event)" :checked="isAllSelected" />
                                            </div>
                                        </th>
                                        <th class="text-center">No</th>
                                        <th class="text-start">Tanggal</th>
                                        <th class="text-center">Jam</th>
                                        <th class="text-center">Jumlah</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <tr v-if="!storyReport?.data.length">
                                        <td colspan="6" class="text-center text-muted">
                                            Tidak ada data ditemukan
                                        </td>
                                    </tr>
                                    <tr :id="row.story_status_id" v-for="(row, rowIndex) in storyReport?.data"
                                        :key="rowIndex">
                                        <td class="text-center" style="width: 5%;">
                                            <div class="form-check d-flex justify-content-center gap-2">
                                                <input type="checkbox" class="form-check-input"
                                                    :name="row.story_status_id" :id="row.story_status_id"
                                                    :value="row.story_status_id" v-model="selected" />
                                            </div>
                                        </td>
                                        <td style="width: 5%;" class="text-center fw-semibold"> {{ rowIndex + 1 +
                                            (storyReport?.current_page - 1) *
                                            storyReport?.per_page }}</td>
                                        <td class="text-start fw-semibold">{{
                                            daysTranslate(row.report_date) }}</td>
                                        <td class="text-center fw-semibold">{{
                                            row.report_time.slice(0, 5) }}</td>
                                        <td class="text-center fw-semibold">{{ row.count_status }}
                                        </td>
                                        <td class="text-center">
                                            <div class="dropdown dropstart">
                                                <button class="btn btn-secondary" type="button"
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-cog"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <Link :href="route('story_report.edit', row.story_status_id)"
                                                            class="dropdown-item fw-semibold d-flex justify-content-between align-items-center">
                                                        Ubah <i class="fas fa-edit text-info"></i>
                                                        </Link>
                                                    </li>
                                                    <li>
                                                        <button @click="deleted('story_report.deleted', row)"
                                                            class="dropdown-item fw-semibold d-flex justify-content-between align-items-center">
                                                            Hapus <i class="fas fa-recycle text-danger"></i>
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <button
                                                            class="dropdown-item fw-semibold d-flex justify-content-between align-items-center">
                                                            Bagikan <i class="fas fa-share-alt text-primary"></i>
                                                        </button>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="fw-bold table-dark">
                                    <tr>
                                        <td></td>
                                        <td class="text-center">Total</td>
                                        <td></td>
                                        <td></td>
                                        <td class="text-center">
                                            {{storyReport?.data.reduce((t, r) => t + (r.count_status ?? 0), 0)}}
                                        </td>
                                        <td class="text-center"></td>
                                    </tr>
                                </tfoot>
                            </table>

                        </div>
                        <div v-if="!isLoading"
                            class="d-flex flex-wrap justify-content-lg-between align-items-center flex-column flex-lg-row p-3">
                            <div class="mb-2 order-1 order-xl-0">
                                Menampilkan <strong>{{ props.storyReport?.from ?? 0 }}</strong> sampai
                                <strong>{{ props.storyReport?.to ?? 0 }}</strong> dari total
                                <strong>{{ props.storyReport?.total ?? 0 }}</strong> data
                            </div>
                            <pagination :links="props.storyReport?.links" routeName="story_report" :additionalQuery="{
                                order_by: filters.order_by,
                                limit: filters.limit,
                                keyword: filters.keyword,
                            }" />
                        </div>
                    </div>


                </div>
            </div>
        </template>
    </app-layout>
</template>
<style scoped>
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
</style>
