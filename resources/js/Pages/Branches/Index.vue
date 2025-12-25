<script setup>
import { computed, reactive, ref, watch } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { debounce } from "lodash";
import { swalAlert, swalConfirmDelete } from "../../helpers/swalHelpers";
import { highlight } from "@/helpers/highlight";
import { formatText } from "@/helpers/formatText";
import moment from "moment";
moment.locale('id');


const page = usePage();
const message = computed(() => page.props.flash.message || "");
const props = defineProps({
    branch: Object,
    filters: Object,
});

// cek permission
const perm = page.props.auth.user.permissions


const filters = reactive({
    keyword: props.filters.keyword ?? '',
    limit: props.filters.limit ?? 10,
    order_by: props.filters.order_by ?? "desc",
    page: props.filters?.page ?? 1,
})

const isLoading = ref(false)
const liveSearch = debounce((e) => {
    isLoading.value = true
    router.get(route("branch"), filters, {
        preserveScroll: true,
        replace: true,
        preserveState: true,
        only: ["branch", "filters"], // optional: lebih hemat bandwidth jika kamu pakai Inertia partial reload
        onFinish: () => isLoading.value = false
    });
}, 1000);


const header = [
    { label: "No", key: "__index" },
    { label: "Kode Cabang", key: "branch_code" },
    { label: "Nama Cabang", key: "name" },
    { label: "No Telepon", key: "phone" },
    { label: "Alamat", key: "address" },
    { label: "Status", key: "status" },
    { label: "Dibuat Oleh", key: "created_by" },
    { label: "Dibuat", key: "created_at" },
    { label: "Diperbarui", key: "updated_at" },
    { label: "Aksi", key: "-" },
];
watch(
    () => [
        filters.keyword,
        filters.limit,
        filters.order_by,],
    () => {
        liveSearch();
    }
);


// CRUD OPERATION
const loaderActive = ref(null)
const create = () => {
    loaderActive.value?.show("Memproses...");
    router.get(route("branch.create"), {}, {
        onFinish: () => {
            loaderActive.value?.hide()
        }
    });
}

const edit = (id) => {
    loaderActive.value?.show("Sedang memuat data...");
    router.get(route("branch.edit", id), {}, {
        onFinish: () => loaderActive.value?.hide()
    });
}

const deleted = (nameRoute, data) => {
    swalConfirmDelete({
        title: 'Hapus',
        text: `Kamu ingin menghapus Cabang ${formatText(data.name)} ?`,
        confirmText: 'Ya, Hapus!',
        onConfirm: () => {
            loaderActive.value?.show("Sedang memuat data...");
            router.delete(route(nameRoute, data.branches_id), {
                onFinish: () => loaderActive.value?.hide(),
                preserveScroll: false,
                replace: true
            });
        },
    })
}
// end CRUD OPERATION

// MULTIPLE DELETE
const selectedRow = ref([]);
const isVisible = ref(false);

const isAllSelected = computed(() => {
    const rows = props.branch?.data ?? [];
    return rows.length > 0 && selectedRow.value.length === rows.length;
})

function deleteSelected() {
    if (!selectedRow.value.length) {
        return swalAlert('Peringatan', 'Tidak ada data yang dipilih.', 'warning');
    }
    swalConfirmDelete({
        title: 'Hapus Data Terpilih',
        text: `Yakin ingin menghapus ${selectedRow.value.length} data terpilih?`,
        confirmText: 'Ya, Hapus Semua!',
        onConfirm: () => {
            loaderActive.value?.show("Sedang memuat data...");
            router.post(route('branch.destroy_all'), { all_id: selectedRow.value }, {
                onFinish: () => loaderActive.value?.hide(),
                preserveScroll: true,
                preserveState: false,
            })
        },
    })
}
const isSelected = (id) => {
    return selectedRow.value.includes(id);
}
const toggleAll = (evt) => {
    if (evt.target.checked) {
        selectedRow.value = props.branch?.data.map(r => r.branches_id);
    } else {
        selectedRow.value = [];
    }
}
watch(selectedRow, (val) => {
    if (val.length > 0) {
        isVisible.value = true
    } else {
        isVisible.value = false
    }
})
// END MULTIPLE DELETE

// date convert
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
    const dateFormat = moment(dayValue).format('DD-MM-YYYY, HH:mm:ss');
    return dayConvert[dayName] + ", " + dateFormat ?? dayName;
}


