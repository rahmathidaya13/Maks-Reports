<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { Head, Link, router, useForm, usePage } from "@inertiajs/vue3";
const props = defineProps({
    transaction: Object,
})

// form fields
const form = useForm({
    payment_method: null,
});

// ===== PERHITUNGAN =====
const totalPaid = computed(() => {
    return props.transaction.payments?.reduce((sum, p) => sum + p.amount, 0) ?? 0
})

const remaining = computed(() => {
    return Math.max(props.transaction.price_final - totalPaid.value, 0)
})

const canSubmit = computed(() => {
    return remaining.value > 0 && form.payment_method
})

const isSubmit = () => {
    if (!canSubmit.value) return
    form.post(route('transaction.settle', props.transaction?.transaction_id), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    })
};
// end form fields
// breandcrumb fields
const title = ref("")
const icon = ref("fas fa-money-bill-wave")
const url = ref(route('transaction'))
onMounted(() => {
    title.value = `Pelunasan Transaksi - ${props.transaction.customer.customer_name}`
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
                            Form Pelunasan Transaksi
                        </h5>
                        <div v-if="form.processing">
                            <loader-horizontal
                                :message="props.transaction?.transaction_id ? 'Sedang memperbarui data' : 'Sedang menyimpan data'" />
                        </div>
                        <div class="card-body" :class="['blur-area', form.processing ? 'is-blurred' : '']">
                            <div class="alert alert-light border mb-3">
                                <p class="mb-1">
                                    Total Harga :
                                    <strong>{{ formatCurrency(transaction.price_final) }}</strong>
                                </p>
                                <p class="mb-1">
                                    Sudah Dibayar :
                                    <strong class="text-success">
                                        {{ formatCurrency(totalPaid) }}
                                    </strong>
                                </p>
                                <p class="mb-0">
                                    Sisa Pembayaran :
                                    <strong class="text-danger">
                                        {{ formatCurrency(remaining) }}
                                    </strong>
                                </p>
                            </div>

                            <form-wrapper @submit="isSubmit">
                                <div class="mb-3">
                                    <input-label class="fw-bold" for="payment_method" value="Metode Pembayaran" />
                                    <select-input name="payment_method" :options="[
                                        { value: null, label: 'Pilih Metode Pembayaran' },
                                        { value: 'cash', label: 'Cash' },
                                        { value: 'transfer', label: 'Transfer' },
                                        { value: 'debit', label: 'Debit' }
                                    ]" v-model="form.payment_method" />
                                    <input-error :message="form.errors.payment_method" />
                                </div>

                                <div class="callout callout-info rounded-0 mb-3">
                                    <ul class="mb-0 ps-3">
                                        <li>Nominal pelunasan akan otomatis sesuai sisa pembayaran.</li>
                                        <li>Transaksi akan ditandai <b>LUNAS</b> setelah proses ini.</li>
                                    </ul>
                                </div>

                                <div class="d-grid d-xl-block">
                                    <button class="btn btn-success px-4" :disabled="!canSubmit || form.processing">
                                        <i class="fas fa-check-circle me-1"></i>
                                        Bayar & Lunasi
                                    </button>
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
