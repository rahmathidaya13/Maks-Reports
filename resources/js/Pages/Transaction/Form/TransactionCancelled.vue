<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { Head, Link, router, useForm, usePage } from "@inertiajs/vue3";
import { formatCurrency } from "@/helpers/formatCurrency";
import { formatDate } from "@/helpers/formatDate";

import { useConfirm } from "@/helpers/useConfirm.js"
const confirm = useConfirm(); // Memanggil fungsi confirm untuk alert

const props = defineProps({
    transaction: Object,
});
const form = useForm({
    reason: '',
})

// hitung total uang yang sudah dibayar
const totalPaid = computed(() => {
    if (!props.transaction.payments) return 0;
    return props.transaction.payments.reduce((sum, pay) => sum + Number(pay.amount), 0);
});

console.log(totalPaid.value);
const remaining = computed(() => {
    return Math.max(props.transaction.price_final - totalPaid.value, 0)
})
// console.log(remaining.value);
const submit = async () => {
    const settConfirm = await confirm.ask({
        title: 'Konfirmasi Pembatalan',
        message: 'Apakah Anda benar-benar yakin ingin membatalkan transaksi ini? Status akan dikunci dan tidak bisa diubah kembali.',
        confirmText: 'Ya, Batalkan Sekarang',
        variantIcon: 'danger',
        variantButton: 'danger',
        requireCheckbox: true,
        checkboxText: 'Saya mengerti aksi ini akan membatalkan seluruh pesanan.'
    });
    if (settConfirm) {
        form.put(route('transaction.cancelled.updated', props.transaction.transaction_id), {
            onSuccess: () => {
                form.reset();
            }
        });
    }
}

const title = ref("");
const icon = ref("");
const url = ref("")
onMounted(() => {
    if (props.transaction && props.transaction?.transaction_id) {
        title.value = "Batalkan Transaksi - " + props.transaction?.customer.customer_name
        icon.value = "fas fa-ban"
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
</script>
<template>

    <Head :title="title" />
    <app-layout>
        <template #content>
            <loader-page ref="loaderActive" />
            <bread-crumbs :icon="icon" :title="title" :items="breadcrumbItems" />

            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="fw-bold text-dark mb-1">Batalkan Transaksi</h4>
                    <p class="text-muted small mb-0">Proses ini akan mengubah status transaksi menjadi 'Batal'.</p>
                </div>
                <Link @click.prevent="goBack" :href="url" class="btn btn-sm btn-danger mb-3">
                    <i class="fas fa-arrow-left"></i>
                    Kembali
                </Link>
            </div>
            <div class="row g-4 pb-3 justify-content-center">

                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                        <div
                            class="card-header bg-danger text-white p-4 border-0 text-center position-relative overflow-hidden">
                            <div class="position-relative z-1">
                                <h3 class="fw-bold mb-1">Konfirmasi Pembatalan</h3>
                                <p class="text-white text-opacity-75 mb-0">
                                    Invoice: <span class="fw-bold text-white bg-dark bg-opacity-25 px-2 py-1 rounded">{{
                                        props.transaction.invoice }}</span>
                                </p>
                            </div>
                        </div>


                        <div class="card-body p-5 position-relative">
                            <loading-overlay :show="form.processing" text="Sedang memproses pembatalan..." />
                            <form-wrapper @submit="submit">
                                <div class="bg-light rounded-3 p-3 mb-4 border border-dashed">
                                    <h6 class="text-uppercase text-xs fw-bold text-muted mb-3 ls-1">Detail Transaksi
                                    </h6>

                                    <div class="d-flex align-items-center mb-3">
                                        <div class="symbol symbol-40px me-3">
                                            <div class="d-flex align-items-center justify-content-center rounded-circle bg-white border text-primary"
                                                style="width: 40px; height: 40px;">
                                                <i class="fas fa-user"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-muted text-xs">Nama Pelanggan</div>
                                            <div class="fw-bold text-dark">{{ props.transaction.customer?.customer_name
                                                ?? '-' }}</div>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center mb-3">
                                        <div class="symbol symbol-40px me-3">
                                            <div class="d-flex align-items-center justify-content-center rounded-circle bg-white border text-success"
                                                style="width: 40px; height: 40px;">
                                                <i class="fas fa-box-open"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-muted text-xs">Produk Dibeli</div>
                                            <div class="fw-bold text-dark">{{ props.transaction.product?.name ?? '-' }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center mb-3">
                                        <div class="symbol symbol-40px me-3">
                                            <div class="d-flex align-items-center justify-content-center rounded-circle bg-white border text-info"
                                                style="width: 40px; height: 40px;">
                                                <i class="fas fa-money-bill"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-muted text-xs">Harga Produk</div>
                                            <div class="fw-bold text-dark">{{
                                                formatCurrency(props.transaction.price_original) ?? '-' }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-2 mt-2 pt-2 border-top">
                                        <div class="col-6">
                                            <div class="text-muted text-xs">Tanggal</div>
                                            <div class="fw-bold text-dark text-sm">{{
                                                formatDate(props.transaction.transaction_date) }}</div>
                                        </div>
                                        <div class="col-6 text-end">
                                            <div class="text-muted text-xs">Total Sudah Bayar</div>
                                            <div class="fw-bold text-danger">{{
                                                formatCurrency(totalPaid) }}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold text-dark small mb-2">
                                        Alasan Pembatalan <span class="text-danger">*</span>
                                    </label>
                                    <text-area placeholder="Jelaskan alasan pembatalan..." name="reason"
                                        v-model="form.reason" :maxChar="500" :rows="5" />
                                    <input-error :message="form.errors.reason" />
                                </div>

                                <div class="d-block mt-4">
                                    <base-button waiting="Memproses..." :loading="form.processing"
                                        class="w-100 btn-lg rounded-3"
                                        :variant="props.transaction?.transaction_id ? 'danger' : 'primary'"
                                        type="submit" label="Batalkan Transaksi" />

                                    <div class="text-center mt-3">
                                        <Link @click.prevent="goBack" :href="url"
                                            class="text-muted small text-decoration-none">
                                            Batal, kembali ke daftar transaksi
                                        </Link>
                                    </div>
                                </div>

                            </form-wrapper>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </app-layout>
</template>
