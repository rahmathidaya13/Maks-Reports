<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { Head, Link, router, useForm, usePage } from "@inertiajs/vue3";
const props = defineProps({
    product: {
        type: Object,
        default: () => [{}]
    },
})
const form = useForm({
    name: props.product?.product_code ?? props.uniqCode,
    category: props.product?.title ?? '',
    price_original: props.product?.title_alias ?? 0,
    price_discount: props.product?.description ?? 0,
    link: props.product?.link ?? '',
    image_link: props.product?.image_link ?? '',
    image_url: props.product?.image_url ?? '',
    description: props.product?.description ?? '',
    image_path: props.product?.image_path
});
const isSubmit = () => {
    if (props.product?.product_id) {
        form.put(route('product.update', props.product.product_id), {
            onSuccess: () => {
                form.reset();
            },
        })
    } else {
        // Create
        form.post(route('product.store'), {
            onSuccess: () => {
                form.reset();
            }
        });
    }
};
const title = ref("");
const icon = ref("");
const url = ref("")
onMounted(() => {
    if (props.product && props.product?.product_id) {
        title.value = "Ubah Data Produk " + props.product?.name
        icon.value = "fas fa-edit"
        url.value = route('product')
    } else {
        title.value = "Tambah Produk Baru"
        icon.value = "fas fa-plus-square"
        url.value = route('product')
    }
})
const breadcrumbItems = computed(() => {
    if (props.product && props.product?.product_id) {
        return [
            { text: "Daftar Produk", url: route("product") },
            { text: "Tambah Produk Baru", url: route("product.create") },
            { text: title.value }
        ]
    }
    return [
        { text: "Daftar Produk", url: route("product") },
        { text: title.value }
    ]
})

const loaderActive = ref(null);
const goBack = () => {
    loaderActive.value?.show("Memproses...");
    router.get(url.value, {}, {
        onFinish: () => loaderActive.value?.hide()
    });
}


// State untuk Mode Gambar (Default 'upload' agar user manual mudah)
const imageMode = ref('upload');
const previewImage = ref(null);

// Jika sedang edit data, cek dulu dia punya image_link (URL) atau tidak
// Jika ada image_link, set mode ke URL, jika tidak asumsikan upload
// (Sesuaikan logika ini dengan props data edit kamu)

// 1. Handle Upload File
const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        // Buat preview lokal
        previewImage.value = URL.createObjectURL(file);
        // Masukkan file ke form inertia
        form.image_path = file;
        // Kosongkan link url agar backend tau ini upload file
        form.image_link = null;
    }
};

// 2. Handle URL Input
const updatePreviewFromUrl = () => {
    // Jika user mengetik URL, jadikan itu preview
    previewImage.value = form.image_link;
    // Kosongkan file upload di form
    form.image_path = null;
};

// 3. Hitung Diskon Otomatis
const discountPercentage = computed(() => {
    if (form.price_original > 0 && form.price_discount > 0 && form.price_original > form.price_discount) {
        const discount = form.price_original - form.price_discount;
        return Math.round((discount / form.price_original) * 100);
    }
    return 0;
});

// State untuk Galeri
const galleryPreviews = ref([]); // Untuk menampilkan gambar di layar
const galleryFiles = ref([]);    // Untuk menyimpan File Object yang akan dikirim ke backend

// Handle Upload Banyak Gambar
const handleGalleryUpload = (event) => {
    const files = Array.from(event.target.files);

    // Batasi jumlah jika perlu (misal max 5)
    if (galleryFiles.value.length + files.length > 10) {
        alert("Maksimal 10 foto galeri!");
        return;
    }

    files.forEach(file => {
        // 1. Masukkan ke array File untuk dikirim nanti
        galleryFiles.value.push(file);

        // 2. Buat URL lokal untuk preview
        const reader = new FileReader();
        reader.onload = (e) => {
            galleryPreviews.value.push(e.target.result);
        };
        reader.readAsDataURL(file);
    });

    // Update form inertia (asumsikan kolom di database namanya image_url tapi di proses controller nanti handle array)
    // Note: Inertia tidak bisa kirim File object di dalam JSON string langsung.
    // Kamu harus mengirimnya sebagai array of files: form.gallery_files
    form.image_url = galleryFiles.value;
};

