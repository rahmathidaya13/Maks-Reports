<script setup>
import { computed, ref } from "vue";
import { Head, usePage, router, Link } from '@inertiajs/vue3';
import { debounce } from "lodash";
const props = defineProps({
    widgets: Object,
    leaderboards: Object,
    charts: Object,
    current_month: String,
});
console.log(props.leaderboards);
// Helper untuk format Rupiah
const formatCurrency = (value) => {
    if (!value) return "Rp 0";
    return new Intl.NumberFormat('id-ID', {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value);
};

// Helper untuk format Angka biasa (ribuan)
const formatNumber = (value) => {
    if (!value) return "0";
    return new Intl.NumberFormat('id-ID').format(value);
};
// (Opsional) Helper resolve image untuk produk

const resolveImage = (path) => {
    if (!path) return "https://ui-avatars.com/api/?name=??";
    // Jika link eksternal (http/https)
    if (path.startsWith("http")) return path;

    // Jika file lokal, tambahkan '/' agar root terbaca
    // Pastikan path di DB kamu 'storage/...' atau sesuaikan disini
    return `/storage/${path}`;
};

</script>
<template>

    <Head title="Dashboard" />
    <app-layout>
        <template #content>
            <div class="d-flex justify-content-between align-items-end mb-4 py-3">
                <div>
                    <h4 class="fw-bold text-dark mb-1">
                        Dashboard Manajemen
                    </h4>
                    <p class="text-muted mb-0">Ringkasan performa bisnis Anda untuk periode <span
                            class="fw-semibold text-primary">{{ current_month }}</span>.</p>
                </div>
                <div>
                    <button class="btn btn-white bg-white border shadow-sm">
                        <i class="fas fa-download me-2 text-muted"></i> Unduh Laporan
                    </button>
                </div>
            </div>
            <callout />

            <div class="row pb-5 g-2">

                <div class="col-xl-4 col-md-6">
                    <div class="card border-0 shadow-sm h-100 widget-card widget-blue">
                        <div class="card-body p-4 d-flex align-items-center">
                            <div
                                class="icon-shape rounded-circle d-flex align-items-center justify-content-center me-4">
                                <i class="fas fa-shopping-cart fs-3"></i>
                            </div>
                            <div>
                                <p class="text-uppercase fw-semibold mb-1 text-muted"
                                    style="font-size: 0.75rem; letter-spacing: 0.5px;">Total Transaksi</p>
                                <h3 class="fw-bold mb-0 text-dark">{{ formatNumber(widgets.total_sales) }} <span
                                        class="fs-6 fw-normal text-muted">Struk</span></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-md-6">
                    <div class="card border-0 shadow-sm h-100 widget-card widget-green">
                        <div class="card-body p-4 d-flex align-items-center">
                            <div
                                class="icon-shape rounded-circle d-flex align-items-center justify-content-center me-4">
                                <i class="fas fa-wallet fs-3"></i>
                            </div>
                            <div>
                                <p class="text-uppercase fw-semibold mb-1 text-muted"
                                    style="font-size: 0.75rem; letter-spacing: 0.5px;">Pendapatan (Lunas)</p>
                                <h3 class="fw-bold mb-0 text-success">{{ formatCurrency(widgets.total_revenue) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-md-12">
                    <div class="card border-0 shadow-sm h-100 widget-card widget-orange">
                        <div class="card-body p-4 d-flex align-items-center">
                            <div
                                class="icon-shape rounded-circle d-flex align-items-center justify-content-center me-4">
                                <i class="fas fa-hand-holding-usd fs-3"></i>
                            </div>
                            <div>
                                <p class="text-uppercase fw-semibold mb-1 text-muted"
                                    style="font-size: 0.75rem; letter-spacing: 0.5px;">Piutang Berjalan (DP)</p>
                                <h3 class="fw-bold mb-0 text-warning">{{ formatCurrency(widgets.total_revenue_dp) }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="row g-4 pb-5">

                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden">
                        <div
                            class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-store text-primary me-2"></i>Top 5
                                Cabang Terbaik</h6>
                            <span class="badge bg-light text-muted border">Bulan Ini</span>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                <li v-if="!leaderboards.top_branches.length"
                                    class="list-group-item p-4 text-center text-muted">
                                    Belum ada data transaksi cabang bulan ini.
                                </li>

                                <li v-for="(branch, index) in leaderboards.top_branches" :key="branch.branches_id"
                                    class="list-group-item p-3 d-flex justify-content-between align-items-center hover-bg">
                                    <div class="d-flex align-items-center">
                                        <div class="rank-badge me-3 d-flex align-items-center justify-content-center fw-bold"
                                            :class="index < 3 ? 'bg-primary text-white' : 'bg-light text-muted border'">
                                            {{ index + 1 }}
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold text-dark text-capitalize">{{ branch.name }}</h6>
                                            <small class="text-muted">ID: {{ branch.branches_id?.substring(0, 6) || '-'
                                            }}</small>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <span
                                            class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-10 px-3 py-2 rounded-pill">
                                            {{ formatNumber(branch.transactions_count) }} Transaksi
                                        </span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden">
                        <div
                            class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-box-open text-warning me-2"></i>Top 5
                                Produk Terlaris</h6>
                            <span class="badge bg-light text-muted border">Bulan Ini</span>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                <li v-if="!leaderboards.top_products.length"
                                    class="list-group-item p-4 text-center text-muted">
                                    Belum ada data penjualan produk bulan ini.
                                </li>

                                <li v-for="(product, index) in leaderboards.top_products" :key="product.product_id"
                                    class="list-group-item p-3 d-flex justify-content-between align-items-center hover-bg">
                                    <div class="d-flex align-items-center">
                                        <div class="position-relative me-3">
                                            <img :src="resolveImage(product.image_link || product.image_path)"
                                                class="rounded-3 border object-fit-cover shadow-sm" width="48"
                                                height="48" alt="Product">
                                            <span v-if="index === 0"
                                                class="position-absolute top-0 start-100 translate-middle text-warning"
                                                style="font-size: 1.2rem; margin-left: -5px; margin-top: -5px;"><i
                                                    class="fas fa-crown"></i></span>
                                        </div>
                                        <div style="max-width: 250px;">
                                            <h6 class="mb-0 fw-bold text-dark text-truncate" :title="product.name">{{
                                                product.name }}</h6>
                                            <small class="text-muted text-uppercase" style="font-size: 0.7rem;">Terjual
                                                bulan ini</small>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <h5 class="mb-0 fw-bold text-dark">{{
                                            formatNumber(product.transactions_sum_quantity) }} <small
                                                class="text-muted fs-7 fw-normal">Unit</small></h5>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </app-layout>
</template>
<style scoped>
/* Card Hover Effects */
.card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border-radius: 12px;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .08) !important;
}

.hover-bg {
    transition: background-color 0.2s ease;
}

.hover-bg:hover {
    background-color: #f8f9fa;
}

/* Icon Shapes for Widgets */
.icon-shape {
    width: 60px;
    height: 60px;
}

/* Widget Colors (Soft Backgrounds for icons) */
.widget-blue .icon-shape {
    background-color: #e0f2fe;
    /* Light Blue */
    color: #0284c7;
}

.widget-green .icon-shape {
    background-color: #dcfce7;
    /* Light Green */
    color: #16a34a;
}

.widget-orange .icon-shape {
    background-color: #ffedd5;
    /* Light Orange */
    color: #ea580c;
}

/* Rank Badge Styles */
.rank-badge {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    font-size: 0.9rem;
}

/* Typography Helpers */
.fs-7 {
    font-size: 0.8rem;
}
</style>
