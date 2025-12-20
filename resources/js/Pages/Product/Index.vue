<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { Head, router, usePage } from '@inertiajs/vue3';
import formatCurrency from "@/helpers/formatCurrency";
import { debounce, get } from "lodash";
import { highlight } from "@/helpers/highlight";
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
</script>
<template>

    <Head title="Halaman Produk" />
    <app-layout>
        <template #content>
            <bread-crumbs :home="false" icon="fas fa-tags" title="DAFTAR PRODUK" :items="[{ text: '' }]" />
            <alert :variant="page.props.flash.message ? 'success' : 'danger'" :duration="10" :message="message" />

            <div class="card mb-4 rounded-0 bg-light rounded-4 overflow-hidden shadow">
                <div class="card-header text-bg-dark">
                    <h5 class="card-title text-start mb-0 p-2 gap-2 text-uppercase fw-bold">
                        <i class="fas fa-filter"></i> Filter Produk
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center p-2 g-2 pb-3">
                        <div class="col-xl-6 col-sm-6 col-md-3">
                            <input-label class="fw-bold mb-1" for="keyword" value="Pencarian:" />
                            <div class="input-group">
                                <text-input ref="inputRef" placeholder="Pencarian....." name="keyword"
                                    v-model="filters.keyword" type="text" :is-valid="false" />
                            </div>
                        </div>

                        <div class="col-xl-2 col-sm-6 col-md-3">
                            <input-label class="fw-bold mb-1" for="category" value="Kategori:" />
                            <div class="input-group">
                                <select-input text="Pilih Kategori" :is-valid="false" v-model="filters.category"
                                    name="category" :options="categories" />
                            </div>
                        </div>
                        <div class="col-xl-2 col-sm-6 col-md-3">
                            <input-label class="fw-bold mb-1" for="limit" value="Batas:" />
                            <div class="input-group">
                                <select-input :is-valid="false" v-model="filters.limit" name="limit" :options="[
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
            </div>

            <div class="card overflow-hidden rounded-3 shadow-sm py-5 text-center text-muted shadow"
                v-if="!product?.data.length">
                <span class="fw-bold">Tidak ada Produk ditemukan</span>
            </div>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-3 row-cols-xl-5 g-3">
                <div class="col-auto" :id="row.product_id" v-for="(row, rowIndex) in product?.data" :key="rowIndex">


                    <figure class="figure text-bg-light p-2 rounded-3 shadow border h-100 d-flex flex-column">

                        <div class="image-wrapper mb-2  rounded-3">
                            <img @click="openModal(row.product_id)"
                                :src="row.image_link ?? 'https://via.placeholder.com/300'"
                                class=" img-fluid image-figure-product" :alt="row.name" />
                        </div>

                        <figcaption class="figure-caption">
                            <span class="badge bg-info text-white border mb-1">
                                {{ formatCategory(row.category) }}
                            </span>

                            <a :id="row.product_id" :href="row.link" target="_blank" class="text-decoration-none">
                                <div class="fw-bold text-wrap text-capitalize" :title="row.name"
                                    v-html="highlight(row.name, filters.keyword)">
                                </div>
                            </a>

                            <div class="text-dark fw-bold mt-1 fs-6 align-items-center d-block">
                                <small v-if="row.price_discount" class="text-muted text-decoration-line-through me-2">{{
                                    formatCurrency(row.price_original) ?? '-'
                                }}</small>

                                {{ formatCurrency(row.price_discount ? row.price_discount : row.price_original)
                                }}
                                <span v-if="row.price_discount" class="badge text-bg-primary mx-1">
                                    {{ row.price_discount / row.price_original < 1 ? Math.round((1 - (row.price_discount
                                        / row.price_original)) * 100) + '%' : '' }} </span>
                                        <div v-if="row.price_discount" class="badge text-bg-success">{{ 'Hemat: ' +
                                            formatCurrency(row.price_original - row.price_discount) }}</div>

                            </div>
                            <small class="text-danger">*Harga jabodetabek</small>

                        </figcaption>
                    </figure>
                </div>


            </div>
            <div class="mt-5 mb-4 d-grid flex-wrap justify-content-center align-items-center flex-column flex-lg-row">
                <pagination :links="props.product?.links" routeName="product" :additionalQuery="{
                    limit: filters.limit,
                    order_by: filters.order_by,
                    keyword: filters.keyword,
                    category: filters.category
                }" />
                <div class="mb-2 text-center">
                    Menampilkan <strong>{{ props.product?.from ?? 0 }}</strong> sampai
                    <strong>{{ props.product?.to ?? 0 }}</strong> dari total
                    <strong>{{ props.product?.total ?? 0 }}</strong> data
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
                                        <img :src="mainImage" class="img-fluid rounded shadow-sm first-image-gallery" />
                                    </div>
                                </div>
                                <div class="d-flex gap-2 flex-wrap justify-content-center mb-5">
                                    <div v-for="(img, index) in imageGallery" :key="index" class="thumbnail-wrapper"
                                        @click="setMainImage(img)">
                                        <img :src="img ?? 'https://via.placeholder.com/150'"
                                            class="img-thumbnail gallery-thumb"
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
</style>
