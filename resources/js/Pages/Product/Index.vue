<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { Head, router, usePage } from '@inertiajs/vue3';
import formatCurrency from "@/helpers/formatCurrency";
import { debounce, get } from "lodash";
import { highlight } from "@/helpers/highlight";
import { swalAlert, swalConfirmDelete } from "@/helpers/swalHelpers";
import { formatText } from "@/helpers/formatText";

import axios from "axios";
const page = usePage();
const message = computed(() => {
    return page.props.flash.message || page.props.flash.error
});

const props = defineProps({
    product: Object,
    filters: Object,
    category: Array,
})
const filters = reactive({
    keyword: props.filters.keyword ?? "",
    category: props.filters.category ?? "",
    limit: props.filters.limit ?? 10,
    order_by: props.filters.order_by ?? "desc",
    page: props.filters?.page ?? 1,
})
const liveSearch = debounce(() => {
    router.get(route("product"), filters, {
        preserveScroll: true,
        replace: true,
        preserveState: true,
        only: ["product", "filters"], // optional: lebih hemat bandwidth jika kamu pakai Inertia partial reload
    });
}, 1000);

watch([
    () => filters.keyword,
    () => filters.limit,
    () => filters.order_by,
    () => filters.category
], () => {
    filters.page = 1;
    liveSearch()
}, {
    deep: true
})
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

// =========Tampilkan Modal========== //
const showModal = ref(false);
const getById = ref(null);
const imageGallery = ref([]);
const description = ref('');
const mainImage = ref('https://via.placeholder.com/800')
function openModal(id) {
    showModal.value = true
    getById.value = id;
}

// tutup modal SETELAH Bootstrap selesai animasi
function closeModal() {
    showModal.value = false
}
watch(() => getById.value,
    async (newId) => {
        if (!newId) {
            getById.value = null;
            return;
        }
        const { data } = await axios.get(route('product.show', newId));
        if (data.status) {
            imageGallery.value = data.galleryImages

            description.value = data.description;
        }
    })
// =========Batas Fungsi untuk Tampilkan Modal========== //


// 🔥 Jika imageGallery berubah → set gambar pertama
watch(imageGallery, (newVal) => {
    mainImage.value = newVal[0];
});
// ganti gambar utama
const setMainImage = (img) => {
    mainImage.value = img
}

const inputRef = ref(null);
onMounted(() => {
    inputRef.value.focus();
})

const resolveImage = (path) => {
    // 1. Jika path kosong/null, pakai placeholder
    if (!path) return 'https://via.placeholder.com/100?text=No+Img';

    // 2. Jika path dimulai dengan 'http', berarti ini link eksternal (Scrape)
    if (path.startsWith('https')) {
        return path;
    }

    return `/${path}`;
};

// Crud Operation
const loaderActive = ref(null)

