<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { debounce, has } from "lodash";
import { highlight } from "@/helpers/highlight";
import { formatText } from "@/helpers/formatText";
import { hasRole, hasPermission } from "@/composables/useAuth";
import moment from "moment";
moment.locale('id');

import { useConfirm } from "@/helpers/useConfirm.js"
import ModalExport from "./ModalExport.vue";
const confirm = useConfirm(); // Memanggil fungsi confirm untuk alert delete

const props = defineProps({
    product: Object,
    filters: Object,
    category: Array,
    branch: Array,
})
const filters = reactive({
    keyword: props.filters.keyword ?? "",
    category: props.filters.category ?? null,
    limit: props.filters.limit ?? null,
    order_by: props.filters.order_by ?? null,
    page: props.filters?.page ?? 1,
    branch: props.filters?.branch ?? null,
    status: props.filters?.status ?? null,
    discount_only: props.filters?.discount_only ?? null,
    condition: props.filters?.condition ?? null,
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
const reset = () => {
    isLoading.value = true
    router.get(route("product.reset"), {}, {
        preserveScroll: false,
        replace: true,
        onFinish: () => isLoading.value = false
    });
}
const branchOptions = computed(() => [
    { label: "Semua Cabang", value: null },
    ...props.branch.map(br => ({
        label: br.name,
        value: br.name,
    }))
]);




watch(
    () => [
        filters.keyword,
        filters.limit,
        filters.order_by,
        filters.category,
        filters.branch,
        filters.status,
        filters.discount_only,
        filters.condition,
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

const deleted = async (data) => {
    const setConfirm = await confirm.ask({
        title: 'Hapus',
        message: `Produk ${formatText(data.product.name)} akan dihapus. Data tidak dapat dikembalikan!`,
        confirmText: 'Ya, Hapus',
        variant: 'danger' // Memberikan warna merah pada tombol konfirmasi
    });

    if (setConfirm) {
        loaderActive.value?.show("Sedang menghapus data...");
        router.delete(route('product.deleted', data.product_price_id), {
            onFinish: () => loaderActive.value?.hide(),
            preserveScroll: false,
            replace: true
        });
    }
}

// end CRUD OPERATION

// MULTIPLE DELETE
const selectedRow = ref([]);
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
        router.post(route('product.destroy_all'), {
            ids: selectedRow.value
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
    const dateFormat = moment(dayValue).format('DD-MM-YYYY');
    return dateFormat === 'Invalid date' ? '00-00-0000' : dateFormat;
}
function itemCondition(val) {
    const value = {
        new: 'Baru',
        used: 'Bekas',
        demaged: 'Rusak',
        discontinued: 'Dihentikan',
        refurbished: 'Diperbaharui',
    }
    return value[val] ?? '-'
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


// Function untuk menentukan warna badge
const getConditionBadgeClass = (status) => {
    switch (status) {
        case 'new':
            return 'badge-soft-success'; // Hijau Segar
        case 'used':
            return 'badge-soft-primary'; // Biru Netral
        case 'refurbished':
            return 'badge-soft-warning'; // Orange/Kuning (Proses/Perbaikan)
        case 'damaged':
            return 'badge-soft-danger';  // Merah (Bahaya/Rusak)
        case 'discontinued':
            return 'badge-soft-dark';    // Abu Gelap (Mati/Stop)
        default:
            return 'badge-soft-secondary'; // Default Abu-abu
    }
}

const filterFields = computed(() => [
    {
        key: 'keyword',
        label: 'Pencarian',
        col: 'col-xl-5 col-md-12',
        type: 'search',
        icon: 'fas fa-search',
        autofocus: true,
        props: {
            placeholder: 'Masukan nama dan produk...',
            inputClass: 'border-start-0 ps-2 shadow-none',
            isValid: false,
        }
    },
    {
        key: 'category',
        label: 'Kategori',
        type: 'select',
        col: 'col-xl-3 col-md-4',
        icon: 'fas fa-tags',
        props: {
            selectClass: 'border-start-0 ps-2 shadow-none',
            isValid: false,
        },
        options: categories.value
    },
    {
        key: 'limit',
        label: 'Tampilkan',
        type: 'select',
        col: 'col-xl-2 col-md-4 col-6',
        icon: 'fas fa-list-ol',
        props: {
            selectClass: 'border-start-0 ps-2 shadow-none',
            isValid: false,
        },
        options: [
            { value: null, label: 'Pilih Batas' },
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
        col: 'col-xl-2 col-md-4 col-6',
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
        key: 'branch',
        label: 'Cabang',
        type: 'select',
        col: 'col-xl-3 col-md-6 col-6',
        icon: 'fas fa-dot-circle',
        roles: ['developer', 'admin'],
        props: {
            selectClass: 'border-start-0 ps-2 shadow-none',
            isValid: false,
        },
        options: branchOptions.value
    },
    {
        key: 'status',
        label: 'Status Publikasi',
        type: 'select',
        col: 'col-xl-3 col-md-6 col-6',
        icon: 'fas fa-dot-circle',
        roles: ['developer', 'admin'],
        props: {
            selectClass: 'border-start-0 ps-2 shadow-none',
            isValid: false,
        },
        options: [
            { value: null, label: 'Semua Publikasi' },
            { value: 'published', label: 'Published' },
            { value: 'draft', label: 'Draft' },
        ]
    },
    {
        key: 'discount_only',
        label: 'Tipe Harga',
        type: 'select',
        col: 'col-xl-3 col-md-6 col-6',
        icon: 'fas fa-dot-circle',
        props: {
            selectClass: 'border-start-0 ps-2 shadow-none',
            isValid: false,
        },
        options: [
            { value: null, label: 'Semua Harga' },
            { value: 'discount', label: 'Diskon' },
            { value: 'normal', label: 'Normal' },
        ]
    },
    {
        key: 'condition',
        label: 'Kondisi Produk',
        type: 'select',
        col: 'col-xl-3 col-md-6 col-6',
        icon: 'fas fa-dot-circle',
        props: {
            selectClass: 'border-start-0 ps-2 shadow-none',
            isValid: false,
        },
        options: [
            { value: null, label: 'Semua Kondisi' },
            { value: 'new', label: 'Baru' },
            { value: 'used', label: 'Bekas' },
            { value: 'refurbished', label: 'Diperbaiki' },
            { value: 'damaged', label: 'Rusak' },
            { value: 'discontinued', label: 'Dihentikan' },
        ]
    },
]);

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
        label: "Produk",
        key: "name",
        attrs: {
            class: "ps-4 text-center"
        }
    },
    {
        label: "Kategori",
        key: "category",
        attrs: {
            class: "d-none d-xl-table-cell text-center"
        }
    },
    {
        label: 'Cabang',
        key: "branches_id",
        attrs: {
            class: "text-center"
        }
    },
    {
        label: "Harga",
        key: "base_price",
        attrs: {
            class: "text-center"
        }
    },
    {
        label: "Tgl.Berlaku",
        key: "valid_from",
        attrs: {
            class: "text-center d-none d-xl-table-cell"
        }
    },
    {
        label: "Tgl.Berakhir",
        key: "valid_until",
        attrs: {
            class: "text-center d-none d-xl-table-cell"
        }
    },
    {
        label: "Tipe Harga",
        key: "price_type",
        attrs: {
            class: "text-center d-none d-xl-table-cell"
        }
    },
    {
        label: "Publikasi",
        key: "status",
        attrs: {
            class: "text-center d-none d-xl-table-cell"
        }
    },
    {
        label: "Aksi",
        key: "-",
        visible: hasRole(['admin', 'developer']),
        attrs: {
            class: "text-center"
        }
    },
];
const showModalExport = ref(false)
const openModalExport = () => {
    showModalExport.value = true
}
const toolbarActions = computed(() => [

    {
        label: `Hapus (${selectedRow.value.length})`,
        icon: 'fas fa-trash-alt',
        iconColor: 'text-danger', // Warna ikon spesifik
        labelColor: 'text-danger',
        disabled: !selectedRow.value.length > 0,
        show: hasRole(['admin', 'developer']),
        click: deleteSelected
    },
    {
        label: 'Unduh',
        icon: 'fas fa-download',
        iconColor: 'text-success', // Warna ikon spesifik
        show: hasRole(['admin', 'developer']),
        click: openModalExport
    },

    {
        label: 'Produk Baru',
        icon: 'fas fa-plus-circle',
        isPrimary: true, // Prioritas Utama
        show: hasRole(['admin', 'developer']),
        click: create
    },
    {
        label: 'Segarkan',
        icon: 'fas fa-redo-alt',
        iconColor: 'text-primary',
        loading: isLoading.value,
        click: reset
    }
]);
</script>
<template>

    <Head title="Halaman Daftar Produk" />
    <app-layout>
        <template #content>
            <loader-page ref="loaderActive" />
            <bread-crumbs :home="false" icon="fas fa-tags" title="Daftar Produk" :items="[{ text: 'Daftar Produk' }]" />
            <callout />

            <div class="row pb-5">

                <div class="col-12 mb-3">
                    <base-filters :roles="['admin', 'developer']" title="Filter Produk" v-model="filters"
                        :fields="filterFields" />
                </div>

                <div class="col-12">
                    <div class="card card-modern border-0 rounded-4 overflow-hidden">
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

                            <action-toolbar v-if="hasRole(['admin', 'developer'])" :actions="toolbarActions" />

                        </div>

                        <div class="card-body p-0 position-relative">
                            <base-table :markAll="hasRole(['admin', 'developer'])" :loader="isLoading"
                                loaderText="Sedang memuat data..." :headers="header" :items="product?.data"
                                row-key="product_price_id" @update:selected="(val) => selectedRow = val">

                                <template #empty>
                                    <div class="bg-light rounded-circle d-inline-flex p-4 mb-3">
                                        <i class="fas fa-box-open text-muted opacity-50 fs-1"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark">Tidak ada produk ditemukan</h6>
                                    <p class="text-muted small">Ubah filter pencarian Anda.</p>
                                </template>

                                <template #row="{ item, index }">

                                    <td class="ps-3 text-muted">{{ index + 1 + (product
                                        ?.current_page
                                        - 1) * product
                                            ?.per_page }}
                                    </td>
                                    <td class="ps-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-product me-3 shadow-sm rounded-3 overflow-hidden">
                                                <img :src="resolveImage(item.product?.image_link || item.product?.image_path)"
                                                    class="w-100 h-100 object-fit-cover" alt="Product">
                                            </div>

                                            <div style="max-width: 500px;">
                                                <div class="fw-bold text-dark mb-1 text-truncate"
                                                    :title="item.product?.name"
                                                    v-html="highlight(item.product?.name ?? '-', filters.keyword)">
                                                </div>
                                                <div class="small text-muted align-items-center d-flex">
                                                    <span
                                                        class="badge bg-light text-secondary border fw-normal rounded-5 me-2">ID:
                                                        <span v-html="highlight(item.product_price_id.substr(0,
                                                            8), filters.keyword)"></span>
                                                    </span>

                                                    <span class="badge border fw-bold rounded-pill me-2 px-3 py-1"
                                                        :class="getConditionBadgeClass(item.product?.item_condition)">
                                                        {{ itemCondition(item.product?.item_condition) }}
                                                    </span>

                                                    <a v-if="item.product?.link" :href="item.product?.link"
                                                        target="_blank"
                                                        class="text-primary text-decoration-none hover-underline">
                                                        <i class="fas fa-external-link-alt fs-10 me-1"></i>Link
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="d-none d-xl-table-cell">
                                        <span
                                            class="d-inline-flex bg-gradient align-items-center text-secondary fw-medium px-2 py-1 rounded-2 bg-light border small">
                                            <i class="fas fa-tag me-2 fs-10"></i> {{
                                                formatCategory(item.product?.category) }}
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
                                    <td class="text-center fw-semibold d-none d-xl-table-cell">
                                        {{ daysTranslate(item.valid_from) }}
                                    </td>
                                    <td class="text-center fw-semibold d-none d-xl-table-cell">
                                        {{ daysTranslate(item.valid_until) }}
                                    </td>
                                    <td class="text-center fw-semibold small text-muted d-none d-xl-table-cell">
                                        {{ item.price_type == 'discount' ? 'Diskon' : 'Normal' }}
                                    </td>

                                    <td class="text-center d-none d-xl-table-cell">
                                        <span class="badge rounded-pill px-3 py-2 fw-bold" :class="{
                                            'badge-soft-success': item.status === 'published',
                                            'badge-soft-secondary': item.status === 'draft'
                                        }">
                                            <i class="me-1"
                                                :class="item.status === 'published' ? 'fas fa-globe' : 'fas fa-lock'"></i>
                                            {{ item.status === 'published' ? 'Publish' : 'Draft' }}
                                        </span>
                                    </td>


                                    <td class="text-center" v-if="hasRole(['admin', 'developer'])">
                                        <dropdown-action :item="item" :actions="[
                                            {
                                                label: 'Ubah Produk',
                                                icon: 'bi bi-pencil-square fs-6',
                                                color_icon: 'success',
                                                action: 'edit',
                                                permission: 'product.edit'
                                            },
                                            {
                                                label: 'Hapus Produk',
                                                icon: 'bi bi-trash fs-6',
                                                color: 'danger',
                                                action: 'delete',
                                                permission: 'product.delete'
                                            }
                                        ]" @edit="edit(item.product_price_id)" @delete="deleted(item)" />
                                    </td>


                                </template>
                            </base-table>
                        </div>

                        <div class="card-footer bg-white border-top py-3 px-4 overflow-hidden">
                            <div
                                class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                                <span class="text-muted small">
                                    Menampilkan <strong>{{ props.product?.from ?? 0 }}</strong> - <strong>{{
                                        props.product?.to ?? 0 }}</strong>
                                    dari <strong>{{ props.product?.total ?? 0 }}</strong> data
                                </span>
                                <pagination size="pagination-sm" :links="props.product?.links" routeName="product"
                                    :additionalQuery="{
                                        limit: filters.limit,
                                        order_by: filters.order_by,
                                        keyword: filters.keyword,
                                        category: filters.category,
                                        branch: filters.branch,
                                        status: filters.status,
                                        discount_only: filters.discount_only,
                                        condition: filters.condition,
                                    }" />
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <ModalExport :show="showModalExport" @update:show="showModalExport = $event" :branches="branch"
                :categories="category" />
        </template>
    </app-layout>
