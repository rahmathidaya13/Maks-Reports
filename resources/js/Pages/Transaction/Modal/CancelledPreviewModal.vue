<script setup>
import { computed, watch } from "vue";
import { formatDate } from "@/helpers/formatDate";
const props = defineProps({
    transaction: Object,
    show: Boolean,
});
const emit = defineEmits(["update:show"]);
const close = () => {
    emit("update:show", false);
}
function formatCurrency(value) {
    if (!value) return "0";
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value);
}
function formatCategory(cat) {
    return cat
        .split("/") // pecah sub kategori
        .map((part) => part.replace(/-/g, " ")) // ganti - dengan spasi
        .map((part) => part.replace(/\b\w/g, (char) => char.toUpperCase())) // kapital
        .join(" - "); // gabungkan dengan pemisah cantik
}
function subString(strValue, length) {
    const clean = strValue.replace(/-/g, "")
    return clean.length > length
        ? clean.substring(0, length)
        : clean
}
// hitung total uang yang sudah dibayar
const totalPaid = computed(() => {
    if (!props.transaction.payments) return 0;
    return props.transaction.payments.reduce((sum, pay) => sum + Number(pay.amount), 0);
});

const remainingBalance = computed(() => {
    if (!props.transaction) return 0;
    return Number(props.transaction.grand_total) - totalPaid.value;
});
</script>
<template>
    <div class="row" v-if="props.show">
        <div class="col-xl-12 col-sm-12">
            <modal width="900px" size="modal-lg" :footer="false" icon="fas fa-info-circle" :show="props.show"
                title="Detail Transaksi Pembatalan" @closed="close">
                <template #body>
                    <div v-if="transaction">
                        <div
                            class="text-center bg-danger bg-opacity-10 rounded-4 p-4 mb-4 border border-danger border-opacity-10">
                            <span class="text-secondary text-uppercase mb-0 fw-bold">Invoice:</span>
                            <h4 class="fw-bold text-dark mb-0">{{ transaction.invoice }}</h4>
                            <div v-if="totalPaid > 0" class="mt-2 text-danger small fw-bold">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                Ada dana masuk sebesar {{ formatCurrency(totalPaid) }} yang mungkin
                                perlu di <i> refund </i> .
                            </div>
                        </div>

                        <div
                            class="alert alert-danger border-0 d-flex align-items-start gap-3 rounded-3 shadow-sm mb-4">
                            <div class="bg-white text-danger rounded-circle p-2 d-flex align-items-center justify-content-center shadow-sm"
                                style="width: 35px; height: 35px; min-width: 35px">
                                <i class="fas fa-quote-left small"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-danger text-uppercase text-xs ls-1 mb-1">
                                    Alasan Pembatalan
                                </h6>
                                <p class="mb-2 text-dark" style="font-size: 0.95rem; line-height: 1.5">
                                    "{{ transaction.cancel_reason ?? "Tidak ada alasan spesifik." }}"
                                </p>
                                <div
                                    class="d-flex align-items-center text-muted text-xs gap-3 border-top border-danger border-opacity-25 pt-2 mt-2">
                                    <span>
                                        <i class="fas fa-user-edit me-1"></i> Oleh:
                                        <strong>{{ transaction.creator?.name ?? "Admin" }}</strong>
                                    </span>
                                    <span>
                                        <i class="fas fa-clock me-1"></i>
                                        {{ formatDate(transaction.cancelled_at) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <div class="h-100 p-3 bg-light rounded-3 border border-dashed">
                                    <div class="d-flex align-items-center">
                                        <div class="symbol symbol-45px me-3">
                                            <div class="d-flex align-items-center justify-content-center rounded-3 bg-primary bg-opacity-10 text-primary"
                                                style="width: 45px; height: 45px;">
                                                <i class="fas fa-user fs-4"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <small
                                                class="text-muted text-uppercase text-xs fw-bold ls-1">Pelanggan</small>
                                            <div class="fw-bold text-dark fs-6">
                                                {{ transaction.customer?.customer_name ?? "Guest" }}
                                            </div>
                                            <div class="text-muted text-xs">
                                                ID:
                                                {{
                                                    transaction.customer_id
                                                        ? subString(transaction.customer_id, 8)
                                                        : "-"
                                                }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card border border-danger border-opacity-25 bg-danger bg-opacity-10 mb-4">
                            <div class="card-header bg-transparent border-danger border-opacity-25 py-2">
                                <h6 class="fw-bold mb-0 text-danger small text-uppercase">
                                    <i class="fas fa-ban me-2"></i>Item Yang Dibatalkan
                                </h6>
                            </div>
                            <div class="table-responsive bg-white">
                                <table class="table table-sm align-middle mb-0">
                                    <thead class="bg-light text-muted text-uppercase text-xs fw-bold">
                                        <tr>
                                            <th class="ps-3">Produk</th>
                                            <th class="text-center">Qty</th>
                                            <th class="text-end pe-3">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="item in transaction.items" :key="item.id">
                                            <td class="ps-3">
                                                <div class="fw-bold text-dark text-sm">
                                                    {{ item.product?.name ?? 'Item Terhapus' }}</div>
                                            </td>
                                            <td class="text-center text-sm">x{{ item.quantity }}</td>
                                            <td class="text-end fw-bold text-dark text-sm pe-3">
                                                {{ formatCurrency((item.price_unit * item.quantity) -
                                                    item.discount_amount) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="bg-light border-top">
                                        <tr>
                                            <td colspan="2" class="text-end fw-bold text-muted text-xs ps-3">TOTAL NILAI
                                                TRANSAKSI</td>
                                            <td class="text-end fw-bold text-dark text-sm pe-3">
                                                {{ formatCurrency(transaction.grand_total) }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="card shadow-none">
                            <div class="card-header bg-white border-bottom py-3 px-4">
                                <h6 class="fw-bold mb-0 text-dark">Riwayat Pembayaran</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0">
                                        <thead class="bg-light text-muted text-uppercase text-xs fw-bold">
                                            <tr>
                                                <th class="ps-4 py-3">Tipe Pembayaran</th>
                                                <th class="py-3">Metode Pembayaran</th>
                                                <th class="py-3 text-end pe-4">Jumlah</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(pay, index) in transaction.payments" :key="index">
                                                <td class="ps-4 py-3">
                                                    <span class="badge rounded-pill fw-normal me-2 border" :class="pay.payment_type === 'repayment'
                                                        ? 'bg-success bg-opacity-10 text-success border-success'
                                                        : 'bg-warning bg-opacity-10 text-warning border-warning'
                                                        ">
                                                        {{
                                                            pay.payment_type === 'repayment' ? 'Pelunasan' :
                                                                'DP/Cicilan'
                                                        }}
                                                    </span>
                                                    <span class="text-xs text-muted">{{
                                                        formatDate(pay.payment_date)
                                                        }}</span>
                                                </td>
                                                <td class="py-3">
                                                    <span class="text-sm text-dark text-capitalize">
                                                        <i class="fas fa-credit-card me-1 text-secondary"></i>
                                                        {{ pay.payment_method }}
                                                    </span>
                                                </td>
                                                <td class="pe-4 py-3 text-end fw-bold text-dark">
                                                    {{ formatCurrency(pay.amount) }}
                                                </td>
                                            </tr>

                                            <tr v-if="transaction.payments?.length === 0">
                                                <td colspan="3" class="text-center py-4 text-muted fst-italic text-sm">
                                                    Belum ada riwayat pembayaran (0 Rupiah).
                                                </td>
                                            </tr>
                                        </tbody>

                                        <tfoot class="bg-light border-top">
                                            <tr>
                                                <td colspan="2" class="ps-4 py-2 text-end text-muted text-sm">
                                                    Total Subtotal
                                                </td>
                                                <td class="pe-4 py-2 text-end fw-bold text-dark">
                                                    {{ formatCurrency(transaction.grand_total) }}
                                                </td>
                                            </tr>

                                            <tr>
                                                <td colspan="2" class="ps-4 py-2 text-end text-sm text-success fw-bold">
                                                    <i class="fas fa-check-circle me-1"></i> Total Sudah Dibayar
                                                </td>
                                                <td class="pe-4 py-2 text-end fw-bold text-success">
                                                    {{ formatCurrency(totalPaid) }}
                                                </td>
                                            </tr>

                                            <tr v-if="remainingBalance > 0">
                                                <td colspan="2"
                                                    class="ps-4 py-3 text-end text-sm text-danger fw-bold border-top">
                                                    Sisa Tagihan
                                                </td>
                                                <td class="pe-4 py-3 text-end fw-bold text-danger border-top fs-6">
                                                    {{ formatCurrency(remainingBalance) }}
                                                </td>
                                            </tr>

                                            <tr v-else>
                                                <td colspan="3"
                                                    class="py-3 text-center bg-success bg-opacity-10 border-top">
                                                    <span class="badge bg-success rounded-pill px-3">
                                                        <i class="fas fa-check-double me-1"></i> SUDAH LUNAS
                                                        SEBELUM DIBATALKAN
                                                    </span>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </modal>
        </div>
    </div>
</template>
<style scoped>
/* Helpers */
.text-xs {
    font-size: 0.75rem;
}

.text-sm {
    font-size: 0.875rem;
}

.ls-1 {
    letter-spacing: 0.5px;
}

/* Dashed Border Utility */
.border-dashed {
    border-style: dashed !important;
}

/* Custom Symbol Size */
.symbol-45px {
    width: 45px;
}
</style>
