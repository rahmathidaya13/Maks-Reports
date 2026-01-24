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

const remaining = computed(() => {
    return Math.max(props.transaction.price_final - totalPaid.value, 0)
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
                                    <p class="text-muted small mb-0">Selesaikan pembayaran untuk pesanan ini.</p>
                                </div>
                            </div>
                            <Link @click.prevent="goBack" :href="url"
                                class="btn btn-danger fw-bold border hover-scale px-3 mt-3 mt-xl-0">
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
                                class="bg-primary bg-opacity-10 rounded-4 p-4 mb-4 text-center border border-primary border-opacity-25 position-relative overflow-hidden">

                                <i class="fas fa-file-invoice-dollar position-absolute bottom-0 end-0 text-primary opacity-25"
                                    style="font-size: 6rem; transform: rotate(-20deg) translate(20px, 10px);"></i>

                                <div class="position-relative" style="z-index: 1;">
                                    <span
                                        class="badge bg-white text-primary border border-primary border-opacity-25 mb-2 px-3 py-2 rounded-pill shadow-sm">
                                        <i class="fas fa-tag me-1"></i> Pembayaran Untuk
                                    </span>

                                    <h3 class="fw-bolder text-dark mb-0 text-break px-3">
                                        {{ transaction.product.name ?? 'Nama Produk/Jasa' }}
                                    </h3>
                                </div>

                                <hr class="border-primary border-opacity-25 border-2 border-dashed mx-auto w-75 my-4 position-relative"
                                    style="z-index: 1;">

                                <div class="position-relative" style="z-index: 1;">
                                    <p class="text-uppercase text-primary fw-bold small mb-1 ls-1">Sisa Yang Harus
                                        Dibayar</p>
                                    <h1 class="display-4 fw-bolder text-primary mb-0">
                                        {{ formatCurrency(remaining) }}
                                    </h1>
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-12">
                                    <div class="d-flex justify-content-between small text-muted mb-1">
                                        <span>Progress Pembayaran</span>
                                        <span>{{ Math.round((totalPaid / transaction.price_final) * 100) }}%</span>
                                    </div>
                                    <div class="progress" style="height: 10px; border-radius: 10px;">
                                        <div class="progress-bar bg-success" role="progressbar"
                                            :style="{ width: (totalPaid / transaction.price_final) * 100 + '%' }">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-6">
                                    <div class="border rounded-3 p-2 bg-light">
                                        <small class="text-muted d-block">Total Tagihan</small>
                                        <span class="fw-bold text-dark">{{ formatCurrency(transaction.price_final)
                                        }}</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="border rounded-3 p-2 bg-light">
                                        <small class="text-muted d-block">Sudah Dibayar</small>
                                        <span class="fw-bold text-success">{{ formatCurrency(totalPaid) }}</span>
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
</style>
