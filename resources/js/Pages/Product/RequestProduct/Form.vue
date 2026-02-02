<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { Head, Link, router, useForm, usePage } from "@inertiajs/vue3";
const props = defineProps({
    product: Array,
});

const form = useForm({
    product_id: null,
    current_price: 0,
    requested_price: 0,
    reason: '',
});

const selectedProduct = ref(null);
watch(() => form.product_id, (newId) => {
    if (newId) {
        selectedProduct.value = props.product.find(p => p.product_id === newId);
    } else {
        selectedProduct.value = null;
    }
});

function formatCurrency(value) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
}

const submit = () => {
    form.post(route('product.request.store'), {
        onSuccess: () => form.reset(),
    });
};

const productOptions = computed(() => {
    return props.product?.map((value) => ({
        id: value.product_id,
        text: value.name
    }))
})
const title = ref("");
const icon = ref("");
const url = ref("");
onMounted(() => {
    title.value = "Buat Permintaan";
    icon.value = "fas fa-plus-square";
    url.value = route("product");
});
const breadcrumbItems = computed(() => {
    return [{ text: "Daftar Produk", url: route("product") }, { text: title.value }];
});

</script>
<template>

    <Head :title="title" />
    <app-layout>
        <template #content>
            <loader-page ref="loaderActive" />
            <bread-crumbs :icon="icon" :title="title" :items="breadcrumbItems" />

            <div class="row pb-5">
                <div class="col-12">

                    <div
                        class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
                        <div class="d-flex align-items-center">
                            <div>
                                <h4 class="fw-bold text-dark mb-1">Buat Permintaan Harga/ Penyesuaian</h4>
                                <p class="text-muted small mb-0">Ajukan permintaan khusus ke admin.</p>
                            </div>
                        </div>
                        <Link :href="route('product')" class="btn btn-danger btn-sm ms-auto px-3 fw-bold">
                            <i class="fas fa-arrow-left me-2"></i> Kembali
                        </Link>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-body p-4 p-md-5">

                            <form-wrapper @submit="submit">

                                <div class="mb-4">
                                    <label class="form-label fw-bold text-dark small mb-2">Pilih Produk</label>
                                    <select-2
                                        :settings="{ width: '100%', placeholder: 'Cari Produk...', allowClear: true }"
                                        name="product_id" :options="productOptions" v-model="form.product_id" />
                                    <input-error :message="form.errors.product_id" />
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-12">
                                        <label class="form-label fw-bold text-dark small mb-2">Harga Saat ini</label>
                                        <div class="input-group">
                                            <currency-input input-class="form-control-lg" :disabled="!selectedProduct"
                                                :decimals="0" v-model="form.current_price" name="current_price"
                                                placeholder="0" />
                                        </div>
                                        <input-error :message="form.errors.current_price" />
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold text-dark small mb-2">Harga Yang
                                            Diajukan</label>
                                        <div class="input-group">
                                            <currency-input input-class="form-control-lg" :disabled="!selectedProduct"
                                                :decimals="0" v-model="form.requested_price" name="requested_price"
                                                placeholder="0" />
                                        </div>
                                        <div class="form-text text-xs text-muted" v-if="selectedProduct">
                                            Selisih: <span
                                                :class="form.requested_price < selectedProduct.prices[0].base_price ? 'text-success' : 'text-danger'">
                                                {{ formatCurrency(form.requested_price -
                                                    form.current_price) }}
                                            </span>
                                        </div>
                                        <input-error :message="form.errors.requested_price" />
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold text-dark small mb-2">Keterangan / Alasan <span
                                            class="text-danger">*</span></label>
                                    <text-area :maxChar="300" name="reason" v-model="form.reason" :rows="5"
                                        placeholder="Contoh: Minta diskon spesial atau inputkan produk dll..." />
                                    <input-error :message="form.errors.reason" />
                                </div>

                                <div class="d-grid">
                                    <button class="btn btn-primary btn-lg rounded-3 fw-bold shadow-sm"
                                        :disabled="form.processing">
                                        <i class="fas fa-paper-plane me-2"></i> Kirim Permintaan
                                    </button>
                                </div>

                            </form-wrapper>

                        </div>
                    </div>

                    <div class="mt-4 text-center">
                        <p class="text-muted small">
                            <i class="fas fa-info-circle me-1"></i>
                            Permintaan Anda akan masuk ke dashboard admin dan notifikasi akan muncul jika disetujui.
                        </p>
                    </div>
                </div>
            </div>
        </template>
    </app-layout>
</template>
>
