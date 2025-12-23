<script setup>
import { computed, nextTick, onMounted, reactive, ref, watch } from "vue";
import { Head, Link, router, useForm, usePage } from "@inertiajs/vue3";
import { debounce } from "lodash";
import moment from "moment";
import { swalAlert, swalConfirmDelete } from "@/helpers/swalHelpers";
import { highlight } from "@/helpers/highlight";
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

const filters = reactive({
    keyword: props.filters.keyword ?? '',
    limit: props.filters.limit ?? null,
    order_by: props.filters.order_by ?? null,
    start_date: props.filters.start_date ?? null,
    end_date: props.filters.end_date ?? null,
    page: props.filters?.page ?? 1,
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
    isLoading.value = true
    router.get(route("story_report"), filters, {
        preserveScroll: true,
        replace: true,
        preserveState: true,
        only: ["storyReport", "filters"], // lebih hemat bandwidth jika pakai Inertia partial reload
        onFinish: () => {
            // Selesai apapun hasilnya → loader hilang
            isLoading.value = false
        }
    });
}, 1000);

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
    filters.keyword = ''
    filters.limit = null
    filters.order_by = null
    filters.start_date = null
    filters.end_date = null

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
const isAllSelected = computed(() => {
    const rows = props.storyReport?.data ?? [];
    return rows.length > 0 && selected.value.length === rows.length;

})
const isSelected = (id) => {
    return selected.value.includes(id);
}
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

const perm = page.props.auth.user

const hasActiveFilter = computed(() => {
    return (
        filters.keyword !== '' ||
        filters.start_date !== null ||
        filters.end_date !== null ||
        filters.limit !== null ||
        filters.order_by !== null
    )
})

