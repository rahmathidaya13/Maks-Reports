<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { Head, Link, router, useForm, usePage } from "@inertiajs/vue3";
import moment from "moment";
moment.locale("id");
const props = defineProps({
    product: {
        type: Object,
        default: () => ({}),
    },
    branch: {
        type: Array,
        default: () => [],
    },
});
const form = useForm({
    name: props.product?.name ?? "",
    item_condition: props.product?.item_condition ?? null,
    link: props.product?.link ?? "",
    category: formatCategory(props.product?.category) ?? "",
    image: null,

    branch_prices: [],
});

const isEditMode = computed(() => {
    return !!props.product?.product_id
});
const previewImage = ref(null);

const branchOfficial = computed(() => {
    const official = props.branch?.find(b => b.status_official === 'official');
    return official ? official.branches_id : null;
})
const toggleBranch = (branchId) => {
    if (branchOfficial.value && branchId === branchOfficial.value) {
        return;
    }
    const index = form.branch_prices.findIndex(p => p.branch_id === branchId);
    if (index === -1) {
        form.branch_prices.push({
            branch_id: branchId,
            base_price: 0,
            discount_price: 0,
            valid_from: moment().format('YYYY-MM-DD'),
            valid_until: "",
            status: "",
        });
    } else {
        form.branch_prices.splice(index, 1);
    }
};
const getBranchName = (id) => props.branch.find(b => b.branches_id === id)?.name || 'Pusat';
const isSubmit = () => {
    const method = isEditMode.value ? "put" : "";
    const url = isEditMode.value
        ? route("product.update", props.product?.product_id)
        : route("product.store");

    form.post(url, {
        forceFormData: true,
        method: method,
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
};

const pageMeta = computed(() => {
    if (isEditMode.value) {
        return {
            title: "Ubah Produk " + props.product?.name,
            icon: "fas fa-edit",
            url: route("product"),
        };
    } else {
        return {
            title: "Produk Baru",
            icon: "fas fa-plus-square",
            url: route("product"),
        };
    }
})
const breadcrumbItems = computed(() => {
    const items = [
        { text: "Daftar Produk", url: route("product") },
    ];
    items.push({
        text: pageMeta.value.title,
        url: null,
    })
    return items;

});

const loaderActive = ref(null);

const goBack = () => {
    loaderActive.value?.show("Memproses...");
    router.get(
        pageMeta.value.url,
        {},
        {
            onFinish: () => loaderActive.value?.hide(),
        }
    );
};
// State untuk Mode Gambar (Default 'upload' agar user manual mudah)
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
    const base = Number(form.branch_prices[0].base_price);
    const disc = Number(form.branch_prices[0].discount_price);

    if (base > 0 && disc > 0 && disc < base) {
        const saving = base - disc;
        return Math.round((saving / base) * 100);
    }
    return 0;
});

// Fungsi ini penting agar gambar dari storage lokal dan link luar bisa tampil
const resolveImage = (path) => {
    if (!path) return "https://ui-avatars.com/api/?name=??";
    // Jika link eksternal (http/https)
    if (path.startsWith("http")) return path;

    // Jika file lokal, tambahkan '/' agar root terbaca
    // Pastikan path di DB kamu 'storage/...' atau sesuaikan disini
    return `/storage/${path}`;
};

// ON MOUNTED (LOGIKA UTAMA EDIT)
onMounted(() => {
    // A. SET PREVIEW GAMBAR UTAMA (COVER)

    if (props.product?.image_path) {
        previewImage.value = resolveImage(props.product?.image_path);
    } else if (props.product?.image_link) {
        previewImage.value = resolveImage(props.product?.image_link);
    }

    if (isEditMode.value && props.product?.prices?.length > 0) {
        // Mapping Array Prices ke Form
        form.branch_prices = props.product.prices.map((price) => {
            return {
                branch_id: price.branch_id,
                base_price: Number(price.base_price),
                discount_price: Number(price.discount_price),
                valid_from: price.valid_from,
                valid_until: price.valid_until,
                status: price.status
            }
        })
    }

});
const inputProduct = ref(null);
onMounted(() => {
    // Fokus ke input nama produk saat halaman dimuat
    inputProduct.value?.focus();
});

