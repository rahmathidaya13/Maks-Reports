<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { Head, Link, router, useForm, usePage } from "@inertiajs/vue3";
const props = defineProps({
    transaction: Object,
    customer: Object,
    product: Object
})

// form fields
const form = useForm({
    invoice: props.transaction?.invoice ?? '',
    customer_id: props.transaction?.customer?.customer_id ?? '',
    product_id: props.transaction?.product?.product_id ?? '',
    price_original: props.transaction?.price_original ?? 0,
    price_discount: props.transaction?.price_discount ?? 0,
    payment_type: props.transaction?.payments[0].payment_type ?? null,
    payment_method: props.transaction?.payments[0].payment_method ?? null,
    amount: props.transaction?.payments[0].amount ?? 0,
});
const priceFinal = computed(() => {
    const original = Number(form.price_original || 0)
    const discount = Number(form.price_discount || 0)
    return Math.max(original - discount, 0)
})
// minimal dana pertama
const dpAmount = computed(() => {
    return priceFinal.value * 0.5
})
const paymentInfoText = computed(() => {
    if (!priceFinal.vsalue) return null

    if (form.payment_type === 'payment') {
        return `Dana pertama (DP) minimal 50% dari harga akhir.`
    }

    if (form.payment_type === 'repayment') {
        return `Pembayaran lunas sesuai harga akhir setelah diskon.`
    }

    return null
})
const isSubmit = () => {
    if (props.transaction?.transaction_id) {
        form.put(route('transaction.update', props.transaction?.transaction_id), {
            onSuccess: () => {
                form.reset();
            },
        })
    } else {
        // Create
        form.post(route('transaction.store'), {
            onSuccess: () => {
                form.reset();
            }
        });
    }
};

const customerOptions = computed(() => {
    return props.customer?.map((value) => ({
        id: value.customer_id,
        text: value.customer_name
    }))
})
const productOptions = computed(() => {
    return props.product?.map((value) => ({
        id: value.product_id,
        text: value.name
    }))
})
// end form fields

// breandcrumb fields
const title = ref("");
const icon = ref("");
const url = ref("")
onMounted(() => {
    if (props.transaction && props.transaction?.transaction_id) {
        title.value = "Ubah Data Transaksi - " + props.transaction?.customer.customer_name
        icon.value = "fas fa-edit"
        url.value = route('transaction')
    } else {
        title.value = "Buat Transaksi Baru"
        icon.value = "fas fa-plus-square"
        url.value = route('transaction')
    }
})
const breadcrumbItems = computed(() => {
    if (props.transaction && props.transaction?.transaction_id) {
        return [
            { text: "Daftar Transaksi", url: route("transaction") },
            { text: "Buat Transaksi Baru", url: route("transaction.create") },
            { text: title.value }
        ]
    }
    return [
        { text: "Daftar Transaksi", url: route("transaction") },
        { text: title.value }
    ]
})
// breadcrumb fields edn

// splash loader screen
const loaderActive = ref(null);
const goBack = () => {
    loaderActive.value?.show("Memproses...");
    router.get(url.value, {}, {
        onFinish: () => loaderActive.value?.hide()
    });
}
// splash loader screen end

// autofocus
// const inputRef = ref(null);
// onMounted(() => {
//     inputRef.value.focus();
// })
// end autofocus

