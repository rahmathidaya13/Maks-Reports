<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { Head, Link, router, useForm, usePage } from "@inertiajs/vue3";

import { useConfirm } from "@/helpers/useConfirm.js"
const confirm = useConfirm(); // Memanggil fungsi confirm untuk alert

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
console.log("Total Paid:", totalPaid.value);
const remaining = computed(() => {
    return Math.max(props.transaction.grand_total - totalPaid.value, 0)
})

const canSubmit = computed(() => {
    return remaining.value > 0 && form.payment_method
})

const isSubmit = async () => {
    if (!canSubmit.value) return;
    const settConfirm = await confirm.ask({
        title: 'Konfirmasi Pelunasan',
        message: `Anda akan melunasi sisa tagihan sebesar ${formatCurrency(remaining.value)}. Proses ini tidak dapat dibatalkan.`,
        confirmText: 'Ya, Lunasi',
        icon: "fas fa-check-circle",
        variantIcon: 'success',
        variantButton: 'success',
        requireCheckbox: true,
        checkboxText: 'Saya telah menerima pembayaran dari pelanggan.'
    });
    if (settConfirm) {
        form.post(route('transaction.settle', props.transaction?.transaction_id), {
            preserveScroll: true,
            onSuccess: () => form.reset(),
        })
    }
};
// end form fields

