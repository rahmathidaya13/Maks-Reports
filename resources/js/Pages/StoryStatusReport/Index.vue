<script setup>
import { computed, nextTick, onMounted, reactive, ref, watch } from "vue";
import { Head, Link, router, useForm, usePage } from "@inertiajs/vue3";
import { debounce } from "lodash";
import moment from "moment";
import { swalAlert, swalConfirmDelete } from "@/helpers/swalHelpers";
import axios from "axios";
moment.locale('id');

const page = usePage();
const message = computed(() => page.props.flash.message || "");

const highlightId = page.props.flash.highlight_by_id ?? [];
const highlightType = page.props.flash.highlight_type ?? null


const props = defineProps({
    storyReport: Object,
    filters: Object,
    totalToday: Number,
    summary: Object
});
// console.log(props.summary);
// const data = props.storyReport.data
// // Reorder data → taruh highlightId di paling atas
// if (highlightId) {
//     props.storyReport.data = [
//         ...data.filter(r => highlightId.includes(r.story_status_id)),
//         ...data.filter(r => !highlightId.includes(r.story_status_id)),
//     ];
// }

const filters = reactive({
    limit: props.filters.limit ?? 10,
    order_by: props.filters.order_by ?? "desc",
    page: props.filters?.page ?? 1,
    start_date: props.filters.start_date ?? '',
    end_date: props.filters.end_date ?? '',
})


