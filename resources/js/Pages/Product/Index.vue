<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { debounce } from "lodash";
import { swalAlert, swalConfirmDelete } from "@/helpers/swalHelpers";
import { highlight } from "@/helpers/highlight";
import { formatText } from "@/helpers/formatText";
import moment from "moment";
import Swal from "sweetalert2";
moment.locale('id');


const page = usePage();
const message = computed(() => page.props.flash.message || "");
const props = defineProps({
    product: Object,
    filters: Object,
    category: Array,
})
console.log(props.product.data);
// cek permission
const perm = page.props.auth.user.permissions

const filters = reactive({
    keyword: props.filters.keyword ?? "",
    category: props.filters.category ?? null,
    limit: props.filters.limit ?? null,
    order_by: props.filters.order_by ?? null,
    page: props.filters?.page ?? 1,
})

const isLoading = ref(false)
const liveSearch = debounce(() => {
    isLoading.value = true
    router.get(route("product"), filters, {
        preserveScroll: true,
        replace: true,
        preserveState: true,
        only: ["product", "filters"], // optional: lebih hemat bandwidth jika kamu pakai Inertia partial reload
        onFinish: () => isLoading.value = false
    });
}, 500);


const header = [
    { label: "No", key: "__index" },
    { label: "Nama Produk", key: "name" },
    { label: "Kategori", key: "category" },
    { label: "Harga Asli", key: "price_original" },
    { label: "Harga Diskon", key: "price_discount" },
    { label: "Status", key: "status" },
    { label: "Aksi", key: "-" },
];
watch(
    () => [
        filters.keyword,
        filters.limit,
        filters.order_by,
        filters.category
    ],
    () => {
        filters.page = 1;
        liveSearch();
    }
);


// CRUD OPERATION
const loaderActive = ref(null)
const create = () => {
    loaderActive.value?.show("Memproses...");
    router.get(route("product.create"), {}, {
        onFinish: () => {
            loaderActive.value?.hide()
        }
    });
}

const edit = (id) => {
    loaderActive.value?.show("Sedang memuat data...");
    router.get(route("product.edit", id), {}, {
        onFinish: () => loaderActive.value?.hide()
    });
}