</template>
<style scoped>
.hover-scale {
    transition: transform 0.2s;
}

.hover-scale:hover {
    transform: scale(1.05);
}

/* Card Modern */
.card-modern {
    background: #ffffff;
    transition: all 0.3s ease;
}

.card-modern:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
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



/* Label Styling (Konsisten dengan halaman lain) */
.form-label-custom {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    color: #8898aa;
    /* Warna abu-abu soft */
    text-transform: uppercase;
}



/* --- PRODUCT AVATAR --- */
.avatar-product {
    width: 50px;
    height: 50px;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
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

/* Base Badge Style */
.badge {
    font-size: 0.65rem;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
}

/* 1. BARU (NEW) - Emerald Green */
.badge-soft-success {
    background-color: #d1fae5;
    /* Hijau Mint Muda */
    color: #065f46;
    /* Hijau Hutan Gelap */
    border-color: #a7f3d0 !important;
}

/* 2. BEKAS (USED) - Sky Blue */
.badge-soft-primary {
    background-color: #e0f2fe;
    /* Biru Langit Muda */
    color: #0369a1;
    /* Biru Laut Gelap */
    border-color: #bae6fd !important;
}

/* 3. DIPERBAIKI (REFURBISHED) - Amber/Orange */
.badge-soft-warning {
    background-color: #fef3c7;
    /* Kuning Krem */
    color: #92400e;
    /* Coklat Orange */
    border-color: #fde68a !important;
}

/* 4. RUSAK (DAMAGED) - Rose Red */
.badge-soft-danger {
    background-color: #ffe4e6;
    /* Merah Muda Pucat */
    color: #be123c;
    /* Merah Rose Gelap */
    border-color: #fecdd3 !important;
}

/* 5. DIHENTIKAN (DISCONTINUED) - Slate Gray */
.badge-soft-dark {
    background-color: #f1f5f9;
    /* Abu-abu Kebiruan Pucat */
    color: #475569;
    /* Abu-abu Slate Gelap */
    border-color: #cbd5e1 !important;
}
</style>
