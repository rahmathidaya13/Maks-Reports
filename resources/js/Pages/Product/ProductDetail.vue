<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { Head, Link, router, useForm, usePage } from "@inertiajs/vue3";
import Swal from "sweetalert2";
const props = defineProps({
    product: Object,
})
// State untuk loader dan Crud
const loaderActive = ref(null);
const goBack = () => {
    loaderActive.value?.show("Memproses...");
    router.get(route('product'), {}, {
        onFinish: () => loaderActive.value?.hide()
    });
}
const edit = (id) => {
    loaderActive.value?.show("Sedang memuat data...");
    router.get(route('product.edit', id), {}, {
        onFinish: () => loaderActive.value?.hide()
    });
}
const destroy = (id) => {

    Swal.fire({
        title: 'Hapus Produk?',
        text: `Produk ${props.product.name} akan dihapus. Data tidak dapat dikembalikan!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Tetap hapus!',
        cancelButtonText: 'Batal',
        customClass: {
            confirmButton: "btn btn-danger",
            cancelButton: "btn btn-outline-secondary",
        },
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('product.deleted', id), {}, {
                onFinish: () => loaderActive.value?.hide()
            });
        }

    })
}

// State untuk gambar aktif
const activeImage = ref('');

// Gabungkan Cover Image + Gallery Array
const allImages = computed(() => {
    let images = [];

    // 1. Masukkan Cover Image (jika ada)
    if (props.product.image_link) {
        images.push(props.product.image_link);
    }
    if (props.product.image_path) {
        images.push(props.product.image_path);
    }

    // 2. Masukkan Galeri (JSON array)
    if (props.product.image_url && Array.isArray(props.product.image_url)) {
        images = [...images, ...props.product.image_url];
    }

    // Filter duplikat (opsional) & pastikan tidak kosong
    return [...new Set(images)];
});

// Set gambar pertama saat load
onMounted(() => {
    if (allImages.value.length > 0) {
        activeImage.value = allImages.value[0];
    }
});

// Helper: Resolve Image Path (Hybrid Logic)
const resolveImage = (path) => {
    if (!path) return 'https://ui-avatars.com/api/?name=??';
    return `/storage/${path}`; // Untuk local storage
};

// Helper: Hitung % Diskon
const calculateDiscount = (original, discount) => {
    if (!original || !discount) return 0;
    return Math.round(((original - discount) / original) * 100);
};
// Helper: Format Rupiah
const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
};
</script>
<template>

    <Head title="Detail Produk" />
    <app-layout>
        <template #content>
            <loader-page ref="loaderActive" />

            <bread-crumbs :home="false" icon="fas fa-tags" title="Detail Produk" :items="[
                { text: 'Detail Produk' },
            ]" />

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">

                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <Link href="/products" class="text-decoration-none">Produk</Link>
                        </li>
                        <li class="breadcrumb-item active text-truncate" aria-current="page">
                            {{ props.product.name }}
                        </li>
                    </ol>
                </nav>

                <div class="d-flex align-items-center gap-2">

                    <button type="button" @click.prevent="goBack"
                        class="btn btn-danger border btn-sm bg-gradient fw-bold px-3 hover-scale">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </button>

                    <div class="vr h-75 align-self-center text-secondary"></div>

                    <button @click.prevent="edit(props.product.product_id)"
                        class="btn btn-outline-warning btn-sm bg-gradient fw-bold px-3 hover-scale" title="Edit Produk">
                        <i class="fas fa-edit"></i> <span class="d-none d-sm-inline">Edit</span>
                    </button>

                    <button @click.prevent="destroy(props.product.product_id)"
                        class="btn btn-outline-danger btn-sm bg-gradient fw-bold px-3 hover-scale" title="Hapus Produk">
                        <i class="fas fa-trash"></i> <span class="d-none d-sm-inline">Hapus</span>
                    </button>
                </div>
            </div>

            <div class="row pb-3">
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4 h-100 py-3">

                        <div class="main-image-wrapper">
                            <transition name="fade" mode="out-in">
                                <img :key="activeImage" :src="resolveImage(activeImage)"
                                    class="img-fluid w-100 h-100 object-fit-contain" alt="Product Image">
                            </transition>

                            <div class="position-absolute bottom-0 end-0 m-3 text-muted opacity-50">
                                <i class="fas fa-search-plus fs-4"></i>
                            </div>
                        </div>

                        <div class="d-flex gap-2 overflow-auto pb-2 custom-scrollbar mx-auto"
                            v-if="allImages.length > 1">

                            <div v-for="(img, index) in allImages" :key="index"
                                class="thumbnail-item flex-shrink-0 cursor-pointer rounded-3 overflow-hidden border"
                                :class="{ 'active-thumb': activeImage === img }" @click="activeImage = img">
                                <img :src="resolveImage(img)" class="img-fluid w-100 h-100 object-fit-cover">
                            </div>

                        </div>

                        <div class="card-body d-flex flex-column">

                            <div class="mb-3">
                                <span
                                    class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill text-uppercase ls-1">
                                    {{ props.product.category }}
                                </span>
                            </div>

                            <h1 class="fw-bold text-dark mb-3 lh-1">{{ props.product.name }}</h1>

                            <div class="p-4 bg-light rounded-4 mb-3 border d-flex justify-content-between shadow-sm">
                                <div class="d-flex align-items-end gap-3 flex-wrap">
                                    <h2 class="fw-bold text-success mb-0 display-6">
                                        {{ formatCurrency(props.product.price_discount || props.product.price_original)
                                        }}
                                    </h2>

                                    <div v-if="props.product.price_discount" class="d-flex flex-column mb-2">
                                        <span class="text-decoration-line-through text-muted small">
                                            {{ formatCurrency(props.product.price_original) }}
                                        </span>
                                        <span class="badge bg-danger rounded-pill">
                                            Hemat {{ calculateDiscount(props.product.price_original,
                                                props.product.price_discount) }}%
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="row g-xl-2 row-cols-1 row-cols-xl-3 mb-4 text-muted small justify-content-evenly">
                                <div class="col">
                                    <i class="fas fa-barcode me-2"></i> ID: <span class="text-dark fw-bold">{{
                                        props.product.product_id.replace(/-/g, '') }}...</span>
                                </div>
                                <div class="col">
                                    <i class="fas fa-user-edit me-1"></i> Oleh: <span class="text-dark fw-bold">{{
                                        props.product.creator?.name ?? '-' }}</span>
                                </div>
                                <div class="col">
                                    <i class="far fa-clock me-2"></i> Update: <span class="text-dark fw-bold">{{ new
                                        Date(props.product.updated_at).toLocaleDateString() }}</span>
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex mb-4 justify-content-between">
                                <a v-if="props.product.link" :href="props.product.link" target="_blank"
                                    class="btn btn-primary btn-lg rounded-3 px-5 shadow-sm btn-hover-lift">
                                    <i class="fas fa-shopping-cart me-2"></i> Kunjungi Toko
                                </a>
                                <button v-else class="btn btn-secondary btn-lg rounded-3 px-5 disabled" disabled>
                                    <i class="fas fa-ban me-2"></i> Link Tidak Tersedia
                                </button>
                            </div>

                            <hr class=" mb-4">

                            <div class="product-description">
                                <h5 class="fw-bold text-dark mb-3"><i
                                        class="fas fa-align-left me-2 text-secondary"></i>Deskripsi Produk</h5>
                                <div class="text-muted lh-lg description-content"
                                    v-html="props.product.description || 'Tidak ada deskripsi.'"></div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </template>
    </app-layout>
</template>
<style scoped>
.product-description {
    min-height: 300px;

}

.hover-scale {
    transition: transform 0.2s;
}

.hover-scale:hover {
    transform: scale(1.05);
}

/* Main Image Wrapper */
.main-image-wrapper {
    height: 650px;
    width: 600px;
    background-color: #fff;
    position: relative;
    overflow: hidden;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: auto;
    padding: 10px;

}

/* Thumbnail Styling */
.thumbnail-item {
    width: 100px;
    height: 100px;
    opacity: 0.6;
    transition: all 0.3s ease;
    object-fit: cover;
    cursor: pointer;
}

.thumbnail-item:hover {
    opacity: 1;
}

.thumbnail-item.active-thumb {
    opacity: 1;
    border-color: #0d6efd;
    /* Warna Primary Bootstrap */
    box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
}

/* Tombol Hover Effect */
.btn-hover-lift {
    transition: transform 0.2s, box-shadow 0.2s;
}

.btn-hover-lift:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
}

/* Description Content Styling */
/* Agar HTML dari WYSIWYG editor tetap rapi */
.description-content :deep(img) {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    margin: 10px 0;
}

.description-content :deep(ul),
.description-content :deep(ol) {
    padding-left: 1.2rem;
}

/* Custom Scrollbar untuk Thumbnail strip */
.custom-scrollbar::-webkit-scrollbar {
    height: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #aaa;
}

/* Animasi Fade saat ganti gambar */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.ls-1 {
    letter-spacing: 1px;
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

.price-box {
    background-color: #f8f9fa;
    border-radius: 80px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}
</style>