function formatCategory(cat = '') {
    return cat
        .split('/')                      // pecah sub kategori
        .map(part => part.replace(/-/g, ' '))  // ganti - dengan spasi
        .map(part => part.replace(/\b\w/g, char => char.toUpperCase())) // kapital
        .join(' - ');                    // gabungkan dengan pemisah cantik
}
</script>
<template>

    <Head :title="pageMeta.title" />
    <app-layout>
        <template #content>
            <loader-page ref="loaderActive" />
            <bread-crumbs :icon="pageMeta.icon" :title="pageMeta.title" :items="breadcrumbItems" />
            <div class="row pb-5">
                <div class="col-12">

                    <div
                        class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
                        <div class="d-flex align-items-center">
                            <div class="icon-box bg-white text-primary shadow-sm rounded-4 me-3 d-flex align-items-center justify-content-center"
                                style="width: 56px; height: 56px;">
                                <i class="fas fa-box-open fs-3"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold text-dark mb-1 ls-tight">Kelola Produk</h4>
                                <p class="text-muted small mb-0">Manajemen katalog produk dan penetapan harga.</p>
                            </div>
                        </div>
                        <Link @click.prevent="goBack" :href="route('product')"
                            class="btn btn-danger btn-sm ms-auto px-3 fw-bold">
                            <i class="fas fa-arrow-left me-2"></i> Kembali
                        </Link>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-body position-relative">
                            <loading-overlay :show="form.processing" text="Sedang memproses..." />
                            <form-wrapper @submit="isSubmit">
                                <div class="row g-4 p-3">
                                    <div class="col-lg-12 col-12 col-xl-4">
                                        <h6 class="fw-bold text-uppercase text-primary small ls-1 mb-2 px-2">
                                            <i class="fas fa-camera me-2"></i>Visual Produk
                                        </h6>
                                        <div
                                            class="ratio ratio-1x1 bg-light rounded-4 mb-3 border border-2 position-relative overflow-hidden group-hover-zoom">
                                            <img v-if="previewImage" :src="previewImage"
                                                class="object-fit-cover w-100 h-100 transition-transform" alt="Preview">

                                            <div v-else
                                                class="d-flex flex-column align-items-center justify-content-center text-muted h-100 opacity-50">
                                                <i class="bi bi-cloud-upload fs-1 mb-2"></i>
                                                <span class="small fw-semibold">Preview Foto</span>
                                            </div>

                                            <div
                                                class="position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-10 opacity-0 hover-opacity-100 transition-all pointer-events-none">
                                            </div>
                                        </div>
                                        <div class="text-center mb-3">
                                            <label
                                                class="btn btn-outline-primary border-dashed w-100 mb-2 cursor-pointer position-relative py-3 rounded-3 hover-bg-light">
                                                <div class="d-flex flex-column align-items-center">
                                                    <i class="fas fa-cloud-upload-alt fs-5 mb-1"></i>
                                                    <span class="fw-bold small">Pilih File Gambar</span>
                                                </div>
                                                <input type="file"
                                                    class="opacity-0 position-absolute w-100 h-100 top-0 start-0 cursor-pointer"
                                                    @change="handleFileUpload"
                                                    accept="image/png,image/jpeg,image/jpg,image/webp,image/svg">
                                            </label>

                                            <div class="d-flex justify-content-between align-items-start mt-2">
                                                <div class="small text-muted fst-italic text-start"
                                                    style="font-size: 0.7rem;">
                                                    JPG/PNG/WEBP/SVG, Max 2MB.
                                                </div>
                                                <a v-if="props.product.product?.image_link"
                                                    :href="props.product.product?.link" target="_blank"
                                                    class="badge bg-light text-secondary border text-decoration-none">
                                                    <i class="fas fa-link me-1"></i> Sumber
                                                </a>
                                            </div>
                                            <input-error :message="form.errors.image" class="text-start" />
                                        </div>



                                    </div>
                                    <div class="col-lg-12 col-12 col-xl-8">
                                        <h6 class="fw-bold text-uppercase text-primary small ls-1 mb-2">
                                            <i class="fas fa-info-circle me-2"></i>Informasi Dasar
                                        </h6>
                                        <div class="p-3 border rounded-3 overflow-hidden mb-3">
                                            <div class="mb-2">
                                                <input-label for="name" class="form-label-custom" value="Nama Produk" />
                                                <text-input ref="inputProduct" placeholder="Nama produk" name="name"
                                                    v-model="form.name"
                                                    input-class="border-0 border-bottom input-height-1 fw-bold rounded-0 p-0 pe-5" />
                                                <input-error :message="form.errors.name" />
                                            </div>
                                            <div class="mb-2">
                                                <input-label for="category" class="form-label-custom"
                                                    value="Kategori" />
                                                <text-input placeholder="Contoh: Mesin, Sparepart dll"
                                                    input-class="border-0 border-bottom input-height-1 fw-bold rounded-0 p-0 pe-5"
                                                    name="category" v-model="form.category" />
                                                <input-error :message="form.errors.category" />
                                            </div>


                                            <div class="mb-2">
                                                <input-label for="item_condition" class="form-label-custom"
                                                    value="Kondisi Produk" />
                                                <select-input :options="[
                                                    { label: '---Pilih Kondisi Produk---', value: null },
                                                    { label: 'Baru', value: 'new' },
                                                    { label: 'Bekas', value: 'used' },
                                                    { label: 'Diperbaharui', value: 'refurbished' },
                                                    { label: 'Rusak', value: 'damaged' },
                                                    { label: 'Dihentikan', value: 'discontinued' }
                                                ]" name="item_condition" v-model="form.item_condition"
                                                    select-class="border-bottom input-height-1 border-0 rounded-0 p-2" />
                                                <input-error :message="form.errors.item_condition" />
                                            </div>


                                            <div class="mb-2">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <input-label for="link" class="form-label-custom"
                                                        value="Link Marketplace" />
                                                    <span
                                                        class="badge bg-light text-secondary border fw-normal mb-2">Opsional</span>
                                                </div>
                                                <div class="position-relative">
                                                    <text-input placeholder="https://shopee/..." name="link"
                                                        v-model="form.link"
                                                        input-class="border-0 border-bottom input-height-1 rounded-0 p-0 pe-5" />
                                                </div>
                                                <input-error :message="form.errors.link" />
                                            </div>
                                        </div>

                                        <div class="p-3 border rounded-3 overflow-hidden mb-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="fw-bold mb-3"><i class="fas fa-store me-2"></i>Pilih Cabang
                                                </h6>
                                                <div class="badge bg-primary">Cabang terpilih: {{
                                                    form.branch_prices.length }}</div>
                                            </div>
                                            <div class="d-flex flex-wrap gap-2">
                                                <button :disabled="b.branches_id === branchOfficial" v-for="b in branch"
                                                    :key="b.branches_id" type="button"
                                                    @click="toggleBranch(b.branches_id)"
                                                    class="btn btn-sm btn-height-1 px-4 rounded-2 transition-all text-capitalize"
                                                    :class="form.branch_prices.some(p => p.branch_id === b.branches_id) ? 'btn-primary' : 'btn-outline-light text-dark border'">
                                                    <i v-if="b.branches_id === branchOfficial"
                                                        class="fas fa-lock me-1 text-warning"></i>
                                                    {{ b.name }}
                                                </button>
                                            </div>
                                            <input-error :message="form.errors.branch_prices" />
                                        </div>

                                        <div class="branch-prices-section border-0 border-top pt-3">

                                            <div v-if="!form.branch_prices.length"
                                                class="empty-state text-center py-5 card  rounded-4 shadow-sm bg-white">
                                                <div class="py-5">
                                                    <i class="fas fa-store-slash fa-4x text-light mb-3"></i>
                                                    <h5 class="text-muted">Belum ada yang cabang dipilih</h5>
                                                </div>
                                            </div>

                                            <div v-for="(priceItem, index) in form.branch_prices"
                                                :key="priceItem.branch_id"
                                                class="card shadow-sm rounded-4 mb-4 pricing-card ">
                                                <div class="card-body p-4">
                                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                                        <div class="d-flex align-items-center">
                                                            <div class="icon-branch me-3">
                                                                <i class="fas fa-map-marked-alt text-primary"></i>
                                                            </div>
                                                            <div>
                                                                <h5 class="fw-bold mb-0 text-capitalize">{{
                                                                    getBranchName(priceItem.branch_id) + " - " +
                                                                    `(${form.name})` }}</h5>
                                                            </div>
                                                        </div>
                                                        <button v-if="branchOfficial !== priceItem.branch_id"
                                                            type="button" @click="toggleBranch(priceItem.branch_id)"
                                                            class="btn btn-link text-danger text-decoration-none">
                                                            <i class="fas fa-trash-alt me-1"></i> Hapus
                                                        </button>
                                                    </div>

                                                    <div class="row g-4">
                                                        <div class="col-md-6">
                                                            <div
                                                                class="input-group-custom p-3 rounded-4 bg-light border border-2 border-transparent">
                                                                <label :for="`branch_prices.${index}.base_price`"
                                                                    class="text-muted small fw-bold text-uppercase d-block mb-1">Harga
                                                                    Normal</label>
                                                                <div class="d-flex align-items-center">
                                                                    <currency-input
                                                                        input-class="bg-transparent border-0 fs-5 fw-bold text-dark shadow-none w-100"
                                                                        :decimals="0" v-model="priceItem.base_price"
                                                                        :name="`branch_prices.${index}.base_price`"
                                                                        placeholder="0" />
                                                                </div>
                                                            </div>
                                                            <input-error
                                                                :message="form.errors[`branch_prices.${index}.base_price`]" />
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div
                                                                class="input-group-custom p-3 rounded-4 bg-rose-soft border border-2 border-transparent">


                                                                <div
                                                                    class="d-flex align-items-center justify-content-between">
                                                                    <label
                                                                        :for="`branch_prices.${index}.discount_price`"
                                                                        class="text-danger small fw-bold text-uppercase d-block mb-1">Harga
                                                                        Promo (Diskon)</label>
                                                                    <transition name="fade">
                                                                        <span v-if="discountPercentage > 0"
                                                                            class="badge bg-danger rounded-pill shadow-sm animate-pulse px-3 border-0">
                                                                            <i class="fas fa-fire me-1"></i> Diskon {{
                                                                                discountPercentage
                                                                            }}%
                                                                        </span>
                                                                    </transition>
                                                                </div>
                                                                <div class="d-flex align-items-center">
                                                                    <currency-input
                                                                        input-class="bg-transparent border-0 fs-5 fw-bold text-danger shadow-none w-100"
                                                                        :decimals="0" v-model="priceItem.discount_price"
                                                                        :name="`branch_prices.${index}.discount_price`"
                                                                        placeholder="0" />
                                                                </div>
                                                            </div>
                                                            <input-error
                                                                :message="form.errors[`branch_prices.${index}.discount_price`]" />
                                                        </div>

                                                        <div class="col-md-6">
                                                            <input-label :for="`branch_prices.${index}.valid_from`"
                                                                class="mb-2 fw-bold text-secondary text-xs text-uppercase"
                                                                value="Tanggal Berlaku" />
                                                            <div class="position-relative">
                                                                <i class="far fa-calendar-alt input-icon-left"></i>
                                                                <text-input type="date" v-model="priceItem.valid_from"
                                                                    :name="`branch_prices.${index}.valid_from`"
                                                                    :input-class="['input-fixed-height',
                                                                        priceItem.valid_from || form.errors[`branch_prices.${index}.valid_from`] ? 'pe-5' : 'pe-3'
                                                                    ]" />
                                                            </div>
                                                            <input-error
                                                                :message="form.errors[`branch_prices.${index}.valid_from`]" />
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div
                                                                class="align-items-center d-flex justify-content-between">
                                                                <input-label :for="`branch_prices.${index}.valid_until`"
                                                                    class="form-label-custom"
                                                                    value="Tanggal Berakhir" />
                                                                <span
                                                                    class="badge bg-light text-muted border fw-normal mb-2"
                                                                    style="font-size: 0.7rem;">OPSIONAL</span>
                                                            </div>
                                                            <div class="position-relative">
                                                                <i class="far fa-calendar-alt input-icon-left"></i>
                                                                <text-input type="date" v-model="priceItem.valid_until"
                                                                    :name="`branch_prices.${index}.valid_until`"
                                                                    :input-class="['input-fixed-height',
                                                                        priceItem.valid_until || form.errors[`branch_prices.${index}.valid_until`] ? 'pe-5' : 'pe-3'
                                                                    ]" />
                                                            </div>
                                                            <input-error
                                                                :message="form.errors[`branch_prices.${index}.valid_until`]" />
                                                        </div>

                                                        <div class="col-12">
                                                            <input-label :for="`branch_prices.${index}.status`"
                                                                class="form-label-custom" value="Status Publikasi" />
                                                            <div class="d-flex gap-3 mt-2">

                                                                <div class="p-2 rounded-2 px-4 border"
                                                                    :class="priceItem.status === 'draft' ? 'border-primary bg-primary bg-opacity-10 text-primary' : 'text-muted'">
                                                                    <radio-box v-model:checked="priceItem.status"
                                                                        value="draft"
                                                                        :name="`branch_prices.${index}.status`">
                                                                        <i class="fas fa-lock me-1 ms-2"></i>
                                                                        Simpan sebagai Draft
                                                                    </radio-box>
                                                                </div>

                                                                <div class="p-2 rounded-2 px-4 border "
                                                                    :class="priceItem.status === 'published' ? 'border-primary bg-primary bg-opacity-10 text-primary' : 'text-muted'">
                                                                    <radio-box v-model:checked="priceItem.status"
                                                                        value="published"
                                                                        :name="`branch_prices.${index}.status`">
                                                                        <i class="fas fa-globe me-1 ms-2"></i>
                                                                        Terbitkan Sekarang
                                                                    </radio-box>
                                                                </div>
                                                            </div>
                                                            <input-error
                                                                :message="form.errors[`branch_prices.${index}.status`]" />
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                </div>
                            </form-wrapper>
                            <div class="d-grid d-xl-flex justify-content-xl-end gap-2">
                                <Link @click.prevent="goBack" :href="route('product')"
                                    class="btn btn-light  text-decoration-none fw-bold text-muted order-last order-xl-0">
                                    Batal & Kembali
                                </Link>
                                <base-button @click.prevent="isSubmit" waiting="Memproses...."
                                    :loading="form.processing" :button-class="['btn shadow-lg fw-bold px-5 text-nowrap',
                                        isEditMode ? 'btn-success' : 'btn-primary']" type="submit"
                                    :icon="isEditMode ? 'fas fa-check-circle me-2' : 'fas fa-plus-circle me-2'"
                                    :label="isEditMode ? 'Simpan perubahan' : 'Tambah Produk'" />
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </template>
    </app-layout>