const deleted = (nameRoute, data) => {
    swalConfirmDelete({
        icon: 'warning',
        title: 'Hapus',
        text: `Kamu ingin menghapus Produk ${formatText(data.name)} ?`,
        confirmText: 'Ya, Hapus!',

        onConfirm: () => {
            loaderActive.value?.show("Sedang memuat data...");
            router.delete(route(nameRoute, data.product_id), {
                onFinish: () => loaderActive.value?.hide(),
                preserveScroll: false,
                replace: true,
                preserveState: true
            });
        },
    })
}
</script>
<template>

    <Head title="Halaman Produk" />
    <app-layout>
        <template #content>
            <loader-page ref="loaderActive" />
            <bread-crumbs :home="false" icon="fas fa-tags" title="DAFTAR PRODUK" :items="[{ text: 'Daftar Produk' }]" />
            <alert :variant="page.props.flash.message ? 'success' : 'danger'" :duration="10" :message="message" />

            <div class="row">
                <div class="col-12">

                    <div class="card border-0 shadow-sm rounded-4 mb-4 filter-card">
                        <div class="card-body p-4">

                            <div class="d-flex align-items-center mb-3">
                                <div class="icon-box-sm bg-primary bg-opacity-10 text-primary rounded-circle me-2">
                                    <i class="fas fa-sliders-h fs-6"></i>
                                </div>
                                <h6 class="fw-bold text-dark mb-0 text-uppercase ls-1">Filter Produk</h6>
                            </div>

                            <div class="row g-3 align-items-end">
                                <div class="col-xl-5 col-md-12">
                                    <input-label class="form-label-custom mb-1" for="keyword" value="KATA KUNCI" />
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 text-muted ps-3">
                                            <i class="fas fa-search"></i>
                                        </span>
                                        <text-input ref="inputRef" placeholder="Cari nama produk..." name="keyword"
                                            v-model="filters.keyword" type="text" :is-valid="false"
                                            input-class="border-start-0 ps-2 shadow-none" />
                                    </div>
                                </div>

                                <div class="col-xl-3 col-md-4">
                                    <input-label class="form-label-custom mb-1" for="category" value="KATEGORI" />
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 text-muted ps-3">
                                            <i class="fas fa-tags"></i>
                                        </span>
                                        <select-input text="Semua Kategori" :is-valid="false" v-model="filters.category"
                                            name="category" :options="categories"
                                            select-class="border-start-0 ps-2 shadow-none" />
                                    </div>
                                </div>

                                <div class="col-xl-2 col-md-4 col-6">
                                    <input-label class="form-label-custom mb-1" for="limit" value="TAMPILKAN" />
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 text-muted ps-3">
                                            <i class="fas fa-list-ol"></i>
                                        </span>
                                        <select-input :is-valid="false" v-model="filters.limit" name="limit" :options="[
                                            { value: 10, label: '10 Item' },
                                            { value: 20, label: '20 Item' },
                                            { value: 50, label: '50 Item' },
                                            { value: 100, label: '100 Item' },
                                        ]" select-class="border-start-0 ps-2 shadow-none" />
                                    </div>
                                </div>

                                <div class="col-xl-2 col-md-4 col-6">
                                    <input-label class="form-label-custom mb-1" for="order_by" value="URUTAN" />
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 text-muted ps-3">
                                            <i class="fas fa-sort"></i>
                                        </span>
                                        <select-input :is-valid="false" v-model="filters.order_by" name="order_by"
                                            :options="[
                                                { value: 'desc', label: 'Terbaru' },
                                                { value: 'asc', label: 'Terlama' },
                                            ]" select-class="border-start-0 ps-2 shadow-none" />
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="!product?.data.length" class="text-center py-5 my-5">
                <div class="mb-3">
                    <i class="bi bi-box2 display-1 text-muted opacity-25"></i>
                </div>
                <h5 class="fw-bold text-muted">Produk tidak ditemukan</h5>
                <p class="text-muted small">Coba ubah kata kunci pencarian atau filter Anda.</p>
            </div>



            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-3 row-cols-xl-5 g-3 gy-5">
                <div class="col" :id="row.product_id" v-for="(row, rowIndex) in product?.data" :key="rowIndex">

                    <div class="d-flex justify-content-evenly mb-2 text-bg-light rounded-pill p-1 shadow-sm">
                        <button class="btn btn-link btn-sm text-decoration-none p-0">
                            <i class="fas fa-edit"></i> Ubah
                        </button>
                        <span class="border mx-1"></span>
                        <button @click.prevent="deleted('product.deleted', row)"
                            class="btn btn-link btn-sm text-decoration-none text-danger p-0">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    </div>
                    <div class="card h-100 product-card border-0 shadow-sm rounded-4 overflow-hidden position-relative">

                        <div class="product-image-wrapper position-relative bg-light">

                            <div class="ratio ratio-1x1 overflow-hidden">
                                <img :src="resolveImage(row.image_link)"
                                    class="card-img-top object-fit-cover product-img transition-transform"
                                    :alt="row.name" />
                            </div>
                        </div>

                        <div class="card-body d-flex flex-column p-3">

                            <a :href="row.link" target="_blank" class="text-decoration-none text-dark mb-2">
                                <h6 class="product-title fw-bold mb-0 text-capitalize line-clamp-2" :title="row.name"
                                    v-html="highlight(row.name, filters.keyword)">
                                </h6>
                            </a>

                            <span v-if="row.price_discount"
                                class="badge position-absolute top-0 end-0 m-2 bg-info shadow-sm rounded-pill z-2">
                                -{{ Math.round((1 - (row.price_discount / row.price_original)) * 100) }}%
                            </span>


                            <div class="small text-muted text-truncate mb-2" :title="formatCategory(row.category)">
                                {{ formatCategory(row.category) }}
                            </div>



                            <div class="mt-auto">
                                <div v-if="row.price_discount" class="d-flex flex-column">
                                    <small class="text-decoration-line-through text-muted fs-8">
                                        {{ formatCurrency(row.price_original) }}
                                    </small>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="fw-bold text-danger fs-5">
                                            {{ formatCurrency(row.price_discount) }}
                                        </span>
                                    </div>
                                    <div class="mt-2">
                                        <span
                                            class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-1 w-100 py-1 fw-normal fs-8">
                                            <i class="fas fa-tags me-1"></i> Hemat {{ formatCurrency(row.price_original
                                                - row.price_discount) }}
                                        </span>
                                    </div>
                                </div>

                                <div v-else>
                                    <span class="fw-bold text-dark fs-5">
                                        {{ formatCurrency(row.price_original) }}
                                    </span>
                                </div>

                                <div class="mt-2 border-top border-dashed p-2">
                                    <small class="text-muted fs-9 fst-italic px-2">
                                        <i class="fas fa-map-marker-alt me-1 text-secondary"></i>Harga Jabodetabek
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="card-action-overlay d-flex justify-content-center align-items-center gap-2">
                            <button @click="openModal(row.product_id)" class="btn btn-light rounded-circle shadow-sm"
                                title="Lihat Detail">
                                <i class="fas fa-eye text-primary"></i>
                            </button>
                            <a :href="row.link" target="_blank" class="btn btn-light rounded-circle shadow-sm"
                                title="Buka Link">
                                <i class="fas fa-external-link-alt text-dark"></i>
                            </a>
                        </div>

                    </div>
                </div>
            </div>

            <div
                class="mt-5 d-flex flex-column align-items-center gap-3 mb-4 rounded-3 bg-white overflow-hidden text-wrap">
                <div class="pt-4 text-center mb-3">
                    <pagination :links="props.product?.links" routeName="product" :additionalQuery="{
                        limit: filters.limit,
                        order_by: filters.order_by,
                        keyword: filters.keyword,
                        category: filters.category
                    }" />
                    <div class="text-muted small">
                        Menampilkan <strong>{{ props.product?.from ?? 0 }}</strong> - <strong>{{ props.product?.to ?? 0
                            }}</strong>
                        dari <strong>{{ props.product?.total ?? 0 }}</strong> produk
                    </div>
                </div>

            </div>


            <div class="row" v-if="showModal">
                <div class="col-xl-12 col-sm-12 col-md-12 col-lg-12">
                    <modal :footer="true" @opened="openModal" size="modal-xl" icon="fas fa-images" v-if="showModal"
                        :show="showModal" title="Galleri Produk" @update:show="showModal = $event" @closed="closeModal">
                        <template #body>
                            <div class="row layout-overlay">
                                <div class="product-gallery">
                                    <div class="mb-3 mt-3">
                                        <img :src="resolveImage(mainImage)"
                                            class="img-fluid rounded shadow-sm first-image-gallery" />
                                    </div>
                                </div>
                                <div class="d-flex gap-2 flex-wrap justify-content-center mb-5">
                                    <div v-for="(img, index) in imageGallery" :key="index" class="thumbnail-wrapper"
                                        @click="setMainImage(img)">
                                        <img :src="resolveImage(img)" class="img-thumbnail gallery-thumb"
                                            :class="{ active: mainImage === img }" />
                                    </div>
                                </div>
                                <div v-html="description"></div>
                            </div>
                        </template>
                    </modal>
                </div>
            </div>
        </template>
    </app-layout>