const deleted = (nameRoute, data) => {
    swalConfirmDelete({
        title: 'Hapus',
        text: `Kamu yakin ingin menghapus laporan ID ${data.report_code} ? Tindakan ini tidak dapat kembalikan data yang terhapus!`,
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
    router.get(route("story_report"), filters, {
        preserveScroll: true,
        replace: true,
        preserveState: true,
        only: ["storyReport", "filters"], // optional: lebih hemat bandwidth jika pakai Inertia partial reload
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

//hapus semua
const selected = ref([]);
const isVisibleButton = ref(false)
const isAllSelected = computed(() => {
    const rows = props.storyReport?.data ?? [];
    return rows.length > 0 && selected.value.length === rows.length;

})
const toggleAll = (evt) => {
    if (evt.target.checked) {
        selected.value = props.storyReport?.data.map(r => r.story_status_id);
    } else {
        selected.value = [];
    }
}
const deletedAll = () => {
    if (!selected.value.length) {
        return swalAlert('Peringatan', 'Tidak ada data yang dipilih.', 'warning');
    }
    swalConfirmDelete({
        title: 'Hapus Data Terpilih',
        text: `Yakin ingin menghapus ${selected.value.length} data terpilih?`,
        confirmText: 'Ya, Hapus Semua!',
        onConfirm: () => {
            // console.log(selected.value);
            router.post(route('story_report.destroy_all'), { ids: selected.value }, {
                preserveScroll: true,
                preserveState: false,
            })
        },
    })
}
watch(selected, (val) => {
    if (val.length > 0) {
        isVisibleButton.value = true;
    } else {
        isVisibleButton.value = false
    }
})
const loaderActive = ref(null)
const createReport = () => {
    loaderActive.value?.show("Memproses...");
    router.get(route("story_report.create"), {}, {
        onFinish: () => {
            loaderActive.value?.hide()
        }
    });
}

const goEdit = (id) => {
    loaderActive.value?.show("Sedang memuat data...");
    router.get(route("story_report.edit", id), {}, {
        onFinish: () => loaderActive.value?.hide()
    });
}

// const downloadItems = [
//     { id: 'pdf', text: 'Unduh PDF', icon: 'fas fa-file-pdf text-danger' },
//     { id: 'excel', text: 'Unduh Excel', icon: 'fas fa-file-excel text-success' },
// ];

// const exportTo = (type) => {
//     console.log(type);
//     if (type === "pdf") {
//         window.location.href = route("story_report.print_to_pdf");
//     } else if (type === "excel") {
//         window.location.href = route("story_report.print_to_excel");
//     }
// };

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
        route('story_report.print_to_pdf', {
            start_date: form.start_date_dw,
            end_date: form.end_date_dw
        }),
        '_blank'
    )
}
function downloadExcel() {
    window.open(
        route('story_report.print_to_excel', {
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
        const { data } = await axios.get(route('story_report.information'), {
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
</script>
<template>

    <Head title="Halaman Laporan Update Status" />
    <app-layout>
        <template #content>
            <loader-page ref="loaderActive" />
            <bread-crumbs :home="false" icon="fas fa-sticky-note" title="Laporan Update Status"
                :items="[{ text: 'Laporan Update Status' }]" />
            <alert :duration="10" :message="message" />
            <div class="row">
                <div class="col-xl-12 col-sm-12">
                    <div class="card mb-3 rounded-3 p-1 bg-light overflow-hidden shadow-sm">
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
                        <transition name="fade">
                            <button v-if="isVisibleButton" @click="deletedAll" type="button"
                                class="btn btn-danger position-relative bg-gradient">
                                <i class="fas fa-trash"></i> Hapus
                                <span v-if="selected.length > 0"
                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">
                                    {{ selected.length }}
                                </span>
                            </button>
                        </transition>

                        <div class="d-inline-flex ms-auto gap-1 align-items-center">

                            <base-button variant="success" icon="fas fa-download" @click="openModal" class="bg-gradient"
                                name="unduh" label="Unduh" />

                            <div class="position-relative">
                                <base-button @click="createReport" class="bg-gradient" name="create"
                                    label="Buat Laporan" icon="fas fa-plus" />
                            </div>
                        </div>
                    </div>
                    <div class="card mb-4 overflow-hidden rounded-3 shadow-sm">
                        <div v-if="isLoading">
                            <loader-horizontal message="Sedang memproses data" />
                        </div>

                        <div class="card-body p-0" :class="['blur-area', isLoading ? 'is-blurred' : '']">
                            <div class="table-responsive">
                                <table class="table align-middle table-hover text-nowrap table-striped table-bordered">
                                    <thead class="table-dark position-sticky">
                                        <tr>
                                            <th>
                                                <div class="form-check d-flex justify-content-center">
                                                    <input type="checkbox" class="form-check-input"
                                                        :checked="isAllSelected" @change="toggleAll($event)" />
                                                </div>
                                            </th>
                                            <th class="text-center">No</th>
                                            <th class="text-center">ID Status</th>
                                            <th class="text-center">Tanggal</th>
                                            <th class="text-center">Jam</th>
                                            <th class="text-center">Jumlah Status</th>
                                            <th class="text-center">Dibuat</th>
                                            <th class="text-center">Diperbarui</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr v-if="!storyReport?.data.length">
                                            <td colspan="9" class="text-center text-muted">
                                                Tidak ada data ditemukan
                                            </td>
                                        </tr>

                                        <tr :class="[
                                            highlightId.includes(row.story_status_id)
                                                ? (highlightType === 'create' ? 'blink-green' : 'blink-blue')
                                                : ''
                                        ]" :id="row.story_status_id" v-for="(row, rowIndex) in storyReport?.data"
                                            :key="rowIndex">

                                            <td class="text-center">
                                                <div class="form-check d-flex justify-content-center">
                                                    <input type="checkbox" class="form-check-input"
                                                        :name="row.story_status_id" :id="row.story_status_id"
                                                        :value="row.story_status_id" v-model="selected" />
                                                </div>
                                            </td>

                                            <td style="width: 5%;" class="text-center"> {{ rowIndex + 1 +
                                                (storyReport?.current_page - 1) *
                                                storyReport?.per_page }}
                                            </td>

                                            <td class="text-center">{{ row.report_code }}</td>
                                            <td class="text-start lh-sm">
                                                <div>
                                                    {{ daysTranslate(row.report_date) }}
                                                </div>
                                                <small class="fw-normal text-muted" style="font-size: 12px;">{{
                                                    row.informasi
                                                }}</small>
                                            </td>
                                            <td class="text-center">
                                                <span>{{ row.report_time.slice(0, 5) }}</span>
                                            </td>

                                            <td class="text-center">{{ row.count_status }}
                                            </td>
                                            <td class="text-center ">{{
                                                moment(row.created_at).format('H:mm A') }}
                                            </td>
                                            <td class="text-center">{{ row.updated_at === row.created_at
                                                ? '-'
                                                : moment(row.updated_at).format('H:mm A') }}
                                            </td>
                                            <td class="text-center">
                                                <div class="dropdown dropstart">
                                                    <button class="btn btn-secondary bg-gradient" type="button"
                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="fas fa-cog"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <button @click="goEdit(row.story_status_id)"
                                                                class="dropdown-item fw-semibold d-flex justify-content-between align-items-center">
                                                                Ubah <i class="bi bi-pencil-square text-info fs-5"></i>
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <button @click="deleted('story_report.deleted', row)"
                                                                class="dropdown-item fw-semibold d-flex justify-content-between align-items-center">
                                                                Hapus <i class="bi bi-trash-fill text-danger fs-5"></i>
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <button
                                                                class="dropdown-item fw-semibold d-flex justify-content-between align-items-center">
                                                                Bagikan <i
                                                                    class="bi bi-share-fill text-primary fs-5"></i>
                                                            </button>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="fw-bold table-dark">
                                        <tr>
                                            <td class="text-center border-0 border">
                                            </td>
                                            <td class="text-center border-0 border">Total</td>
                                            <td class="text-center border-0 border"></td>
                                            <td class="text-center border-0 border"></td>
                                            <td class="text-center border-0 border"></td>
                                            <td class="text-center border-0 border">
                                                {{ props.totalToday ?? props.totalWithFilter }}
                                            </td>
                                            <td class="text-center border-0 border"></td>
                                            <td class="text-center border-0 border"></td>
                                            <td class="text-center border-0 border"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <div v-if="props.storyReport?.data.length > 0"
                                class="d-flex flex-wrap justify-content-lg-between align-items-center flex-column flex-lg-row px-2">
                                <div class="mb-2 order-1 order-xl-0">
                                    Menampilkan <strong>{{ props.storyReport?.from ?? 0 }}</strong> sampai
                                    <strong>{{ props.storyReport?.to ?? 0 }}</strong> dari total
                                    <strong>{{ props.storyReport?.total ?? 0 }}</strong> data
                                </div>
                                <pagination size="pagination-sm" :links="props.storyReport?.links" routeName="story_report" :additionalQuery="{
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


            <div class="row">
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
                                    <li>Sistem akan menghitung otomatis <b>Total Baris Data</b> dan <b>Total Jumlah
                                            Status</b>.
                                    </li>
                                    <li>Jika tidak ada data pada periode tersebut, laporan tetap dapat diunduh namun
                                        berisi
                                        informasi kosong.</li>
                                    <li>Laporan dapat diunduh dalam format <b>PDF</b> atau <b>Excel</b>.</li>
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
                                    <li><b>Total Baris Data:</b> {{ information.total_rows }}</li>
                                    <li><b>Total Jumlah Status:</b> {{ information.total_status }}</li>
                                    <li>
                                        <b>Minggu Ke:</b>
                                        {{ information.week_start }}
                                        <template v-if="information.week_start !== information.week_end">
                                            s/d {{ information.week_end }}
                                        </template>
                                    </li>
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
</style>
