<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { debounce, has } from "lodash";
import { highlight, highlightForId } from "@/helpers/highlight";
import { formatText } from "@/helpers/formatText";
import { hasRole, hasPermission } from "@/composables/useAuth";
import moment from "moment";
moment.locale('id');

import { useConfirm } from "@/helpers/useConfirm.js"
const confirm = useConfirm(); // Memanggil fungsi confirm untuk alert delete

import ModalExport from "./ModalExport.vue";
import ModalProdukDetail from "./ModalProdukDetail.vue";

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
    condition: props.filters?.condition ?? null,
})

const isLoading = ref(false)
const liveSearch = debounce(() => {
    isLoading.value = true
    router.get(route("product"), filters, {
        preserveScroll: true,
        replace: true,
        preserveState: true,
        only: ["product", "filters", "branch", "category"],
        onFinish: () => isLoading.value = false
    });
}, 500);
const reset = () => {
    isLoading.value = true
    navigateTo("product.reset", {}, false);
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
const navigateTo = (routeName, params = {}, message = "Sedang membuka...") => {
    if (message) loaderActive.value?.show(message);
    router.get(route(routeName, params), {}, {
        onFinish: () => message && loaderActive.value?.hide(),
        preserveScroll: false,
        replace: true,
    });

}

const deleted = async (data) => {
    const setConfirm = await confirm.ask({
        title: 'Hapus',
        message: `Produk ${formatText(data.name)} akan dihapus. Data tidak dapat dikembalikan!`,
        confirmText: 'Ya, Hapus',
        variant: 'danger' // Memberikan warna merah pada tombol konfirmasi
    });

    if (setConfirm) {
        loaderActive.value?.show("Sedang menghapus data...");
        router.delete(route('product.deleted', data.product_id), {
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
        col: 'col-xl-12 col-md-12',
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
        col: 'col-xl-3 col-md-3 col-6',
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
        col: 'col-xl-3 col-md-3 col-6',
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
        col: 'col-xl-3 col-md-3 col-6',
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
        key: 'condition',
        label: 'Kondisi Produk',
        type: 'select',
        col: 'col-xl-3 col-md-3 col-6',
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
        label: "#",
        key: "__index",
        attrs: { class: "text-center align-middle", style: "width: 50px" }
    },
    {
        label: "ID Produk", // Label lebih profesional
        key: "product_id",
        attrs: { class: "text-start align-middle", style: "width: 120px" }
    },
    {
        label: "Informasi Produk", // Gabungan Gambar + Nama + Link
        key: "name",
        attrs: { class: "ps-4 align-middle" } // Hapus text-center agar rapi rata kiri
    },
    {
        label: "Kategori",
        key: "category",
        attrs: { class: "d-none d-xl-table-cell text-center align-middle" }
    },
    {
        label: "Kondisi",
        key: "item_condition",
        attrs: { class: "text-center align-middle" }
    },
    {
        label: "", // Aksi tidak butuh label teks
        key: "actions",
        visible: hasRole(['admin', 'developer']),
        attrs: { class: "text-end pe-4 align-middle", style: "width: 100px" }
    },
];

const modals = reactive({
    export: false,
    detailInfo: false
});

const selectedData = ref(null);
const openModal = (type, data) => {
    selectedData.value = data;
    if (type === 'export') modals.export = true;
    if (type === 'detail_info') modals.detailInfo = true;
}
// end modal untuk tampilkan export to

const download = async (format) => {
    // Cek apakah melebihi batas maksimal
    if (selectedRow.value.length > 500) {
        return await confirm.ask({
            title: 'Perhatian',
            message: 'Data terlalu banyak untuk format ' + format.toUpperCase() + ' (>500). Silakan kurangi data yang akan diexport.',
            cancelText: 'Mengerti', // Ubah teks tombol tutup
            showButtonConfirm: false,
            variant: 'warning' // Gunakan warna kuning/orange untuk warning
        });
    }

    // Cek apakah ada data yang dipilih
    if (!selectedRow.value.length) {
        return await confirm.ask({
            title: 'Perhatian',
            message: 'Silakan pilih minimal satu data untuk diexport.',
            cancelText: 'Mengerti', // Ubah teks tombol tutup
            showButtonConfirm: false,
            variant: 'warning' // Gunakan warna kuning/orange untuk warning
        });
    }

    // Siapkan URL dan dxport data yang terpilih
    const url = route('product.export', {
        format: format,
        all_id: selectedRow.value.length > 0 ? selectedRow.value : null
    });

    // Buka di tab baru
    window.open(url, '_blank');
}


const toolbarActions = computed(() => [

    {
        label: `Hapus (${selectedRow.value.length})`,
        icon: 'fas fa-trash-alt',
        iconColor: 'text-danger', // Warna ikon spesifik
        labelColor: 'text-danger',
        disabled: !selectedRow.value.length > 0,
        show: hasRole(['admin', 'developer']),
        click: () => deleteSelected()
    },
    // {
    //     label: `PDF (${selectedRow.value.length})`,
    //     icon: 'fas fa-file-pdf',
    //     iconColor: 'text-danger',
    //     labelColor: 'text-danger',
    //     disabled: !selectedRow.value.length > 0,
    //     show: hasPermission('product.export') && hasRole(['admin', 'developer']),
    //     click: () => download('pdf')
    // },
    // {
    //     label: `Excel (${selectedRow.value.length})`,
    //     icon: 'fas fa-file-excel',
    //     iconColor: 'text-success',
    //     labelColor: 'text-success',
    //     disabled: !selectedRow.value.length > 0,
    //     show: hasPermission('product.export') && hasRole(['admin', 'developer']),
    //     click: () => download('excel')
    // },
    {
        label: 'Produk Baru',
        icon: 'fas fa-plus-circle',
        isPrimary: true, // Prioritas Utama
        show: hasRole(['admin', 'developer']),
        click: () => navigateTo("product.create")
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

    <Head title="Halaman Daftar Produk" />
    <app-layout>
        <template #content>
            <loader-page ref="loaderActive" />
            <bread-crumbs :home="false" icon="fas fa-box-open" title="Daftar Produk"
                :items="[{ text: 'Daftar Produk' }]" />
            <callout />

            <div v-if="!props.product.total"
                class="alert alert-info border-0 shadow-sm rounded-4 d-flex align-items-start p-3 mb-4">
                <i class="fas fa-map-marker-alt fa-lg me-3 mt-1"></i>

                <div>
                    <h6 class="fw-bold mb-1">Lokasi Tidak Terjangkau</h6>
                    <p class="small mb-0 opacity-75">
                        Produk belum tersedia di lokasimu.
                        <strong>Hubungi Admin</strong>
                        untuk dapat menyesuaikan produk berdasarkan wilayahmu.
                    </p>
                </div>
            </div>

            <div class="row pb-5">

                <div class="col-12">
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

                            <action-toolbar :actions="toolbarActions" />

                        </div>

                        <div class="card-body p-0 position-relative">
                            <base-table :markAll="hasRole(['admin', 'developer'])" :loader="isLoading"
                                loaderText="Sedang memuat data..." :headers="header" :items="product?.data"
                                row-key="product_id" @update:selected="(val) => selectedRow = val">

                                <template #empty>
                                    <div class="bg-light rounded-circle d-inline-flex p-4 mb-3">
                                        <i class="fas fa-box-open text-muted opacity-50 fs-1"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark">Tidak ada produk ditemukan</h6>
                                    <p class="text-muted small">Ubah filter pencarian Anda.</p>
                                </template>

                                <template #row="{ item, index }">
                                    <td class="text-center text-muted fw-medium">
                                        {{ index + 1 + (product?.current_page - 1) * product?.per_page }}
                                    </td>

                                    <td>
                                        <div
                                            class="bg-light border rounded px-2 py-1 d-inline-block font-monospace text-secondary small">
                                            <span
                                                v-html="highlight(item.product_id.substr(0, 12).replace(/-/g, '').toUpperCase(), filters.keyword)"></span>
                                        </div>
                                    </td>

                                    <td class="ps-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="product-avatar me-3 position-relative">
                                                <img :src="resolveImage(item.image_link || item.image_path)"
                                                    class="rounded-3 shadow-sm object-fit-cover border" width="56"
                                                    height="56" alt="Product">
                                            </div>

                                            <div class="d-flex flex-column" style="max-width: 400px;">
                                                <div class="fw-bold text-dark mb-1 text-truncate" :title="item.name">
                                                    <span v-html="highlight(item.name ?? '-', filters.keyword)"></span>
                                                </div>

                                                <div class="d-flex align-items-center gap-1 small">
                                                    <button @click.prevent="openModal('detail_info', item)"
                                                        class="btn-link-custom text-secondary text-decoration-none d-flex align-items-center">
                                                        <i class="fas fa-eye me-1"></i> Detail
                                                    </button>

                                                    <span class="text-muted border-start ps-3" v-if="item.link"></span>

                                                    <a v-if="item.link" :href="item.link" target="_blank"
                                                        class="btn-link-custom text-primary text-decoration-none d-flex align-items-center">
                                                        <i class="fas fa-external-link-alt me-1"></i> Buka Link
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="d-none d-xl-table-cell text-center">
                                        <span
                                            class="badge bg-white border text-secondary fw-normal px-3 py-2 rounded-pill shadow-sm">
                                            {{ formatCategory(item.category) }}
                                        </span>
                                    </td>

                                    <td class="text-center">
                                        <span class="badge rounded-pill px-3 py-2 fw-semibold text-capitalize"
                                            :class="getConditionBadgeClass(item.item_condition)">
                                            {{ itemCondition(item.item_condition) }}
                                        </span>
                                    </td>

                                    <td class="text-end pe-4" v-if="hasRole(['admin', 'developer'])">
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
                                        ]" @edit="navigateTo('product.edit', item.product_id)"
                                            @delete="deleted(item)" />
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
                                        condition: filters.condition,
                                    }" />
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <!-- <ModalExport :product="product" :show="modals.export" @update:show="modals.export = $event"
                :branches="branch" :categories="category" /> -->
            <ModalProdukDetail :product="selectedData" :show="modals.detailInfo"
                @update:show="modals.detailInfo = $event" />
        </template>
    </app-layout>
</template>
<style scoped>
/* Card Modern */
.card-modern {
    background: #ffffff;
    transition: all 0.3s ease;
}

.card-modern:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
}

/* --- TYPOGRAPHY --- */
.fs-7 {
    font-size: 0.75rem;
}

.fs-9 {
    font-size: 0.75rem;
}

.fs-10 {
    font-size: 0.8rem;
}

.ls-1 {
    letter-spacing: 0.5px;
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

/* Font Monospace untuk ID */
.font-monospace {
    font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, Courier, monospace;
    font-size: 0.8rem;
    letter-spacing: -0.5px;
}

/* Custom Link Button (Detail/External) */
.btn-link-custom {
    font-size: 0.8rem;
    transition: color 0.2s;
    background: none;
    border: none;
    padding: 0;
    cursor: pointer;
}

.btn-link-custom:hover {
    text-decoration: underline !important;
    color: var(--bs-primary) !important;
}
</style>
