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
    payment_type: props.transaction?.payments[0].payment_type ?? '',
    payment_method: props.transaction?.payments[0].payment_method ?? null,
    amount: props.transaction?.payments[0].amount ?? 0,
});
console.log(props.transaction)
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
            <div class="row">
                <div class="col-xl-12 col-sm-12 pb-3">
                    <div class="card overflow-hidden rounded-3 shadow-sm">
                        <h5 class="card-header fw-bold text-uppercase p-3 text-bg-dark">
                            <i class="fas fa-info-circle me-1 text-light"></i>
                            Form Transaksi
                        </h5>
                        <div v-if="form.processing">
                            <loader-horizontal
                                :message="props.transaction?.transaction_id ? 'Sedang memperbarui data' : 'Sedang menyimpan data'" />
                        </div>
                        <div class="card-body" :class="['blur-area', form.processing ? 'is-blurred' : '']">
                            <form-wrapper @submit="isSubmit">
                                <div class="row">
                                    <div class="col-xl-6">
                                        <div class="mb-3">
                                            <input-label class="fw-bold" for="invoice" value="Invoice" />
                                            <text-input v-model="form.invoice" name="invoice" />
                                            <input-error :message="form.errors.invoice" />
                                        </div>
                                        <div class="mb-3">
                                            <input-label class="fw-bold" for="customer_name" value="Nama Pelanggan" />
                                            <select-2 :settings="{
                                                width: '100%',
                                                placeholder: 'Pilih Nama Pelanggan',
                                                allowClear: true,
                                            }" name="customer_name" :options="customerOptions"
                                                v-model="form.customer_id" />
                                            <input-error :message="form.errors.customer_id" />
                                        </div>

                                        <div class="mb-3">
                                            <input-label class="fw-bold" for="product_name" value="Produk/Barang" />
                                            <select-2 :settings="{
                                                width: '100%',
                                                placeholder: 'Pilih Produk/Barang',
                                                allowClear: true,
                                            }" name="product_name" :options="productOptions"
                                                v-model="form.product_id" />
                                            <input-error :message="form.errors.product_id" />
                                        </div>

                                        <div class="mb-3">
                                            <input-label class="fw-bold" for="payment_type" value="Jenis Pembayaran" />
                                            <select-input name="payment_type" :options="[
                                                { value: null, label: 'Pilih Jenis Pembayaran' },
                                                { value: 'payment', label: 'Dana Pertama(50%)' },
                                                { value: 'repayment', label: 'Lunas' },
                                            ]" v-model="form.payment_type" />
                                            <input-error :message="form.errors.payment_type" />
                                        </div>



                                        <div class="text-bg-light p-2 mb-3 rounded-3 border">
                                            <div class="mb-2">
                                                <input-label class="fw-bold" for="price_original"
                                                    value="Harga Produk" />
                                                <currency-input :decimals="0" v-model="form.price_original"
                                                    name="price_original" />
                                                <input-error :message="form.errors.price_original" />
                                            </div>

                                            <div class="mb-2">
                                                <input-label class="fw-bold" for="price_discount"
                                                    value="Harga Diskon" />
                                                <currency-input :decimals="0" name="price_discount"
                                                    v-model="form.price_discount" />
                                                <input-error :message="form.errors.price_discount" />
                                            </div>

                                            <div class="mb-2" v-if="form.payment_type === 'payment'">
                                                <input-label class="fw-bold" for="amount" value="Dana Pertama (DP)" />
                                                <currency-input :decimals="0" v-model="form.amount" name="amount" />
                                                <input-error :message="form.errors.amount" />
                                            </div>

                                            <div class="mt-2 fw-semibold">
                                                <div v-if="form.payment_type === 'payment'">
                                                    Harga Akhir: <strong>{{ formatCurrency(priceFinal) }}</strong><br>
                                                    DP Minimal (50%): <strong>{{ formatCurrency(dpAmount) }}</strong>
                                                </div>

                                                <div v-else-if="form.payment_type === 'repayment'">
                                                    Harga Akhir (Lunas): <strong>{{ formatCurrency(priceFinal)
                                                        }}</strong>
                                                </div>
                                            </div>

                                            <!-- Callout Info -->
                                            <div class="callout callout-info rounded-0 mt-3" v-if="paymentInfoText">
                                                <h6 class="fw-bold">
                                                    <i class="fas fa-info-circle me-2"></i>Informasi
                                                </h6>
                                                <ul class="mb-0 ps-3">
                                                    <li>{{ paymentInfoText }}</li>
                                                </ul>
                                            </div>

                                        </div>

                                        <div class="mb-3">
                                            <input-label class="fw-bold" for="payment_method"
                                                value="Metode Pembayaran" />
                                            <select-input name="payment_method" :options="[
                                                { value: null, label: 'Pilih Metode Pembayaran' },
                                                { value: 'cash', label: 'Cash' },
                                                { value: 'transfer', label: 'Transfer' },
                                                { value: 'debit', label: 'Debit' }
                                            ]" v-model="form.payment_method" />
                                            <input-error :message="form.errors.payment_method" />
                                        </div>
                                    </div>
                                    <div class="col-xl-4 border rounded-3 p-3 position-relative text-bg-grey">
                                        <p>seejkfbsdjk</p>
                                    </div>
                                </div>
                                <div class="d-grid mb-3 col-xl-6">
                                    <base-button :loading="form.processing"
                                        class="rounded-3 bg-gradient px-5 btn-height-2"
                                        :icon="props.transaction?.transaction_id && props.transaction?.transaction_id ? 'fas fa-edit' : 'fas fa-save'"
                                        :variant="props.transaction?.transaction_id && props.transaction?.transaction_id ? 'success' : 'primary'"
                                        type="submit"
                                        :name="props.transaction?.transaction_id && props.transaction?.transaction_id ? 'ubah' : 'simpan'"
                                        :label="props.transaction?.transaction_id && props.transaction?.transaction_id ? 'Ubah' : 'Simpan'" />
                                </div>
                            </form-wrapper>
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