function formatCurrency(value) {
    if (!value) return "0";
    return new Intl.NumberFormat('id-ID', {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value)
}
</script>
<template>

    <Head :title="title" />
    <app-layout>
        <template #content>
            <loader-page ref="loaderActive" />
            <bread-crumbs :icon="icon" :title="title" :items="breadcrumbItems" />
            <div class="d-flex justify-content-between">
                <Link @click.prevent="goBack" :href="url" class="btn btn-danger mb-3">
                    <i class="fas fa-arrow-left"></i>
                    Kembali
                </Link>
            </div>
            <div class="row g-4 pb-3">

                <div class="col-xl-8 col-lg-8 col-12">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                        <div class="card-header bg-white py-3 px-4 border-bottom">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3 me-3">
                                    <i class="fas fa-file-invoice fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-uppercase ls-1">Form Transaksi</h6>
                                    <small class="text-muted">Isi detail transaksi pelanggan di bawah ini</small>
                                </div>
                            </div>
                        </div>

                        <div v-if="form.processing"
                            class="position-absolute w-100 h-100 bg-white opacity-75 d-flex align-items-center justify-content-center"
                            style="z-index: 10;">
                            <loader-horizontal
                                :message="props.transaction?.transaction_id ? 'Memperbarui...' : 'Menyimpan...'" />
                        </div>

                        <div class="card-body p-4">
                            <form-wrapper @submit="isSubmit">

                                <h6 class="text-primary fw-bold mb-3 small text-uppercase">
                                    <i class="fas fa-user-tag me-1"></i> Informasi Dasar
                                </h6>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <input-label class="fw-bold small" for="invoice" value="Nomor Invoice" />
                                        <div class="position-relative">
                                            <i class="fas fa-hashtag input-icon-left"></i>
                                            <text-input input-class="input-fixed-height" name="invoice"
                                                v-model="form.invoice" />
                                        </div>
                                        <input-error :message="form.errors.invoice" />
                                    </div>
                                    <div class="col-md-6">
                                        <input-label class="fw-bold small" for="customer_name" value="Pelanggan" />
                                        <select-2
                                            :settings="{ width: '100%', placeholder: 'Cari Pelanggan...', allowClear: true }"
                                            name="customer_name" :options="customerOptions"
                                            v-model="form.customer_id" />
                                        <input-error :message="form.errors.customer_id" />
                                    </div>
                                </div>

                                <hr class="border-dashed my-4 opacity-50">

                                <h6 class="text-primary fw-bold mb-3 small text-uppercase">
                                    <i class="fas fa-box-open me-1"></i> Detail Produk
                                </h6>

                                <div class="mb-3">
                                    <input-label class="fw-bold small" for="product_name" value="Pilih Produk" />
                                    <select-2
                                        :settings="{ width: '100%', placeholder: 'Cari Produk...', allowClear: true }"
                                        name="product_name" :options="productOptions" v-model="form.product_id" />
                                    <input-error :message="form.errors.product_id" />
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <input-label class="fw-bold small" for="price_original"
                                            value="Harga Satuan (Asli)" />
                                        <currency-input :decimals="0" v-model="form.price_original"
                                            name="price_original" />
                                        <input-error :message="form.errors.price_original" />
                                    </div>
                                    <div class="col-md-6">
                                        <input-label class="fw-bold small" for="price_discount"
                                            value="Potongan / Diskon" />
                                        <currency-input :decimals="0" name="price_discount"
                                            v-model="form.price_discount" />
                                        <input-error :message="form.errors.price_discount" />
                                    </div>
                                </div>

                                <hr class="border-dashed my-4 opacity-50">

                                <h6 class="text-primary fw-bold mb-3 small text-uppercase">
                                    <i class="fas fa-wallet me-1"></i> Skema Pembayaran
                                </h6>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <input-label class="fw-bold small" for="payment_type"
                                            value="Jenis Pembayaran" />
                                        <select-input name="payment_type" :options="[
                                            { value: null, label: '— Pilih Opsi —' },
                                            { value: 'payment', label: 'DP / Cicilan (50%)' },
                                            { value: 'repayment', label: 'Lunas Langsung' },
                                        ]" v-model="form.payment_type" />
                                        <input-error :message="form.errors.payment_type" />
                                    </div>

                                    <div class="col-md-6">
                                        <input-label class="fw-bold small" for="payment_method" value="Metode Bayar" />
                                        <select-input name="payment_method" :options="[
                                            { value: null, label: '— Pilih Metode —' },
                                            { value: 'cash', label: 'Tunai' },
                                            { value: 'transfer', label: 'Transfer Bank' },
                                            { value: 'debit', label: 'Debit Card' }
                                        ]" v-model="form.payment_method" />
                                        <input-error :message="form.errors.payment_method" />
                                    </div>

                                    <div class="col-12" v-if="form.payment_type === 'payment'">
                                        <div
                                            class="bg-warning bg-opacity-10 p-3 rounded-3 border border-warning border-opacity-25 mt-2">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <label class="fw-bold text-warning-emphasis mb-0">Nominal DP (Down
                                                    Payment)</label>
                                                <span class="badge bg-warning text-dark">Wajib Min. 50%</span>
                                            </div>
                                            <currency-input :decimals="0" v-model="form.amount" name="amount"
                                                placeholder="Input nominal DP..." />
                                            <input-error :message="form.errors.amount" />

                                            <div class="mt-2 small text-muted">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Minimal DP yang disarankan: <strong>{{ formatCurrency(dpAmount)
                                                    }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-block d-lg-none mt-4">
                                    <base-button :loading="form.processing" class="w-100 btn-lg rounded-3"
                                        :variant="props.transaction?.transaction_id ? 'success' : 'primary'"
                                        type="submit"
                                        :label="props.transaction?.transaction_id ? 'Simpan Perubahan' : 'Buat Transaksi'" />
                                </div>

                            </form-wrapper>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-lg-4 col-12">
                    <div class="sticky-top" style="top: 20px; z-index: 1;">
                        <div class="card border-0 shadow-sm rounded-4 bg-light bg-gradient">
                            <div class="card-body p-4">
                                <h6 class="fw-bold text-dark mb-4 text-uppercase ls-1 border-bottom pb-2">
                                    Ringkasan Biaya
                                </h6>

                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted">Harga Produk</span>
                                    <span class="fw-semibold">{{ formatCurrency(form.price_original || 0) }}</span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-danger small"><i
                                            class="fas fa-minus-circle me-1"></i>Diskon</span>
                                    <span class="text-danger fw-semibold">- {{ formatCurrency(form.price_discount || 0)
                                        }}</span>
                                </div>

                                <div class="bg-white p-3 rounded-3 shadow-sm border mb-4">
                                    <div class="text-center">
                                        <small class="text-uppercase text-muted fw-bold ls-1"
                                            style="font-size: 0.7rem;">Total Tagihan Akhir</small>
                                        <h2 class="text-primary fw-bolder mb-0 mt-1">
                                            {{ formatCurrency(priceFinal) }}
                                        </h2>
                                    </div>
                                </div>

                                <div v-if="form.payment_type" class="alert alert-light border shadow-sm mb-4">
                                    <div v-if="form.payment_type === 'payment'">
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span>Status:</span>
                                            <span class="badge bg-warning text-dark">Kredit / DP</span>
                                        </div>
                                        <div class="d-flex justify-content-between small fw-bold">
                                            <span>Bayar Sekarang (DP):</span>
                                            <span class="text-dark">{{ formatCurrency(form.amount || 0) }}</span>
                                        </div>
                                        <div
                                            class="d-flex justify-content-between small mt-1 text-muted border-top pt-1">
                                            <span>Sisa Pelunasan:</span>
                                            <span>{{ formatCurrency(priceFinal - (form.amount || 0)) }}</span>
                                        </div>
                                    </div>
                                    <div v-else-if="form.payment_type === 'repayment'">
                                        <div class="d-flex justify-content-between small">
                                            <span>Status:</span>
                                            <span class="badge bg-success">Lunas</span>
                                        </div>
                                        <div class="mt-1 text-center small text-success fw-bold">
                                            Pembayaran Penuh
                                        </div>
                                    </div>
                                </div>

                                <div class="d-grid gap-2">
                                    <base-button :loading="form.processing" class="rounded-3 py-3 fw-bold shadow-sm"
                                        :class="props.transaction?.transaction_id ? 'btn-success' : 'btn-primary'"
                                        :icon="props.transaction?.transaction_id ? 'fas fa-save' : 'fas fa-paper-plane'"
                                        type="button" @click="isSubmit"
                                        :label="props.transaction?.transaction_id ? 'Simpan Perubahan' : 'Proses Transaksi'" />

                                    <button @click.prevent="goBack" type="button"
                                        class="btn btn-link text-decoration-none text-muted btn-sm">
                                        Batal & Kembali
                                    </button>
                                </div>

                                <div class="mt-3 small text-center text-muted fst-italic" v-if="paymentInfoText">
                                    <i class="fas fa-info-circle me-1"></i> {{ paymentInfoText }}
                                </div>

                            </div>
                        </div>
                    </div>
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
</style>