// Hapus Gambar dari Galeri
const removeGalleryImage = (index) => {
    galleryPreviews.value.splice(index, 1);
    galleryFiles.value.splice(index, 1);
    form.image_url = galleryFiles.value;
};
</script>
<template>

    <Head :title="title" />
    <app-layout>
        <template #content>
            <loader-page ref="loaderActive" />
            <bread-crumbs :icon="icon" :title="title" :items="breadcrumbItems" />

            <div class="row pb-3">
                <div class="col-12">

                    <div class="d-flex align-items-center mb-4">
                        <div class="icon-square-lg bg-primary bg-opacity-10 text-primary rounded-circle shadow-sm me-3">
                            <i class="fas fa-boxes fs-3"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold text-dark mb-0">Kelola Produk</h4>
                            <p class="text-muted small mb-0">Tambah atau ubah produk untuk katalog yang ditampilkan.</p>
                        </div>
                        <Link @click.prevent="goBack" :href="route('product')"
                            class="btn btn-danger ms-auto border hover-scale px-3 fw-bold">
                            <i class="fas fa-arrow-left me-2"></i> Kembali
                        </Link>
                    </div>

                    <form-wrapper @submit="isSubmit">
                        <div class="row g-4">
                            <div class="col-lg-4">
                                <div class="card border-0 shadow-sm rounded-4 h-100">
                                    <div class="card-body p-4">
                                        <h6 class="section-title text-indigo mb-3">
                                            <i class="fas fa-camera me-2"></i>Foto Utama
                                        </h6>

                                        <div
                                            class="image-preview-box mb-3 rounded-3 overflow-hidden border bg-light position-relative group ">
                                            <img v-if="previewImage" :src="previewImage"
                                                class="img-fluid w-100 h-100 object-fit-cover transition-transform hover-zoom cursor-pointer"
                                                alt="Preview">

                                            <div v-else class="text-center text-muted opacity-50 p-4 absolute-center">
                                                <i class="bi bi-image fs-1 mb-2"></i>
                                                <p class="small mb-0">Belum ada gambar dipilih</p>
                                            </div>
                                        </div>

                                        <div class="nav nav-pills nav-fill mb-3 bg-light p-1 rounded-3" role="tablist">
                                            <button class="nav-link rounded-2 py-1 fs-8 fw-bold"
                                                :class="{ 'active bg-white shadow-sm text-dark': imageMode === 'upload' }"
                                                @click.prevent="imageMode = 'upload'" type="button">
                                                <i class="fas fa-cloud-upload-alt me-1"></i> UPLOAD
                                            </button>
                                            <button class="nav-link rounded-2 py-1 fs-8 fw-bold"
                                                :class="{ 'active bg-white shadow-sm text-dark': imageMode === 'url' }"
                                                @click.prevent="imageMode = 'url'" type="button">
                                                <i class="fas fa-link me-1"></i> URL LINK
                                            </button>
                                        </div>

                                        <div v-if="imageMode === 'upload'" class="animate-fade">
                                            <input-label class="form-label-custom" value="PILIH FILE DARI PERANGKAT" />
                                            <input type="file" ref="fileInput" class="form-control form-control-sm"
                                                @change="handleFileUpload" accept="image/png,image/jpeg,image/jpg">
                                            <div class="form-text small text-muted mt-2">
                                                <i class="fas fa-info-circle me-1"></i>Format JPG/PNG, Max 2MB.
                                            </div>
                                        </div>

                                        <div v-if="imageMode === 'url'" class="animate-fade">
                                            <input-label class="form-label-custom" value="PASTE LINK GAMBAR" />
                                            <div class="position-relative">
                                                <i class="fas fa-globe input-icon-left text-secondary"></i>
                                                <text-input input-class="input-fixed-height" placeholder="https://..."
                                                    v-model="form.image_link" @input="updatePreviewFromUrl" />
                                            </div>
                                            <div class="small text-muted mt-2">
                                                Cocok untuk produk dari marketplace luar.
                                            </div>
                                        </div>

                                        <div class="mt-4 pt-4 border-top">
                                            <div class="d-flex align-items-center justify-content-between mb-3">
                                                <h6 class="section-title text-primary mb-0 border-0 pb-0">
                                                    <i class="fas fa-images me-2"></i>Galeri Produk
                                                </h6>
                                                <span class="badge bg-light text-muted border">{{ galleryPreviews.length
                                                    }} Foto</span>
                                            </div>

                                            <div class="upload-gallery-box text-center p-3 rounded-3 mb-3 cursor-pointer hover-bg-light"
                                                @click="$refs.galleryInput.click()">
                                                <input type="file" ref="galleryInput" class="d-none" multiple
                                                    accept="image/png,image/jpeg,image/jpg"
                                                    @change="handleGalleryUpload">
                                                <i
                                                    class="fas fa-cloud-upload-alt fs-3 text-primary mb-2 opacity-75"></i>
                                                <h6 class="fw-bold text-dark fs-8 mb-0">Klik untuk Upload Galeri</h6>
                                                <small class="text-muted fs-9">Bisa pilih banyak foto sekaligus</small>
                                            </div>

                                            <div class="row g-2" v-if="galleryPreviews.length">
                                                <div class="col-4 position-relative fade-in"
                                                    v-for="(img, index) in galleryPreviews" :key="index">
                                                    <div
                                                        class="ratio ratio-1x1 rounded-3 overflow-hidden border shadow-sm group-gallery">
                                                        <img :src="img" class="object-fit-cover w-100 h-100">

                                                        <button type="button" @click="removeGalleryImage(index)"
                                                            class="btn-remove-gallery position-absolute top-0 end-0 m-1 bg-danger text-white rounded-circle shadow d-flex align-items-center justify-content-center"
                                                            style="width: 20px; height: 20px; padding: 0;">
                                                            <i class="fas fa-times" style="font-size: 10px;"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <div v-else class="text-center py-3 text-muted fst-italic fs-9">
                                                Belum ada foto tambahan.
                                            </div>
                                        </div>

                                        <input-error :message="form.errors.image_path" />
                                    </div>
                                </div>
                            </div>



                            <div class="col-lg-8">
                                <div class="card border-0 shadow-sm rounded-4">
                                    <div class="card-body p-4">

                                        <div class="mb-5">
                                            <h6 class="section-title text-primary mb-4">
                                                <i class="fas fa-tag me-2"></i>Detail Produk
                                            </h6>
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <input-label class="form-label-custom" for="name"
                                                        value="NAMA PRODUK" />
                                                    <text-input placeholder="Masukan nama produk..." name="name"
                                                        v-model="form.name" input-class="fw-bold" />
                                                    <input-error :message="form.errors.name" />
                                                </div>
                                                <div class="col-md-6">
                                                    <input-label class="form-label-custom" for="category"
                                                        value="KATEGORI" />
                                                    <text-input placeholder="Contoh: Mesin Makanan, Sparpart Dll"
                                                        name="category" v-model="form.category" input-class="fw-bold" />
                                                    <input-error :message="form.errors.category" />
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="align-items-center d-flex">
                                                        <input-label class="form-label-custom" for="link"
                                                            value="LINK MARKETPLACE" />
                                                        <span
                                                            class="badge bg-light text-muted border fw-normal mx-2 mb-2">Opsional</span>
                                                    </div>
                                                    <div class="position-relative">
                                                        <i
                                                            class="fas fa-external-link-alt input-icon-left text-secondary"></i>
                                                        <text-input placeholder="https://shopee/..." name="link"
                                                            v-model="form.link" input-class="input-fixed-height" />
                                                    </div>
                                                    <input-error :message="form.errors.link" />
                                                </div>
                                            </div>
                                        </div>

                                        <div
                                            class="mb-5 p-4 bg-light rounded-4 border border-dashed position-relative overflow-hidden">
                                            <i class="fas fa-coins position-absolute bottom-0 end-0 text-secondary opacity-10"
                                                style="font-size: 6rem; transform: rotate(-20deg) translate(20px, 20px);"></i>
                                            <div
                                                class="d-flex align-items-center justify-content-between mb-4 position-relative z-1">
                                                <h6 class="fw-bold text-dark mb-0 text-uppercase ls-1">
                                                    <i class="fas fa-money-bill-wave me-2 text-success"></i>Pengaturan
                                                    Harga
                                                </h6>
                                                <transition name="fade">
                                                    <span v-if="discountPercentage > 0"
                                                        class="badge bg-danger shadow-sm px-3 py-2 rounded-pill">
                                                        <i class="fas fa-fire me-1"></i> Diskon {{ discountPercentage
                                                        }}%
                                                    </span>
                                                </transition>
                                            </div>

                                            <div class="row g-4 position-relative z-1">
                                                <div class="col-md-6">
                                                    <input-label class="form-label-custom"
                                                        value="HARGA JUAL (NORMAL)" />
                                                    <div class="input-group">
                                                        <currency-input input-class="text-bg-grey fw-bold" :decimals="0"
                                                            v-model="form.price_original" name="price_original" />
                                                    </div>
                                                    <input-error :message="form.errors.price_original" />
                                                </div>
                                                <div class="col-md-6">
                                                    <input-label class="form-label-custom text-danger"
                                                        value="HARGA CORET (DISKON)" />
                                                    <div class="input-group">
                                                        <currency-input input-class="text-bg-grey fw-bold" :decimals="0"
                                                            v-model="form.price_discount" name="price_discount" />
                                                    </div>
                                                    <div class="form-text fst-italic small text-muted mt-1">Isi hanya
                                                        jika sedang
                                                        promo.</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <input-label class="form-label-custom mb-2" value="DESKRIPSI LENGKAP" />
                                            <div class="quill-wrapper rounded-3 border overflow-hidden">
                                                <quill-text :maxChar="3000" placeholder="Tuliskan spesifikasi produk..."
                                                    v-model="form.description" height="300px" />
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-end pt-3 border-top gap-2">
                                            <base-button :loading="form.processing"
                                                button-class="btn-height-1 rounded-3 px-4 shadow btn-save-animate"
                                                type="submit" icon="fas fa-save" label="Simpan Produk"
                                                variant="primary" />
                                        </div>

                                    </div>
                                </div>
                            </div>

                        </div>
                    </form-wrapper>
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

