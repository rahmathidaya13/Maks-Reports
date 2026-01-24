<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
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
const grandTotal = computed(() => Number(props.transaction?.price_final ?? 0));
const remainingBalance = computed(() => {
    const sisa = grandTotal.value - totalPaid.value
    return sisa > 0 ? sisa : 0;
});
const paymentPercentage = computed(() => {
    if (grandTotal.value === 0) return 0;
    const percentage = (totalPaid.value / grandTotal.value) * 100;
    return percentage > 100 ? 100 : percentage;
});

const paymentStatus = computed(() => {
    if (totalPaid.value >= grandTotal.value && grandTotal.value > 0) return 'PAID';
    if (totalPaid.value > 0) return 'PARTIAL';
    return 'UNPAID';
});

// Warna Tema Berdasarkan Status
const themeColor = computed(() => {
    if (props.transaction?.status === 'cancelled') return 'danger';
    if (paymentStatus.value === 'PAID') return 'success';
    if (paymentStatus.value === 'PARTIAL') return 'warning';
    return 'secondary';
});
</script>
<template>
    <div class="row" v-if="props.show">
        <div class="col-xl-12 col-sm-12">
            <modal size="modal-lg" :footer="false" icon="fas fa-info-circle" :show="props.show" title="Detail Transaksi"
                @closed="close">
                <template #body>
                    <div v-if="transaction">

                        <div class="position-relative overflow-hidden rounded-4 p-4 text-center text-white"
                            :class="`bg-${themeColor}`"
                            style="background-image: linear-gradient(rgba(0,0,0,0.1), rgba(0,0,0,0.1));">

                            <div class="position-relative z-1">
                                <div class="text-uppercase ls-1 small opacity-75 mb-1 fw-bold">Invoice</div>
                                <h2 class="fw-bold mb-2">{{ transaction.invoice }}</h2>

                                <span
                                    class="badge bg-white bg-opacity-25 border border-white border-opacity-50 px-3 py-2 rounded-pill shadow-sm backdrop-blur">
                                    <i class="fas me-1" :class="{
                                        'fa-check-circle': paymentStatus === 'PAID',
                                        'fa-clock': paymentStatus === 'PARTIAL',
                                        'fa-circle': paymentStatus === 'UNPAID',
                                        'fa-times-circle': transaction.status === 'cancelled'
                                    }"></i>

                                    <span v-if="transaction.status === 'cancelled'">DIBATALKAN</span>
                                    <span v-else-if="paymentStatus === 'PAID'">LUNAS (PAID)</span>
                                    <span v-else-if="paymentStatus === 'PARTIAL'">BELUM LUNAS (DP)</span>
                                    <span v-else>MENUNGGU PEMBAYARAN</span>
                                </span>

                            </div>
                        </div>

                        <div class="bg-light border-bottom px-4 py-3 mt-3 rounded-4" v-if="transaction.status !== 'cancelled'">
                            <div class="d-flex justify-content-between small fw-bold mb-1">
                                <span :class="`text-${themeColor}`">Terbayar: {{ Math.round(paymentPercentage)
                                    }}%</span>
                                <span class="text-muted">Tagihan: {{ formatCurrency(grandTotal) }}</span>
                            </div>
                            <div class="progress shadow-sm" style="height: 10px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated"
                                    :class="`bg-${themeColor}`" role="progressbar"
                                    :style="{ width: paymentPercentage + '%' }">
                                </div>
                            </div>
                        </div>

                        <div class="p-4">

                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <div
                                        class="d-flex align-items-start p-3 bg-white rounded-3 border h-100">
                                        <div class="symbol symbol-45px me-3">
                                            <div class="d-flex align-items-center justify-content-center rounded-3 bg-primary bg-opacity-10 text-primary"
                                                style="width: 45px; height: 45px;">
                                                <i class="fas fa-user fs-4"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <small
                                                class="text-muted text-uppercase text-xs fw-bold ls-1">Pelanggan</small>
                                            <div class="fw-bold text-dark fs-6">{{ transaction.customer?.customer_name ??
                                                'Umum / Guest' }}</div>
                                            <div class="text-xs text-secondary mt-1">
                                                <i class="fas fa-id-badge me-1 opacity-50"></i> ID: {{
                                                    transaction.customer_id ? subString(transaction.customer_id,8)
                                                        : '-' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div
                                        class="d-flex align-items-start p-3 bg-white rounded-3 border h-100">
                                        <div class="symbol symbol-45px me-3">
                                            <div class="d-flex align-items-center justify-content-center rounded-3 bg-info bg-opacity-10 text-info"
                                                style="width: 45px; height: 45px;">
                                                <i class="fas fa-headset fs-4"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <small class="text-muted text-uppercase text-xs fw-bold ls-1">Sales</small>
                                            <div class="fw-bold text-dark fs-6">{{  transaction.creator?.name ??
                                                '-' }}</div>
                                            <div class="text-xs text-secondary mt-1">
                                                <i class="fas fa-calendar-alt me-1 opacity-50"></i> {{
                                                    formatDate(transaction.transaction_date) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card border border-dashed bg-light mb-4">
                                <div class="card-body d-flex align-items-center">
                                    <div class="bg-white p-2 rounded border me-3">
                                        <i class="fas fa-box-open fs-1 text-secondary opacity-50"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <small class="text-muted text-uppercase text-xs">Produk Dibeli</small>
                                        <h6 class="fw-bold text-dark mb-0">{{ transaction.product?.name ??
                                            'Nama Produk Tidak Tersedia' }}</h6>
                                        <span class="badge bg-white border text-secondary mt-1 fw-normal text-xs">
                                            {{ formatCategory(transaction.product?.category) ?? 'Uncategorized' }}
                                        </span>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted d-block text-xs">Harga Produk</small>
                                        <span class="fw-bold text-dark">{{ formatCurrency(transaction.price_original) }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="card border shadow-none overflow-hidden">
                                <div class="card-header bg-white border-bottom py-3">
                                    <h6 class="fw-bold mb-0 text-dark">
                                        <i class="fas fa-receipt me-2 text-primary"></i>Riwayat Pembayaran
                                    </h6>
                                </div>
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0">
                                        <thead class="bg-light text-muted text-uppercase text-xs fw-bold">
                                            <tr>
                                                <th class="ps-4">Tanggal</th>
                                                <th>Metode pembayaran</th>
                                                <th>Tipe pembayaran</th>
                                                <th class="pe-4 text-end">Jumlah</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(pay, idx) in transaction.payments" :key="idx">
                                                <td class="text-sm ps-4">{{ formatDate(pay.payment_date) }}</td>
                                                <td class="text-uppercase text-sm">
                                                    <i class="fas fa-credit-card me-1 text-muted"></i> {{
                                                        pay.payment_method }}
                                                </td>
                                                <td>
                                                    <span class="badge rounded-pill text-xs border "
                                                        :class="pay.payment_type === 'repayment' ? 'bg-success bg-opacity-10 text-success border-success' : 'bg-warning bg-opacity-10 text-warning border-warning'">
                                                        {{ pay.payment_type === 'repayment' ? 'PELUNASAN'
                                                            : 'DP / CICILAN' }}
                                                    </span>
                                                </td>
                                                <td class="pe-4 text-end fw-bold text-dark text-sm">
                                                    {{ formatCurrency(pay.amount) }}
                                                </td>
                                            </tr>
                                            <tr v-if="!transaction.payments?.length">
                                                <td colspan="4" class="text-center py-4 text-muted fst-italic text-xs">
                                                    Belum ada data pembayaran masuk.
                                                </td>
                                            </tr>
                                        </tbody>

                                        <tfoot class="bg-light border-top">
                                            <tr>
                                                <td colspan="3" class="ps-4 text-end text-muted text-sm">Harga Awal</td>
                                                <td class="pe-4 text-end text-muted text-sm">{{
                                                    formatCurrency(transaction.price_original) }}</td>
                                            </tr>
                                            <tr v-if="transaction.price_discount > 0">
                                                <td colspan="3" class="ps-4 text-end text-danger text-sm">Diskon /
                                                    Potongan</td>
                                                <td class="pe-4 text-end text-danger text-sm">- {{
                                                    formatCurrency(transaction.price_discount) }}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="3"
                                                    class="ps-4 text-end fw-bold text-dark text-sm border-top">Total
                                                    Tagihan (Net)</td>
                                                <td class="pe-4 text-end fw-bold text-dark text-sm border-top">{{
                                                    formatCurrency(transaction.price_final) }}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="3" class="ps-4 text-end text-success fw-bold text-sm">Total
                                                    Dibayar</td>
                                                <td class="pe-4 text-end text-success fw-bold text-sm">{{
                                                    formatCurrency(totalPaid) }}</td>
                                            </tr>
                                            <tr v-if="remainingBalance > 0">
                                                <td colspan="3" class="ps-4 text-end text-danger fw-bold fs-6 pt-3">SISA
                                                    TAGIHAN</td>
                                                <td class="pe-4 text-end text-danger fw-bold fs-6 pt-3">{{
                                                    formatCurrency(remainingBalance) }}</td>
                                            </tr>
                                            <tr v-else>
                                                <td colspan="4"
                                                    class="text-center py-3 bg-success bg-opacity-10 text-success fw-bold border-top">
                                                    <i class="fas fa-check-double me-1"></i> TRANSAKSI LUNAS
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
.text-xxs { font-size: 0.65rem; }
.text-sm {
    font-size: 0.9rem;
}

.ls-1 {
    letter-spacing: 0.5px;
}
/* Font Monospace untuk Angka Uang (Agar lurus sejajar) */
.font-monospace {
    font-family: 'Consolas', 'Monaco', monospace;
}
/* Shadow Hover Effect */
.shadow-sm-hover { transition: all 0.2s ease; }
.shadow-sm-hover:hover {
    transform: translateY(-2px);
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.08)!important;
}
/* Dashed Border Utility */
.border-dashed {
    border-style: dashed !important;
}

/* Backdrop Blur untuk Badge di Header */
.backdrop-blur {
    backdrop-filter: blur(5px);
    -webkit-backdrop-filter: blur(5px);
}
/* Custom Symbol Size */
.symbol-45px {
    width: 45px;
}
</style>