// breandcrumb fields
const title = ref("")
const icon = ref("fas fa-money-bill-wave")
const url = ref(route('transaction'))
onMounted(() => {
    title.value = `Pelunasan Transaksi`
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
            <div class="row pb-3">
                <div class="col-xl-12 col-12">
                    <div class="card border-0 shadow rounded-4 overflow-hidden">

                        <div
                            class="card-header bg-white p-4 border-bottom-0 d-xl-flex d-block justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="icon-square-lg bg-primary bg-opacity-10 text-primary rounded-3 me-3">
                                    <i class="fas fa-wallet fs-4"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold text-dark mb-1">Pelunasan Transaksi {{
                                        transaction.customer.customer_name }}</h5>
                                    <p class="text-muted small mb-0">Invoice: <span class="fw-bold text-primary">{{
                                        transaction.invoice }}</span></p>
                                </div>
                            </div>
                            <Link @click.prevent="goBack" :href="url"
                                class="btn btn-danger fw-bold border px-3 mt-3 mt-xl-0">
                                <i class="fas fa-arrow-left me-2"></i> Kembali
                            </Link>
                        </div>

                        <div v-if="form.processing"
                            class="position-absolute w-100 h-100 bg-white opacity-75 d-flex align-items-center justify-content-center"
                            style="z-index: 10;">
                            <div class="text-center">
                                <div class="spinner-border text-primary mb-2" role="status"></div>
                                <p class="fw-bold text-dark">Memproses...</p>
                            </div>
                        </div>

                        <div class="card-body p-4 position-relative">
                            <loading-overlay :show="form.processing" text="Sedang memproses pelunasan..." />

                            <div
                                class="bg-primary bg-gradient text-white rounded-4 p-4 mb-4 text-center shadow position-relative overflow-hidden">
                                <i class="fas fa-wallet position-absolute top-50 start-0 translate-middle text-white opacity-25"
                                    style="font-size: 8rem; margin-left: 20px;"></i>

                                <div class="position-relative" style="z-index: 2;">
                                    <p class="text-uppercase text-white-50 fw-bold small mb-2 ls-1">Sisa Tagihan
                                    </p>
                                    <h1 class="display-4 fw-bolder mb-0">
                                        {{ formatCurrency(remaining) }}
                                    </h1>
                                    <div
                                        class="mt-2 badge bg-white bg-opacity-25 text-white fw-normal px-3 py-2 rounded-pill">
                                        {{ transaction.customer?.customer_name }}
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <h6 class="fw-bold text-muted small text-uppercase mb-3">
                                    <i class="fas fa-list me-1"></i> Rincian Item Belanja
                                </h6>
                                <div class="border rounded-4 overflow-hidden">
                                    <div
                                        class="bg-light p-3 px-4 border-bottom d-flex justify-content-between small fw-bold text-muted">
                                        <span>Produk</span>
                                        <span>Subtotal</span>
                                    </div>

                                    <div class="list-group list-group-flush px-2">
                                        <div v-for="item in transaction.items" :key="item.id"
                                            class="list-group-item d-flex justify-content-between align-items-center p-3">

                                            <div>
                                                <div class="fw-bold text-dark mb-1">
                                                    {{ item.product?.name ?? 'Produk Dihapus' }}
                                                </div>
                                                <div class="small text-muted">
                                                    <span class="badge bg-light text-dark border me-1">{{ item.quantity
                                                        }} x</span>
                                                    {{ formatCurrency(item.price_unit) }}

                                                    <span v-if="item.discount_amount > 0" class="text-danger ms-1"
                                                        style="font-size: 0.75rem;">
                                                        (Disc: {{ formatCurrency(item.discount_amount) }})
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="fw-bold text-dark">
                                                {{ formatCurrency(item.subtotal) }}
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        class="bg-light p-3 px-4 d-flex justify-content-between align-items-center border-top">
                                        <span class="small fw-bold text-uppercase text-muted">Total Transaksi</span>
                                        <span class="fw-bold fs-5 text-dark">{{ formatCurrency(transaction.grand_total)
                                            }}</span>
                                    </div>

                                    <div
                                        class="bg-white p-3 px-4 d-flex justify-content-between align-items-center border-top text-success">
                                        <span class="small fw-bold"><i class="fas fa-check-circle me-1"></i> Sudah
                                            Dibayar (DP)</span>
                                        <span class="fw-bold">- {{ formatCurrency(totalPaid) }}</span>
                                    </div>
                                </div>
                            </div>

                            <hr class="border-secondary border my-4">

                            <form-wrapper @submit="isSubmit">

                                <div class="mb-4">
                                    <input-label class="fw-bold mb-2 text-dark" for="payment_method"
                                        value="Pilih Metode Pembayaran" />

                                    <select-input
                                        select-class="input-height-2 shadow-none border-secondary border-opacity-25"
                                        name="payment_method" :options="[
                                            { value: null, label: '— Pilih Metode —' },
                                            { value: 'cash', label: '💵 Tunai (Cash)' },
                                            { value: 'transfer', label: '🏦 Transfer Bank' },
                                            { value: 'debit', label: '💳 Kartu Debit' },
                                            { value: 'qris', label: '𝄃𝄂𝄂𝄀𝄁𝄃𝄂𝄂𝄃 QRIS' },
                                        ]" v-model="form.payment_method" />
                                    <input-error :message="form.errors.payment_method" class="mt-2" />
                                </div>

                                <div
                                    class="alert alert-info border-0 d-flex align-items-center mb-4 bg-info bg-opacity-10 text-info-emphasis">
                                    <i class="fas fa-info-circle me-3 fs-4"></i>
                                    <div class="small lh-sm">
                                        Status transaksi akan otomatis berubah menjadi <strong>LUNAS</strong> setelah
                                        pembayaran ini diproses.
                                    </div>
                                </div>

                                <div class="d-grid">
                                    <button class="btn btn-primary btn-height-2 rounded-3 fw-bold shadow-sm"
                                        :class="{ 'disabled': !canSubmit || form.processing }"
                                        :disabled="!canSubmit || form.processing">
                                        <span v-if="!form.processing">
                                            <i class="fas fa-check-circle me-2"></i> Bayar & Lunasi Sekarang
                                        </span>
                                        <span v-else>
                                            <span class="spinner-border spinner-border-sm me-2" role="status"
                                                aria-hidden="true"></span>
                                            Memproses...
                                        </span>
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

.icon-square-lg {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.hover-scale {
    transition: transform 0.2s;
}

.hover-scale:hover {
    transform: scale(1.05);
}

.list-group-item:first-child { border-top: 0; }
</style>