/* Warna Custom */
.text-indigo {
    color: #6610f2;
}

.bg-indigo {
    background-color: #6610f2;
}

/* Preview Box Image */
.image-preview-box {
    width: 100%;
    height: 300px;
    background-color: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}

.absolute-center {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    width: 100%;
}

/* Hover Effects pada Preview */
.overlay-hover {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.3);
    opacity: 0;
    transition: opacity 0.3s;
    cursor: pointer;
}

.image-preview-box:hover .overlay-hover {
    opacity: 1;
}

.hover-zoom {
    transition: transform 0.5s;
}

.image-preview-box:hover .hover-zoom {
    transform: scale(1.05);
}

/* Nav Pills Custom (Switcher) */
.nav-pills .nav-link {
    color: #6c757d;
    transition: all 0.2s;
}

.nav-pills .nav-link.active {
    background-color: #fff;
    color: #000;
    border: 1px solid #dee2e6;
}

/* Animasi Fade untuk perpindahan Tab */
.animate-fade {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(5px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Input Group Harga */
.input-group-lg .input-group-text {
    min-width: 3.5rem;
    justify-content: center;
}

/* Section Title */
.section-title {
    font-size: 0.85rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid rgba(0, 0, 0, 0.05);
}

.border-dashed {
    border-style: dashed !important;
}

.fs-8 {
    font-size: 0.75rem !important;
}

.form-label-custom {
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    color: #6c757d;
    text-transform: uppercase;
}

.quill-wrapper {
    background-color: #fff;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.quill-wrapper:focus-within {
    border-color: #86b7fe !important;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

/* Button Animation */
.btn-save-animate {
    transition: transform 0.2s;
    font-weight: 600;
}

.btn-save-animate:hover {
    transform: translateY(-2px);
}

.btn-save-animate:active {
    transform: translateY(0);
}

.hover-scale {
    transition: transform 0.2s;
}

.hover-scale:hover {
    transform: scale(1.05);
}

.icon-square-lg {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