</template>

<style scoped>
.form-label-custom {
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    color: #6c757d;
    text-transform: uppercase;
}

.hover-scale {
    transition: transform 0.2s;
}

.hover-scale:hover {
    transform: scale(1.05);
}

.pe-5 {
    padding-right: 2rem !important;
}

/* GENERAL UTILS */
.ls-tight {
    letter-spacing: -0.5px;
}

.ls-1 {
    letter-spacing: 1px;
}

.text-xs {
    font-size: 0.75rem;
}

/* HOVER EFFECTS */
.hover-lift {
    transition: transform 0.2s;
}

.hover-lift:hover {
    transform: translateY(-2px);
}

.hover-scale {
    transition: transform 0.2s;
}

.hover-scale:hover {
    transform: scale(1.02);
}

.hover-bg-light:hover {
    background-color: #f5f4f4;
    border-color: #0d6efd;
    color: #818181;
}

/* IMAGE PREVIEW BOX */
.group-hover-zoom:hover img {
    transform: scale(1.05);
}

.hover-opacity-100:hover {
    opacity: 1 !important;
}

.transition-transform {
    transition: transform 0.5s ease;
}

.transition-all {
    transition: all 0.3s ease;
}

.border-dashed {
    border-style: dashed !important;
    border-width: 2px;
}

