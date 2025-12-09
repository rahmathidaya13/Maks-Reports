<script setup>
import { computed, reactive, ref, watch } from "vue";
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

const categories = computed(() => [
    { label: "Semua Kategori", value: null },
    ...props.category.map(cat => ({
        label: cat.category,
        value: cat.category,
    }))

]);

// =========Tampilkan Modal========== //
const showModal = ref(false);
const getById = ref(null);
const imageGallery = ref([]);
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
            imageGallery.value = data.galleryImages;
        }
        console.log(imageGallery.value);

    })
// =========Batas Fungsi untuk Tampilkan Modal========== //
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
                                <text-input placeholder="Pencarian....." autofocus name="keyword"
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

            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-3 row-cols-xl-5 g-3">
                <div class="mx-auto text-center" v-if="!product?.data.length">
                    <span class="fw-bold">Tidak ada data ditemukan</span>
                </div>
                <div class="col-auto" :id="row.id" v-for="(row, rowIndex) in product?.data" :key="rowIndex">


                    <figure class="figure text-bg-light p-3 rounded-4 shadow border h-100 d-flex flex-column">

                        <img @click="openModal(row.id)" :src="row.image_link ?? 'https://via.placeholder.com/300'"
                            class="figure-img img-fluid rounded shadow-sm" :alt="row.name"
                            style="width: 100%; height: 250px; object-fit: cover; object-position: center; cursor: pointer;" />

                        <figcaption class="figure-caption">
                            <span class="badge bg-secondary text-white border mb-1">
                                {{ row.category }}
                            </span>

                            <a :href="row.link" class="text-decoration-none" target="_blank">
                                <div class="fw-bold text-wrap text-capitalize" :title="row.name"
                                    v-html="highlight(row.name, filters.keyword)">
                                </div>
                            </a>

                            <div class="text-dark fw-bold mt-1 fs-6">
                                <small v-if="row.price_discount" class="text-muted text-decoration-line-through me-2">{{
                                    formatCurrency(row.price_original) ?? '-'
                                }}</small>

                                {{ formatCurrency(row.price_discount ? row.price_discount : row.price_original) }}
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


            <div class="row">
                <div class="col-xl-12 col-sm-12">
                    <modal @opened="openModal" size="modal-xl" :footer="false" icon="fas fa-gallery" v-if="showModal"
                        :show="showModal" title="Galleri" @update:show="showModal = $event" @closed="closeModal">
                        <template #body>
                            <!-- <div v-for="(img, index) in imageGallery" :key="index"
                                class="border p-2 shadow cursor-pointer">
                                <img :src="img ?? 'https://via.placeholder.com/600'"
                                    class="img-fluid img-thumbnail" />
                            </div> -->

                            <div class="row">
                                <div class="col-4 p-3 col-xl-4 col-md-6" v-for="(img, index) in imageGallery" :key="index">
                                    <img :src="img ?? 'https://via.placeholder.com/600'"
                                        class="img-fluid img-thumbnail h-h-50" />
                                </div>
                            </div>

                        </template>
                    </modal>
                </div>
            </div>
        </template>
    </app-layout>
</template>
<style scoped></style>