</template>
<style scoped>
.image-wrapper {
    overflow: hidden;
    height: 220px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    border: 1px solid #dadada;
}


.image-figure-product {
    transition: transform 0.2s ease-in-out;
    width: 100%;
    height: 220px;
    object-fit: fill;
    object-position: center;
}

.image-figure-product:hover {
    transform: scale(1.03);
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
}

.image-wrapper:hover {
    box-shadow: 0 0 8px rgba(0, 0, 0, 0.432);
}

.first-image-gallery {
    height: 550px;
    width: 550px;
    object-fit: contain;
    object-position: center;
    display: flex;
    margin: auto;
    border: 1px solid #d6d6d6;
    padding: 5px;

}

.layout-overlay {
    max-height: 100vh;
    overflow-y: auto;
    padding-right: 3px;
    position: relative;
}

.gallery-thumb {
    width: 80px;
    height: 80px;
    object-fit: cover;
    cursor: pointer;
    transition: 0.2s;
}

.gallery-thumb:hover {
    transform: scale(1.05);
}

.gallery-thumb.active {
    border: 2px solid #0d6efd;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.6);
}

iframe {
    width: 100%;
    height: 360px;
    border: none;
}



/* Card Base Styling */
.product-card {
    background: #ffffff;
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    border: 1px solid rgba(0, 0, 0, 0.05) !important;
    /* Border sangat tipis */
}