const fileterFields = computed(() => [
    {
        key: 'keyword',
        label: 'Pencarian',
        type: 'text',
        col: 'col-xl-8 col-12',
        props: {
            placeholder: 'Masukan ID Report',
            inputClass: ' input-height-1',
            isValid: false,
            autofocus: true
        }
    },
    {
        key: 'limit',
        label: 'Batas',
        type: 'select',
        col: 'col-xl-2 col-md-6 col-6',
        props: {
            selectClass: ' input-height-1',
            isValid: false,
        },
        options: [
            { value: null, label: 'Pilih Batas Data' },
            { value: 10, label: '10' },
            { value: 20, label: '20' },
            { value: 30, label: '30' },
            { value: 50, label: '50' },
            { value: 100, label: '100' },
        ]
    },
    {
        key: 'order_by',
        label: 'Urutan',
        type: 'select',
        col: 'col-xl-2 col-md-6 col-6',
        props: {
            selectClass: 'input-height-1',
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
        props: {
            inputClass: ' input-height-1',
            isValid: false,
        }
    },
    {
        key: 'end_date',
        label: 'Tanggal Akhir',
        type: 'date',
        col: 'col-xl-6 col-md-6 col-6',
        props: {
            inputClass: ' input-height-1',
            isValid: false,
        }
    },

    //  button trigger
    {
        key: 'reset',
        label: 'Bersihkan',
        type: 'button',
        name: 'reset',
        class: 'btn-secondary',
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
        class: 'btn-secondary',
        icon: 'fas fa-filter',
        class: !hasActiveFilter.value ? 'btn-secondary' : 'btn-primary',
        disabled: !hasActiveFilter.value,
        handler: () => applyDateRange()
    },
]);
</script>
<template>

    <Head title="Halaman Laporan Update Status" />
    <app-layout>
        <template #content>
            <loader-page ref="loaderActive" />
            <bread-crumbs :home="false" icon="fas fa-sticky-note" title="Laporan Update Status"
                :items="[{ text: 'Laporan Update Status' }]" />
            <callout type="success" :duration="10" :message="message" />

            <div class="row pb-3">
                <div class="col-xl-12 col-12 mb-3">
                    <filter-dynamic title="Filter" v-model="filters" :fields="fileterFields" />
                </div>
                <div class="col-12 col-xl-12">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">

                        <div
                            class="card-header bg-white py-3 px-4 border-bottom-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h5 class="fw-bold mb-0 text-dark">Laporan Status</h5>
                                <p class="text-muted small mb-0">Rekapitulasi update status harian.</p>
                            </div>

                            <div class="d-flex gap-2">
                                <transition name="fade">
                                    <button
                                        v-if="perm.permissions.includes('status.report.delete') && selected.length > 0"
                                        @click="deletedAll" :disabled="!isVisibleButton" type="button"
                                        class="btn btn-danger rounded-3 shadow-sm px-3 d-flex align-items-center animate__animated animate__fadeIn">
                                        <i class="fas fa-trash-alt me-2"></i>
                                        Hapus ({{ selected.length }})
                                    </button>
                                </transition>

                                <base-button v-if="perm.permissions.includes('status.report.export')"
                                    class="btn-success rounded-3 shadow-sm text-white" icon="fas fa-download"
                                    @click="openModal" name="unduh" label="Unduh" />

                                <div v-if="perm.permissions.includes('status.report.create')">
                                    <base-button @click="createReport" class="btn-primary rounded-3 shadow-sm fw-bold"
                                        name="create" label="Buat Laporan" icon="fas fa-plus" />
                                </div>
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
                                    <thead class="bg-light text-uppercase text-secondary fw-bold"
                                        style="letter-spacing: 0.5px;">
                                        <tr>
                                            <th class="text-center" width="50"
                                                v-if="perm.permissions.includes('status.report.delete')">
                                                <div class="form-check d-flex justify-content-center">
                                                    <input :disabled="!storyReport?.data.length" type="checkbox"
                                                        class="form-check-input shadow-none cursor-pointer"
                                                        :checked="isAllSelected" @change="toggleAll($event)" />
                                                </div>
                                            </th>

                                            <th class="text-center">No</th>
                                            <th class="text-center">ID Report</th>
                                            <th class="text-start">Tanggal & Info</th>
                                            <th class="text-center">Jam</th>
                                            <th class="text-center">Jumlah Status</th>
                                            <th class="text-center">Dibuat</th>
                                            <th class="text-center">Diperbarui</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>

                                    <tbody class="border-top-0">

                                        <tr v-if="!storyReport?.data.length">
                                            <td :colspan="(perm.permissions.includes('status.report.delete') ? 9 : 8)"
                                                class="text-center py-5">
                                                <div class="py-4">
                                                    <i class="fas fa-folder-open fa-3x text-muted opacity-25 mb-3"></i>
                                                    <p class="text-muted fw-semibold">Tidak ada data laporan ditemukan.
                                                    </p>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr v-for="(row, rowIndex) in storyReport?.data" :key="rowIndex"
                                            :id="row.story_status_id" :class="[
                                                { 'bg-primary bg-opacity-10': isSelected(row.story_status_id) },
                                                highlightId.includes(row.story_status_id) ? (highlightType === 'create' ? 'blink-green' : 'blink-blue') : ''
                                            ]">

                                            <td class="text-center"
                                                v-if="perm.permissions.includes('status.report.delete')">
                                                <div class="form-check d-flex justify-content-center">
                                                    <input type="checkbox"
                                                        class="form-check-input shadow-none cursor-pointer"
                                                        :value="row.story_status_id" v-model="selected" />
                                                </div>
                                            </td>

                                            <td class="text-center text-muted fw-bold">
                                                {{ rowIndex + 1 + (storyReport?.current_page - 1) *
                                                    storyReport?.per_page }}
                                            </td>

                                            <td class="text-center">
                                                <span class="font-monospace bg-light border px-2 py-1 rounded text-dark"
                                                    v-html="highlight(row.report_code, filters.keyword)">
                                                </span>
                                            </td>

                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-semibold text-dark">
                                                        <i class="far fa-calendar-alt me-1 text-primary small"></i>
                                                        {{ daysTranslate(row.report_date) }}
                                                    </span>
                                                    <small class="text-muted fst-italic mt-1"
                                                        style="font-size: 0.75rem;">
                                                        {{ row.informasi }}
                                                    </small>
                                                </div>
                                            </td>

                                            <td class="text-center">
                                                <span class="badge bg-light text-dark border fw-normal"
                                                    style="font-size: 0.9rem;">
                                                    <i class="far fa-clock me-1"></i> {{ row.report_time.slice(0, 5) }}
                                                </span>
                                            </td>

                                            <td class="text-center">
                                                <span class="fw-bold fs-6 text-primary">
                                                    {{ row.count_status }}
                                                </span>
                                            </td>

                                            <td class="text-center text-muted">
                                                {{ moment(row.created_at).format('H:mm A') }}
                                            </td>
                                            <td class="text-center text-muted">
                                                {{ row.updated_at === row.created_at ? '-' :
                                                    moment(row.updated_at).format('H:mm A') }}
                                            </td>

                                            <td class="text-center">
                                                <div class="dropdown dropstart">
                                                    <button class="btn btn-light bg-gradient border text-secondary"
                                                        type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="fas fa-cog"></i>
                                                    </button>
                                                    <ul
                                                        class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                                                        <li v-if="perm.permissions.includes('status.report.edit')">
                                                            <button @click.prevent="goEdit(row.story_status_id)"
                                                                class="dropdown-item py-2 d-flex align-items-center gap-2 fw-semibold">
                                                                <i class="fas fa-pencil-alt text-info"></i> Ubah
                                                            </button>
                                                        </li>
                                                        <li v-if="perm.permissions.includes('status.report.share')">
                                                            <button
                                                                class="dropdown-item py-2 d-flex align-items-center gap-2 fw-semibold">
                                                                <i class="fas fa-share-alt text-primary"></i> Bagikan
                                                            </button>
                                                        </li>
                                                        <li v-if="perm.permissions.includes('status.report.delete')">
                                                            <div class="dropdown-divider"></div>
                                                            <button
                                                                @click.prevent="deleted('story_report.deleted', row)"
                                                                class="dropdown-item py-2 d-flex align-items-center gap-2 text-danger fw-semibold">
                                                                <i class="fas fa-trash-alt"></i> Hapus
                                                            </button>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>

                                    <tfoot class="bg-light" v-if="storyReport?.data.length">
                                        <tr>
                                            <td :colspan="(perm.permissions.includes('status.report.delete') ? 5 : 4)"
                                                class="text-end fw-bold text-uppercase text-secondary py-3 pe-3">
                                                Total Keseluruhan :
                                            </td>

                                            <td
                                                class="text-center fw-bolder text-dark py-3 fs-6 bg-warning bg-opacity-10 border-start border-end border-warning border-opacity-25">
                                                {{ props.totalToday ?? props.totalWithFilter ?? '0' }}
                                            </td>

                                            <td colspan="3"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <div class="card-footer bg-white border-top py-3" v-if="storyReport?.data.length">
                            <div class="d-flex flex-wrap justify-content-between align-items-center">
                                <div class="text-muted mb-2 mb-md-0">
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