/* INPUT STYLES */
.form-label-custom {
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    color: #6c757d;
    text-transform: uppercase;
}

/* Custom Gradient Background for Header Card */
.bg-gradient-primary-soft {
    background: linear-gradient(135deg, rgba(13, 110, 253, 0.05) 0%, rgba(13, 110, 253, 0.0) 100%);
}

/* SELECTION CARDS (BRANCHES) */
.select-card {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    border: 2px solid transparent;
}

.select-card:hover {
    border-color: #dee2e6;
    transform: translateY(-2px);
}

/* When Selected */
.btn-check:checked+.select-card {
    border-color: var(--bs-primary) !important;
    background-color: rgba(13, 110, 253, 0.05) !important;
    box-shadow: 0 4px 6px -1px rgba(13, 110, 253, 0.1);
}

/* ANIMATIONS */
.animate-pulse {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4);
    }

    70% {
        box-shadow: 0 0 0 6px rgba(220, 53, 69, 0);
    }

    100% {
        box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
    }
}

/* Pointer Events Helper */
.branch-prices-section {
    pointer-events: auto;
    max-height: 550px;
    overflow-y: auto;
    overflow-x: hidden;

}

.pointer-events-none {
    pointer-events: none;
}

.cursor-pointer {
    cursor: pointer;
}

/* Card Styling */
.pricing-card {
    border-left: 5px solid #0d6efd !important;
    transition: 0.3s;
}

.icon-branch {
    width: 48px;
    height: 48px;
    background: rgba(13, 110, 253, 0.1);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}
</style>