/* Hover Effect: Card Lift */
.product-card:hover {
    transform: translateY(-5px);
    /* Naik sedikit */
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08) !important;
    /* Shadow menebal */
    border-color: rgba(0, 0, 0, 0.0) !important;
}

/* Image Zoom Effect */
.product-img {
    transition: transform 0.5s ease;
}

.product-card:hover .product-img {
    transform: scale(1.08);
    /* Gambar membesar sedikit */
}

/* Typography Helpers */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    height: 2.5em;
    /* Menjaga tinggi judul konsisten */
    line-height: 1.25;
}

.fs-8 {
    font-size: 0.75rem !important;
}

.fs-9 {
    font-size: 0.7rem !important;
}

/* Overlay Action Buttons (Muncul saat hover) */
.card-action-overlay {
    position: absolute;
    top: 45%;
    left: 50%;
    transform: translate(-50%, -50%);
    opacity: 0;
    transition: all 0.3s ease;
    z-index: 5;
    pointer-events: none;
}

/* Efek saat card di hover, overlay muncul */
.product-card:hover .card-action-overlay {
    opacity: 1;
    pointer-events: auto;
}

.product-card:hover .product-image-wrapper::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.1);
    /* Sedikit gelap saat hover */
    pointer-events: none;
    transition: background 0.3s;
}

/* Border Dashed untuk info tambahan */
.border-dashed {
    border-style: dashed !important;
}

/* Cursor Pointer */
.cursor-pointer {
    cursor: pointer;
}


/* Card Filter */
.filter-card {
    background: #ffffff;
    transition: box-shadow 0.3s ease;
}

.filter-card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
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

/* Styling Input Group agar terlihat Seamless */
.input-group-text {
    background-color: #fff;
    border-color: #dee2e6;
    /* Warna border default bootstrap */
    border-right: none;
    /* Hilangkan border kanan ikon */
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

/* Icon Box Kecil di Judul */
.icon-box-sm {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Letter Spacing */
.ls-1 {
    letter-spacing: 1px;
}
</style>
