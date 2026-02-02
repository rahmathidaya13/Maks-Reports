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

const grandTotal = computed(() => {
    return Number(props.transaction.grand_total || 0);
});

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

            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h4 class="fw-bold text-dark mb-1">Batalkan Transaksi</h4>
                    <p class="text-muted small mb-0">Proses ini akan mengubah status transaksi menjadi 'Batal'.</p>
                </div>
                <Link @click.prevent="goBack" :href="url" class="btn btn-sm btn-danger mb-3">
                    <i class="fas fa-arrow-left"></i>
                    Kembali
                </Link>
            </div>
            <div class="row g-3 pb-3 justify-content-center">
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


                        <div class="card-body position-relative">
                            <loading-overlay :show="form.processing" text="Sedang memproses pembatalan..." />

                            <div v-if="totalPaid > 0"
                                class="alert alert-warning border-warning border-opacity-25 d-flex align-items-center mb-4 shadow-sm">
                                <div class="bg-warning bg-opacity-25 text-warning rounded-circle p-2 me-3 d-flex align-items-center justify-content-center"
                                    style="width: 40px; height: 40px;">
                                    <i class="fas fa-exclamation-triangle fs-5"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">Perhatian: Dana Masuk Terdeteksi</div>
                                    <div class="small text-dark text-opacity-75">
                                        Terdapat pembayaran masuk sebesar <strong class="text-danger">{{
                                            formatCurrency(totalPaid) }}</strong>.
                                        Pastikan Anda memproses <u>Refund / Pengembalian Dana</u> kepada pelanggan
                                        setelah pembatalan.
                                    </div>
                                </div>
                            </div>

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

                            <div class="card rounded-3 overflow-hidden shadow-none mb-4">
                                <div class="card-header bg-light py-2">
                                    <h6 class="mb-0 fw-bold text-muted small text-uppercase">
                                        <i class="fas fa-box-open me-1"></i> Item Yang Dibatalkan
                                    </h6>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead class="bg-white text-muted text-uppercase text-xs fw-bold border-bottom">
                                            <tr>
                                                <th class="ps-3 py-2">Produk</th>
                                                <th class="text-center py-2">Jumlah (Qty)</th>
                                                <th class="text-end py-2">Harga</th>
                                                <th class="text-end pe-3 py-2">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="item in transaction.items" :key="item.id">
                                                <td class="ps-3 py-2">
                                                    <div class="fw-bold text-dark text-sm">{{ item.product?.name ??
                                                        'Item Terhapus' }}</div>
                                                    <div class="text-xs text-muted" v-if="item.discount_amount > 0">
                                                        Disc: <span class="text-danger">-{{
                                                            formatCurrency(item.discount_amount) }}</span>
                                                    </div>
                                                </td>
                                                <td class="text-center text-sm py-2">x{{ item.quantity }}</td>
                                                <td class="text-end text-sm text-muted py-2">{{
                                                    formatCurrency(item.price_unit) }}</td>
                                                <td class="text-end fw-bold text-dark text-sm pe-3 py-2">
                                                    {{ formatCurrency((item.price_unit * item.quantity) -
                                                        item.discount_amount) }}
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot class="bg-light border-top">
                                            <tr>
                                                <td colspan="3" class="text-end text-muted text-sm fw-bold py-2 pe-3">
                                                    TOTAL NILAI TRANSAKSI</td>
                                                <td class="text-end fw-bolder text-dark text-sm py-2 pe-3">
                                                    {{ formatCurrency(grandTotal) }}
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            <form-wrapper @submit="submit">
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-dark small">
                                        Alasan Pembatalan <span class="text-danger">*</span>
                                    </label>
                                    <text-area placeholder="Jelaskan alasan pembatalan..." name="reason"
                                        v-model="form.reason" :maxChar="500" :rows="5" />
                                    <input-error :message="form.errors.reason" />
                                </div>

                                <div class="d-block mt-3 mb-4">
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