const deleted = (data) => {
    swalConfirmDelete({
        title: 'Hapus',
        text: `Produk ${data.name} akan dihapus. Data tidak dapat dikembalikan!`,
        confirmText: 'Ya, Hapus!',
        onConfirm: () => {
            loaderActive.value?.show("Sedang memuat data...");
            router.delete(route('product.deleted', data.product_price_id), {
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
    const rows = props.product?.data ?? [];
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
            router.post(route('product.destroy_all'), { all_id: selectedRow.value }, {
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
        selectedRow.value = props.product?.data.map(r => r.product_price_id);
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
    const dateFormat = moment(dayValue).format('DD/MM/YYYY');
    return dateFormat === 'Invalid date' ? '00/00/0000' : dateFormat;
}
function formatCurrency(value) {
    if (!value) return "Rp 0";
    return new Intl.NumberFormat('id-ID', {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value)
}
const resolveImage = (path) => {
    // 1. Jika path kosong/null, pakai placeholder
    if (!path) return 'https://ui-avatars.com/api/?name=??';

    // 2. Jika path dimulai dengan 'http', berarti ini link eksternal (Scrape)
    if (path.startsWith('http')) {
        return path;
    }

    return `/storage/${path}`;
};

function formatCategory(cat) {
    return cat
        .split('/')                      // pecah sub kategori
        .map(part => part.replace(/-/g, ' '))  // ganti - dengan spasi
        .map(part => part.replace(/\b\w/g, char => char.toUpperCase())) // kapital
        .join(' - ');                    // gabungkan dengan pemisah cantik
}
const categories = computed(() => [
    { label: "Semua Kategori", value: null },
    ...props.category.map(cat => ({
        label: formatCategory(cat.category),
        value: cat.category,
    }))

]);
const inputRef = ref(null)
onMounted(() => {
    inputRef.value.focus()
})
</script>
<template>

    <Head title="Halaman Daftar Produk" />
    <app-layout>
        <template #content>
            <loader-page ref="loaderActive" />
            <bread-crumbs :home="false" icon="fas fa-tags" title="Daftar Produk" :items="[{ text: 'Daftar Produk' }]" />
            <callout type="success" :duration="10" :message="message" />

            <div class="row pb-5">

                <div class="col-12 mb-3">
                    <div class="card border-0 shadow-sm rounded-4 mb-4 filter-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-circle me-2">
                                    <i class="fas fa-sliders-h fs-6"></i>
                                </div>
                                <h5 class="fw-bold text-dark mb-0 text-uppercase ls-1">Filter Produk</h5>
                            </div>
                            <div class="row g-3 align-items-end">
                                <div class="col-xl-5 col-md-12">
                                    <input-label class="form-label-custom mb-1" for="keyword" value="KATA KUNCI" />
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 text-muted ps-3">
                                            <i class="fas fa-search"></i>
                                        </span>
                                        <text-input ref="inputRef" placeholder="Cari produk..." name="keyword"
                                            v-model="filters.keyword" type="text" :is-valid="false"
                                            input-class="border-start-0 ps-2 shadow-none" />
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-4">
                                    <input-label class="form-label-custom mb-1" for="category" value="KATEGORI" />
                                    <div class="input-group">
                                        <span class="input-group-text border-end-0 text-muted ps-3">
                                            <i class="fas fa-tags"></i>
                                        </span>
                                        <select-input text="--Pilih Kategori--" :is-valid="false"
                                            v-model="filters.category" name="category" :options="categories"
                                            select-class="border-start-0 ps-2 shadow-none" />
                                    </div>
                                </div>
                                <div class="col-xl-2 col-md-4 col-6">
                                    <input-label class="form-label-custom mb-1" for="limit" value="TAMPILKAN" />
                                    <div class="input-group">
                                        <span class="input-group-text border-end-0 text-muted ps-3">
                                            <i class="fas fa-list-ol"></i>
                                        </span>
                                        <select-input :is-valid="false" v-model="filters.limit" name="limit" :options="[
                                            { value: null, label: 'Pilih Batas' },
                                            { value: 10, label: '10 Baris' },
                                            { value: 20, label: '20 Baris' },
                                            { value: 50, label: '50 Baris' },
                                            { value: 100, label: '100 Baris' },
                                        ]" select-class="border-start-0 ps-2 shadow-none" />
                                    </div>
                                </div>
                                <div class="col-xl-2 col-md-4 col-6">
                                    <input-label class="form-label-custom mb-1" for="order_by" value="URUTAN" />
                                    <div class="input-group">
                                        <span class="input-group-text border-end-0 text-muted ps-3">
                                            <i class="fas fa-sort"></i>
                                        </span>
                                        <select-input :is-valid="false" v-model="filters.order_by" name="order_by"
                                            :options="[
                                                { value: null, label: 'Pilih Urutan' },
                                                { value: 'desc', label: 'Terbaru' },
                                                { value: 'asc', label: 'Terlama' },
                                            ]" select-class="border-start-0 ps-2 shadow-none" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card card-modern border-0 shadow rounded-4 overflow-hidden">

                        <div
                            class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3 me-3">
                                    <i class="fas fa-boxes fs-5"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0 text-dark">Data Produk</h5>
                                    <p class="text-muted small mb-0">Total {{ props.product?.total ?? 0 }} produk
                                        tersedia</p>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <transition name="pop">
                                    <button v-if="selectedRow.length > 0" @click="deleteSelected" type="button"
                                        class="btn btn-danger px-3 shadow-sm d-flex align-items-center">
                                        <i class="fas fa-trash-alt me-2"></i> Hapus ({{ selectedRow.length }})
                                    </button>
                                </transition>

                                <button type="button" @click.prevent="create"
                                    class="btn btn-primary px-4 shadow-sm d-flex align-items-center fw-bold hover-lift">
                                    <i class="fas fa-plus me-2"></i> Produk Baru
                                </button>
                            </div>
                        </div>

                        <div v-if="isLoading"
                            class="position-absolute w-100 h-100 bg-white opacity-75 d-flex align-items-center justify-content-center"
                            style="z-index: 10;">
                            <div class="text-center">
                                <div class="spinner-border text-primary mb-2" role="status"></div>
                                <p class="fw-bold text-dark">Sedang memuat data...</p>
                            </div>
                        </div>

                        <div class="card-body p-0" :class="['blur-area', isLoading ? 'is-blurred' : '']">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 custom-table text-nowrap ">
                                    <thead class="bg-light border-bottom">
                                        <tr>
                                            <th width="50" class="text-center">
                                                <div class="form-check d-flex justify-content-center">
                                                    <input :disabled="!product?.data.length" type="checkbox"
                                                        class="form-check-input custom-checkbox pointer"
                                                        :checked="isAllSelected" @change="toggleAll($event)" />
                                                </div>
                                            </th>
                                            <th class="text-secondary text-uppercase fw-bold ps-4">Produk</th>
                                            <th class="text-secondary text-uppercase fw-bold">Kategori</th>
                                            <th class="text-secondary text-uppercase fw-bold">Cabang</th>
                                            <th class="text-secondary text-uppercase fw-bold">Harga</th>
                                            <th class="text-secondary text-uppercase fw-bold text-center">
                                                Tgl.Berlaku</th>
                                            <th class="text-secondary text-uppercase fw-bold text-center">
                                                Tgl.Berakhir</th>
                                            <th class="text-secondary text-uppercase fw-bold text-center">
                                                Tipe Harga</th>
                                            <th class="text-secondary text-uppercase fw-bold text-center">
                                                Status Publish</th>
                                            <th class="text-secondary text-uppercase fw-bold text-center">
                                                Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="!product?.data.length">
                                            <td colspan="6" class="text-center py-5">
                                                <div class="empty-state">
                                                    <div class="bg-light rounded-circle d-inline-flex p-4 mb-3">
                                                        <i class="fas fa-box-open text-muted opacity-50"></i>
                                                    </div>
                                                    <h6 class="fw-bold text-dark">Tidak ada produk ditemukan</h6>
                                                    <p class="text-muted small">Coba ubah filter pencarian Anda.</p>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr v-for="(item, index) in product?.data" :key="index"
                                            :class="{ 'row-selected': isSelected(item.product_price_id) }"
                                            class="transition-colors">

                                            <td class="text-center">
                                                <div class="form-check d-flex justify-content-center">
                                                    <input type="checkbox"
                                                        class="form-check-input custom-checkbox pointer"
                                                        :value="item.product_price_id" v-model="selectedRow" />
                                                </div>
                                            </td>

                                            <td class="ps-4 py-3">
                                                <div class="d-flex align-items-center">
                                                    <div
                                                        class="avatar-product me-3 shadow-sm rounded-3 overflow-hidden group-hover-img">
                                                        <img :src="resolveImage(item.product.image_link)"
                                                            class="w-100 h-100 object-fit-cover" alt="Product">
                                                    </div>

                                                    <div style="max-width: 250px;">
                                                        <div class="fw-bold text-dark text-truncate mb-1"
                                                            :title="item.product.name"
                                                            v-html="highlight(item.product.name ?? '-', filters.keyword)">
                                                        </div>
                                                        <div class="small text-muted d-flex align-items-center gap-2">
                                                            <span
                                                                class="badge bg-light text-secondary border fw-normal">ID:
                                                                {{ item.product_price_id.substr(0, 8) }}</span>
                                                            <a v-if="item.product.link" :href="item.product.link"
                                                                target="_blank"
                                                                class="text-primary text-decoration-none hover-underline">
                                                                <i class="fas fa-external-link-alt fs-10 me-1"></i>Link
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span
                                                    class="d-inline-flex bg-gradient align-items-center text-secondary fw-medium px-2 py-1 rounded-2 bg-light border small">
                                                    <i class="fas fa-tag me-2 fs-10"></i> {{
                                                        formatCategory(item.product.category) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span
                                                    class="d-inline-flex text-white align-items-center fw-medium px-2 py-1 bg-opacity-75 rounded-2 bg-success border small text-capitalize">
                                                    <i class="fas fa-map-marker-alt me-2 fs-10"></i>
                                                    {{ item.branch?.name ?? 'Jabodetabek' }}
                                                </span>
                                            </td>

                                            <td>
                                                <div class="d-flex flex-column">
                                                    <template v-if="item.discount_price">
                                                        <span class="fw-bold text-dark">{{
                                                            formatCurrency(item.discount_price) }}</span>
                                                        <small class="text-decoration-line-through fs-10 text-danger">{{
                                                            formatCurrency(item.base_price) }}</small>
                                                    </template>
                                                    <template v-else>
                                                        <span class="fw-bold text-dark">{{
                                                            formatCurrency(item.base_price) }}</span>
                                                    </template>
                                                </div>
                                            </td>
                                            <td class="text-center fw-semibold small">
                                                {{ daysTranslate(item.valid_from) }}
                                            </td>
                                            <td class="text-center fw-semibold small">
                                                {{ daysTranslate(item.valid_until) }}
                                            </td>
                                            <td class="text-center fw-semibold small text-muted">
                                                {{ item.price_type == 'discount' ? 'Diskon' : 'Normal' }}
                                            </td>

                                            <td class="text-center">
                                                <span class="badge rounded-pill px-3 py-2 fw-bold" :class="{
                                                    'badge-soft-success': item.product.status === 'published',
                                                    'badge-soft-secondary': item.product.status === 'draft'
                                                }">
                                                    <i class="me-1"
                                                        :class="item.product.status === 'published' ? 'fas fa-globe' : 'fas fa-lock'"></i>
                                                    {{ item.product.status === 'published' ? 'Publish' : 'Draft' }}
                                                </span>
                                            </td>


                                            <td class="text-center ps-4">
                                                <div class="dropdown dropstart">
                                                    <button
                                                        class="btn btn-icon btn-lg btn-light rounded-circle shadow-sm"
                                                        type="button" data-bs-toggle="dropdown">
                                                        <i class="fas fa-ellipsis-h text-muted"></i>
                                                    </button>
                                                    <ul
                                                        class="dropdown-menu dropdown-menu-end border-0 shadow-lg p-2 rounded-3">
                                                        <li>
                                                            <button @click.prevent="edit(item.product_price_id)"
                                                                class="dropdown-item rounded-2 py-2 d-flex align-items-center">
                                                                <div
                                                                    class="icon-box-xs bg-primary bg-opacity-10 text-primary rounded-1 me-2">
                                                                    <i class="fas fa-pen fs-10"></i>
                                                                </div>
                                                                <span>Ubah Produk</span>
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <hr class="dropdown-divider my-1">
                                                        </li>
                                                        <li>
                                                            <button @click.prevent="deleted(item)"
                                                                class="dropdown-item rounded-2 py-2 d-flex align-items-center text-danger">
                                                                <div
                                                                    class="icon-box-xs bg-danger bg-opacity-10 text-danger rounded-1 me-2">
                                                                    <i class="fas fa-trash fs-10"></i>
                                                                </div>
                                                                <span>Hapus Produk</span>
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

                        <div class="card-footer bg-white border-top py-3 px-4 overflow-hidden">
                            <div
                                class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                                <span class="text-muted small">
                                    Menampilkan <strong>{{ props.product?.from ?? 0 }}</strong> - <strong>{{
                                        props.product?.to ?? 0 }}</strong>
                                    dari <strong>{{ props.product?.total ?? 0 }}</strong> data
                                </span>
                                <pagination :links="props.product?.links" routeName="product" :additionalQuery="{
                                    limit: filters.limit,
                                    order_by: filters.order_by,
                                    keyword: filters.keyword,
                                    category: filters.category
                                }" />
                            </div>
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
    background-color: #f4f4f5;
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
    /* Biru muda jika checkbox dicentang */
    background-color: #d7e0ec !important;
}

/* Custom Checkbox Size */
.custom-checkbox {
    width: 1.1em;
    height: 1.1em;
    cursor: pointer;
}

/* Avatar Circle untuk kolom Creator */
.avatar-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
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

/* Card Filter */
.filter-card {
    background: #ffffff;
    transition: box-shadow 0.3s ease;
}

.filter-card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
}

/* Target input/select komponen Vue kamu */
.filter-card input,
.filter-card select {
    border-left: none;
    /* Hilangkan border kiri input */
    color: #495057;
    font-weight: 500;
}

/* Efek Focus: Border input group menyala */
.filter-card .input-group:focus-within .input-group-text,
.filter-card .input-group:focus-within input,
.filter-card .input-group:focus-within select {
    border-color: #86b7fe;
    /* Warna focus bootstrap */
    box-shadow: none;
    /* Hilangkan shadow default input, kita pakai border saja */
}

.filter-card .input-group:focus-within {
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    border-radius: 0.375rem;
}

/* Label Styling (Konsisten dengan halaman lain) */
.form-label-custom {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    color: #8898aa;
    /* Warna abu-abu soft */
    text-transform: uppercase;
}



/* --- TABLE STYLING --- */
.custom-table th {
    font-weight: 700;
    color: #64748b;
    border-bottom: 2px solid #f1f5f9;
}

.custom-table td {
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
    padding-top: 1rem;
    padding-bottom: 1rem;
}

/* Hover Row Effect */
.table-hover tbody tr:hover {
    background-color: #f8fafc;
    /* Warna abu-abu sangat muda */
}

/* --- PRODUCT AVATAR --- */
.avatar-product {
    width: 50px;
    height: 50px;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
}

.img-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.4);
    opacity: 0;
    transition: opacity 0.2s;
}

.group-hover-img:hover .img-overlay {
    opacity: 1;
}

/* --- SOFT BADGES --- */
.badge-soft-success {
    background-color: rgba(25, 135, 84, 0.1);
    color: #198754;
}

.badge-soft-secondary {
    background-color: rgba(108, 117, 125, 0.1);
    color: #6c757d;
}

.dot-indicator {
    display: inline-block;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    margin-bottom: 1px;
}

/* --- TYPOGRAPHY --- */
.fs-9 {
    font-size: 0.75rem;
}

.fs-10 {
    font-size: 0.8rem;
}

.ls-1 {
    letter-spacing: 0.5px;
}

.hover-underline:hover {
    text-decoration: underline !important;
}

/* --- DROPDOWN & ICON --- */
.btn-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.btn-icon:hover {
    background-color: #e9ecef;
    transform: rotate(90deg);
}

.icon-box-xs {
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* --- ANIMATION --- */
.hover-lift {
    transition: transform 0.2s;
}

.hover-lift:hover {
    transform: translateY(-2px);
}

/* Transition for Bulk Delete Button */
.pop-enter-active,
.pop-leave-active {
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.pop-enter-from,
.pop-leave-to {
    opacity: 0;
    transform: scale(0.8);
}
</style>
