<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { Head, Link, router, useForm, usePage } from "@inertiajs/vue3";
const props = defineProps({
    product: {
        type: Object,
        default: () => ({})
    },
    branch: Array
})
const form = useForm({
    name: props.product.product?.name ?? '',
    link: props.product?.product?.link ?? '',
    category: props.product?.product?.category ?? '',
    status: props.product?.product?.status ?? null,
    image: null,

    base_price: props.product?.base_price ?? 0,
    discount_price: props.product?.discount_price ?? 0,
    branch: props.product?.branch?.branches_id ?? [],
    valid_from: props.product?.valid_from ?? '',
    valid_until: props.product?.valid_until ?? '',

});

const isSubmit = () => {
    if (props.product?.product_price_id) {
        form.post(route('product.update', props.product.product_price_id), {
            forceFormData: true,
            _method: 'put',
            onSuccess: () => {
                form.reset();
            },

        })
    } else {
        // Create
        form.post(route('product.store'), {
            forceFormData: true,
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
    if (props.product && props.product?.product_price_id) {
        title.value = "Ubah Data Produk " + props.product?.product?.name
        icon.value = "fas fa-edit"
        url.value = route('product')
    } else {
        title.value = "Tambah Produk Baru"
        icon.value = "fas fa-plus-square"
        url.value = route('product')
    }
})
const breadcrumbItems = computed(() => {
    if (props.product && props.product?.product_price_id) {
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

// branch option
const branchOptions = computed(() => {
    return [{
        value: '',
        label: '-- Pilih Cabang --'
    },
    ...(props.branch?.map((branch) => ({
        value: branch.branches_id,
        label: branch.name
    })))
    ];
})

// State untuk Mode Gambar (Default 'upload' agar user manual mudah)
const previewImage = ref(null);
const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        // Buat preview lokal
        previewImage.value = URL.createObjectURL(file);
        // Masukkan file ke form
        form.image = file;
    }
};

// Hitung Diskon Otomatis
const discountPercentage = computed(() => {
    if (form.base_price > 0 && form.discount_price > 0 && form.base_price > form.discount_price) {
        const discount = form.base_price - form.discount_price;
        return Math.round((discount / form.base_price) * 100);
    }
    return 0;
});

// Fungsi ini penting agar gambar dari storage lokal dan link luar bisa tampil
const resolveImage = (path) => {
    if (!path) return 'https://ui-avatars.com/api/?name=??';
    // Jika link eksternal (http/https)
    if (path.startsWith('http')) return path;

    // Jika file lokal, tambahkan '/' agar root terbaca
    // Pastikan path di DB kamu 'storage/...' atau sesuaikan disini
    return `/storage/${path}`;
};

// ON MOUNTED (LOGIKA UTAMA EDIT)
onMounted(() => {
    // A. SET PREVIEW GAMBAR UTAMA (COVER)
    if (props.product.image_path) {
        previewImage.value = resolveImage(props.product.image_path);
    }
});
// const isChecked = (id) => {
//     return form.branch.includes(id)
// }
// console.log(props.product?.branch?.branches_id)
// watch(
//     () => props.product?.branch?.branches_id,
//     (val) => {
//         if (val) {
//             form.branch = String(val)
//         }
//         console.log(val)
//     },
//     { immediate: true }
// )
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
                                        <div class="animate-fade">
                                            <input-label class="form-label-custom" value="PILIH FILE DARI PERANGKAT" />
                                            <input type="file" class="form-control form-control-sm"
                                                @change="handleFileUpload" accept="image/png,image/jpeg,image/jpg">
                                            <div class="form-text small text-muted mt-2">
                                                <i class="fas fa-info-circle me-1"></i>Format JPG/PNG, Max 2MB.
                                            </div>
                                        </div>
                                        <input-error :message="form.errors.image" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-8">
                                <div class="card border-0 shadow-sm rounded-4 h-100">
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
                                            class="mb-3 p-4 bg-light rounded-4 border border-dashed position-relative overflow-hidden">
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

                                            <div class="row g-3 position-relative z-1">
                                                <div class="col-md-6">
                                                    <input-label class="form-label-custom" for="valid_from"
                                                        value="Tanggal Berlaku" />
                                                    <div class="input-group">
                                                        <text-input type="date" v-model="form.valid_from"
                                                            name="valid_from" />
                                                    </div>
                                                    <input-error :message="form.errors.valid_from" />

                                                </div>

                                                <div class="col-md-6">
                                                    <div class="d-flex align-items-center">
                                                        <input-label class="form-label-custom" for="valid_until"
                                                            value="Tanggal Berakhir" />
                                                        <span
                                                            class="badge bg-light text-muted border fw-normal mx-2 mb-2">Opsional</span>
                                                    </div>
                                                    <div class="input-group">
                                                        <text-input type="date" v-model="form.valid_until"
                                                            name="valid_until" />
                                                    </div>
                                                    <input-error :message="form.errors.valid_until" />
                                                    <div class="form-text fst-italic small text-muted mt-1">Tentukan
                                                        jika sedang
                                                        promo.
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <input-label class="form-label-custom" value="Harga Per-Cabang" />
                                                    <div :class="[{ 'text-bg-light bg-opacity-10 ': form.branch.length > 0, 'text-bg-danger': form.branch.errors }]"
                                                        class="border p-2 rounded-3 d-flex gap-2 flex-wrap ">
                                                        <!-- <label
                                                            class="form-check-label gap-1 d-flex border  rounded-2 p-1 small text-capitalize"
                                                            v-for="item in branch" :key="item.branches_id">
                                                            <input multiple class="form-check-input" type="checkbox"
                                                                :value="item.branches_id" v-model="form.branch" />
                                                            <check-box :value="item.branches_id"
                                                                v-model:checked="form.branch" :label="item.name"
                                                                :name="item.name" />
                                                            <span class="fw-semibold">{{ item.name
                                                                }}</span>
                                                        </label> -->
                                                        <check-box :value="form.branch" 
                                                                v-model:checked="form.branch" :label="form.branch"
                                                                :name="form.branch" />
                                                    </div>
                                                    <input-error :message="form.errors.branch" />

                                                    <div class="form-text fst-italic small text-muted mt-1">Tentukan
                                                        harga untuk setiap cabang
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <input-label class="form-label-custom" for="base_price"
                                                        value="HARGA JUAL (NORMAL)" />
                                                    <div class="input-group">
                                                        <currency-input input-class="text-bg-grey fw-bold" :decimals="0"
                                                            v-model="form.base_price" name="base_price" />
                                                    </div>
                                                    <input-error :message="form.errors.base_price" />
                                                </div>
                                                <div class="col-md-6">
                                                    <input-label class="form-label-custom text-danger"
                                                        value="HARGA CORET (DISKON)" />
                                                    <div class="input-group">
                                                        <currency-input input-class="text-bg-grey fw-bold" :decimals="0"
                                                            v-model="form.discount_price" name="discount_price" />
                                                    </div>
                                                    <div class="form-text fst-italic small text-muted mt-1">Isi hanya
                                                        jika sedang
                                                        promo.
                                                    </div>
                                                </div>

                                            </div>
                                        </div>

                                        <div
                                            class="mb-3 p-4 bg-light rounded-4 border border-dashed position-relative overflow-hidden">

                                            <div
                                                class="d-flex align-items-center justify-content-between mb-4 position-relative z-1">
                                                <h6 class="fw-bold text-dark mb-0 text-uppercase ls-1">
                                                    <i class="fas fa-cog me-2 text-success"></i> Add On
                                                </h6>
                                            </div>

                                            <div class="row g-4 position-relative z-1">
                                                <div class="col-md-12">
                                                    <input-label class="form-label-custom" value="Status Publisher" />
                                                    <div class="input-group">
                                                        <select-input :options="[
                                                            { value: null, label: '--Pilih Status--' },
                                                            { value: 'draft', label: 'Draft' },
                                                            { value: 'published', label: 'Publish' }
                                                        ]" v-model="form.status" />
                                                    </div>
                                                    <input-error :message="form.errors.status" />
                                                </div>
                                            </div>
                                        </div>



                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="card overflow-hidden border-0 shadow-sm rounded-4">
                                    <div class="card-body p-3 mb-3">

                                    </div>

                                </div>
                            </div>
                            <div class="d-flex justify-content-end pt-3 border-top">
                                <base-button :loading="form.processing"
                                    button-class="btn-height-2 rounded-3 px-4 shadow btn-save-animate" type="submit"
                                    :icon="props.product?.product_price_id ? 'fas fa-edit' : 'fas fa-save'"
                                    :label="props.product?.product_price_id ? 'Simpan Perubahan' : 'Simpan Produk'"
                                    :variant="props.product?.product_price_id ? 'success' : 'primary'" />
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

/* Container Card */
.gallery-card {
    transition: all 0.3s ease;
    border: 1px solid #f0f0f0;
}

/* Efek Hover pada Card */
.gallery-card:hover {
    transform: translateY(-3px);
    /* Naik sedikit saat hover */
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
}

/* Animasi Gambar */
.transition-transform {
    transition: transform 0.5s ease;
}

.gallery-card:hover .transition-transform {
    transform: scale(1.05);
    /* Zoom in halus */
}

/* Tombol Hapus Custom */
.btn-delete {
    width: 28px;
    height: 28px;
    padding: 0;
    opacity: 0.8;
    /* Sedikit transparan */
    transition: all 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    z-index: 10;
}

.btn-delete i {
    font-size: 12px;
}

/* Hover pada Tombol Hapus */
.gallery-card:hover .btn-delete {
    opacity: 1;
    /* Jadi jelas saat card di-hover */
}

.btn-delete:hover {
    transform: scale(1.15) rotate(90deg);
    /* Efek membesar & putar saat mau diklik */
    background-color: #dc3545 !important;
}

/* Overlay Gradient (Supaya icon putih tetap kebaca di gambar putih) */
.overlay-gradient {
    background: linear-gradient(to bottom, rgba(0, 0, 0, 0.2) 0%, rgba(0, 0, 0, 0) 30%);
    pointer-events: none;
    /* Agar klik tembus ke gambar */
}

/* Utilities Kecil */
.fs-10 {
    font-size: 10px;
}

.backdrop-blur {
    backdrop-filter: blur(2px);
}

.pointer-events-none {
    pointer-events: none;
}
</style>