const fileterFields = computed(() => [
    {
        key: 'keyword',
        label: 'Pencarian',
        type: 'text',
        col: 'col-xl-8 col-12',
        props: {
            placeholder: 'Masukan Pencarian...',
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
            selectClass: 'input-height-1',
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
]);
</script>
<template>

    <Head title="Halaman Cabang" />
    <app-layout>
        <template #content>
            <loader-page ref="loaderActive" />
            <bread-crumbs :home="false" icon="fas fa-building" title="Daftar Cabang"
                :items="[{ text: 'Daftar Cabang' }]" />
            <callout type="success" :duration="10" :message="message" />

            <div class="row pb-3">
                <div class="col-xl-12 col-12 mb-3">
                    <filter-dynamic title="Filter" v-model="filters" :fields="fileterFields" />
                </div>

                <div class="col-xl-12 col-12">
                    <div class="card card-modern border-0 shadow-sm rounded-4 overflow-hidden">
                        <div
                            class="card-header bg-white py-3 px-4 border-bottom-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h5 class="fw-bold mb-0 text-dark">Data Lokasi/Cabang</h5>
                                <p class="text-muted small mb-0">informasi Cabang terdaftar</p>
                            </div>

                            <div class="d-flex gap-2">
                                <transition name="fade">
                                    <button v-if="perm.includes('branches.delete') && selectedRow.length > 0"
                                        :disabled="!isVisible" @click="deleteSelected" type="button"
                                        class="btn btn-danger shadow-sm d-flex align-items-center animate-pop">
                                        <i class="fas fa-trash me-2"></i>
                                        <span class="fw-bold">Hapus ({{ selectedRow.length }})</span>
                                    </button>
                                </transition>

                                <button v-if="perm.includes('branches.create')" type="button" @click.prevent="create"
                                    class="btn btn-primary shadow-sm d-flex align-items-center px-3">
                                    <i class="fas fa-plus me-2"></i> Buat Baru
                                </button>


                            </div>
                        </div>

                        <div v-if="isLoading"
                            class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-flex justify-content-center align-items-center z-3">
                            <loader-horizontal message="Memuat..." />
                        </div>

                        <div class="card-body p-0" :class="['blur-area', isLoading ? 'is-blurred' : '']">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 custom-table text-nowrap">
                                    <thead class="bg-light">
                                        <tr>
                                            <th v-if="perm.includes('branches.delete')">
                                                <div class="form-check d-flex justify-content-center">
                                                    <input :disabled="!branch?.data.length" type="checkbox"
                                                        class="form-check-input" :checked="isAllSelected"
                                                        @change="toggleAll($event)" />
                                                </div>
                                            </th>
                                            <th v-for="col in header" :key="col.key"
                                                class="text-secondary text-uppercase fw-bold text-center">
                                                {{ col.label }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="!branch?.data.length">
                                            <td :colspan="header.length + 1" class="text-center">
                                                <div class="text-muted d-flex flex-column align-items-center">
                                                    <i class="far fa-folder-open fs-1 mb-3 opacity-25"></i>
                                                    <span>Tidak ada data ditemukan</span>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr v-for="(item, index) in branch?.data" :key="index"
                                            :class="{ 'row-selected': isSelected(item.branches_id) }">

                                            <td class="text-center" v-if="perm.includes('job.title.delete')">
                                                <div class="form-check d-flex justify-content-center">
                                                    <input type="checkbox" class="form-check-input custom-checkbox"
                                                        :name="item.branches_id" :id="item.branches_id"
                                                        :value="item.branches_id" v-model="selectedRow" />
                                                </div>
                                            </td>

                                            <td class="ps-3 text-muted">{{ index + 1 + (branch?.current_page
                                                - 1) * branch?.per_page }}</td>

                                            <td class="text-center ps-3">
                                                <span class="font-monospace bg-light border px-2 py-1 rounded text-dark"
                                                    v-html="highlight(item.branch_code, filters.keyword)">
                                                </span>
                                            </td>
                                            <td class="ps-3">
                                                <div class="fw-bold text-dark text-capitalize"
                                                    v-html="highlight(item.name, filters.keyword)"></div>
                                            </td>
                                            <td class="text-start phone" >
                                                <ul class="list-unstyled lh-2" style="font-size: 1.2rem;" >
                                                    <li v-for="item in item.branch_phone" :key="item.branch_phone_id">
                                                        <div class="badge text-dark bg-light font-monospace border" v-html="highlight(item.phone, filters.keyword)">
                                                        </div>
                                                    </li>
                                                </ul>
                                                <div class="text-center" v-if="!item.branch_phone.length">-</div>
                                            </td>

                                            <td class="text-start ps-3 " >
                                                <div v-html="item.address"></div>
                                            </td>

                                            <td class="text-center ps-3">
                                                <span class="badge py-2 px-4" :class="{
                                                    'text-bg-success': item.status === 'active',
                                                    'text-bg-danger': item.status === 'inactive'
                                                }">
                                                    {{ item.status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                                                </span>

                                            </td>
                                            <td class="ps-3">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="avatar-circle bg-light text-primary fw-bold">
                                                        {{ item.creator?.name ?
                                                            item.creator.name.substring(0, 2).toUpperCase() : '??' }}
                                                    </div>
                                                    <span>{{ item.creator?.name ?? '-' }}</span>
                                                </div>
                                            </td>

                                            <td class="ps-3 small text-muted">{{ daysTranslate(item.created_at) }}
                                            </td>
                                            <td class="ps-3 small text-muted">{{ daysTranslate(item.updated_at) }}
                                            </td>

                                            <td class="text-center">
                                                <div class="dropdown dropstart">
                                                    <button class="btn btn-light btn-icon shadow-sm" type="button"
                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="fas fa-ellipsis-v text-muted"></i>
                                                    </button>
                                                    <ul
                                                        class="dropdown-menu dropdown-menu-end border-0 shadow-lg p-2 rounded-3">
                                                        <li v-if="perm.includes('branches.edit')">
                                                            <button @click.prevent="edit(item.branches_id)"
                                                                class="dropdown-item rounded-2 py-2 mb-1 d-flex align-items-center">
                                                                <i class="bi bi-pencil-square text-primary me-2"></i>
                                                                Edit Data
                                                            </button>
                                                        </li>
                                                        <li v-if="perm.includes('branches.delete')">
                                                            <button @click.prevent="deleted('branch.deleted', item)"
                                                                class="dropdown-item rounded-2 py-2 text-danger d-flex align-items-center">
                                                                <i class="bi bi-trash me-2"></i> Hapus Data
                                                            </button>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div
                            class="card-footer bg-white border-0 py-3 px-4 d-flex flex-column flex-md-row justify-content-between align-items-center">
                            <span class="text-muted mb-2 mb-md-0">
                                Menampilkan <strong>{{ props.branch?.from ?? 0 }}</strong> - <strong>{{
                                    props.branch?.to ?? 0 }}</strong> dari <strong>{{ props.branch?.total ?? 0
                                    }}</strong>
                            </span>
                            <pagination size="pagination-sm" :links="props.branch?.links" routeName="job_title"
                                :additionalQuery="{
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
.blur-area {
    transition: all 0.3s ease;
}

.blur-area.is-blurred {
    filter: blur(3px);
    pointer-events: none;
    user-select: none;
    opacity: 0.6;
}

/* Card Modern */
.card-modern {
    background: #ffffff;
    transition: all 0.3s ease;
}

/* Custom Table Styling */
.custom-table thead th {
    letter-spacing: 0.5px;
    background-color: #f8f9fa;
    /* Abu-abu sangat muda */
    border-bottom: 2px solid #e9ecef;
}

.custom-table tbody td {
    border-bottom: 1px solid #f1f3f5;
    padding-top: 1rem;
    padding-bottom: 1rem;
}

/* Row Hover Effect */
.custom-table tbody tr {
    transition: background-color 0.2s ease;
}

.custom-table tbody tr:hover {
    background-color: #f8faff;
    /* Biru sangat pudar saat hover */
}

/* Row Selected State */
.row-selected {
    background-color: #eff6ff !important;
    /* Biru muda jika checkbox dicentang */
}

/* Custom Checkbox Size */
.custom-checkbox {
    width: 1.1em;
    height: 1.1em;
    cursor: pointer;
}

/* Avatar Circle untuk kolom Creator */
.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
}

/* Tombol Icon Action (Titik tiga) */
.btn-icon {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #f1f1f1;
}

.btn-icon:hover {
    background-color: #e9ecef;
    color: #000;
}


/* Animation Utilities */
.animate-pop {
    animation: popIn 0.3s cubic-bezier(0.68, -0.55, 0.27, 1.55);
}

@keyframes popIn {
    0% {
        transform: scale(0);
        opacity: 0;
    }

    100% {
        transform: scale(1);
        opacity: 1;
    }
}

.fs-7 {
    font-size: 0.75rem;
}
</style>
