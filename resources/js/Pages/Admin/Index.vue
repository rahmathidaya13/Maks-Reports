<script setup>
import { computed, ref } from "vue";
import { Head, usePage, router, Link } from '@inertiajs/vue3';
import { debounce } from "lodash";
import moment from "moment";
moment.locale('id');
const props = defineProps({
    widgets: Object,
    leaderboards: Object,
    charts: Object,
    current_month: String,
});
console.log(props);
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

// Helper resolve image untuk produk
const resolveImage = (path) => {
    if (!path) return "https://ui-avatars.com/api/?name=??";
    // Jika link eksternal (http/https)
    if (path.startsWith("http")) return path;

    // Jika file lokal, tambahkan '/' agar root terbaca
    // Pastikan path di DB kamu 'storage/...' atau sesuaikan disini
    return `/storage/${path}`;
};


// 1. Buat array untuk Label (Sumbu X) -> Mengubah '2026-02-18' menjadi '18 Feb'
const chartLabels = computed(() => {
    // Pastikan data daily_sales ada sebelum di-map
    if (!props.charts?.daily_sales) return [];
    return props.charts.daily_sales.map(item => moment(item.date).format('DD MMM'));
});

// 2. Buat array untuk Data Angka (Sumbu Y) -> Mengambil nominal uang
const chartData = computed(() => {
    if (!props.charts?.daily_sales) return [];

    const countTrx = props.charts.daily_sales.map(item => {
        // Jika hasilnya null/undefined/NaN, otomatis jadikan 0.
        return Number(item.total_revenue) || 0;
    });

    // Cari nilai pendapatan tertinggi di minggu tersebut
    const maxRevenue = Math.max(...countTrx);

    // Atur warna dinamis (Tertinggi = Emas/Oranye, Sisanya = Biru Teal/Modern)
    const backgroundColors = countTrx.map(nilai =>
        nilai === maxRevenue && maxRevenue > 0
            ? 'rgba(245, 158, 11, 0.9)'  // Warna Emas/Oranye untuk yang tertinggi
            : 'rgba(14, 165, 233, 0.7)'  // Warna Biru Cerah (Teal) untuk sisanya
    );

    const hoverColors = countTrx.map(nilai =>
        nilai === maxRevenue && maxRevenue > 0
            ? 'rgba(217, 119, 6, 1)'     // Emas Gelap saat di-hover
            : 'rgba(2, 132, 199, 1)'     // Biru Gelap saat di-hover
    );
    // 4. Kembalikan format array datasets yang diminta Chart.js
    return [
        {
            label: 'Total Pendapatan',
            data: countTrx,
            backgroundColor: backgroundColors,
            hoverBackgroundColor: hoverColors,
            borderRadius: 10,       // Ujung batang membulat (Modern UI)
            borderSkipped: false,  // Membulat di semua sisi atas-bawah
            barPercentage: 0.5,   // Ketebalan batang (tidak terlalu gemuk)
        }
    ];
});
const reset = () => {
    router.get(route("admin.dashboard.reset"), {}, {
        preserveScroll: false,
        replace: true,
    });
}
</script>
<template>

    <Head title="Dashboard" />
    <app-layout>
        <template #content>

            <div class="d-flex justify-content-between align-items-center mb-4 py-3">
                <div>
                    <h4 class="fw-bold text-dark mb-1">
                        Dashboard Manajemen
                    </h4>
                </div>
                <div>
                    <button class="btn btn-white bg-white border shadow-sm">
                        <i class="fas fa-download me-2 text-muted"></i> Unduh Laporan
                    </button>
                    <button type="button" title="Segarkan" class="btn btn-success btn-sm rounded-3 px-3 ms-1"
                        @click.prevent="reset">
                        <i class="bi bi-arrow-clockwise fw-bold"></i>
                    </button>
                </div>
            </div>

            <callout />

            <div class="row g-2 pb-3">

                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm widget-card widget-blue rounded-4">
                        <div class="card-body p-3 d-block">
                            <div
                                class="icon-shape rounded-circle d-flex align-items-center justify-content-center me-3">
                                <i class="fas fa-shopping-cart fs-3"></i>
                            </div>
                            <div class="px-2 mt-3">
                                <p class="text-uppercase fw-semibold mb-1 text-muted"
                                    style="font-size: 0.7rem; letter-spacing: 0.5px;">Total Semua Transaksi Cabang</p>
                                <h5 class="fw-bold mb-0 text-dark">{{ formatNumber(widgets.total_sales) }} <span
                                        class="fs-6 fw-normal text-muted">Transaksi</span></h5>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm widget-card widget-green rounded-4">
                        <div class="card-body p-3 d-block">
                            <div
                                class="icon-shape rounded-circle d-flex align-items-center justify-content-center me-3">
                                <i class="fas fa-wallet fs-3"></i>
                            </div>
                            <div class="px-2 mt-3">
                                <p class="text-uppercase fw-semibold mb-1 text-muted"
                                    style="font-size: 0.7rem; letter-spacing: 0.5px;">Transaksi Lunas Semua Cabang</p>
                                <h5 class="fw-bold mb-0 text-success">{{ formatCurrency(widgets.total_revenue) }}</h5>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm widget-card widget-orange rounded-4">
                        <div class="card-body p-3 d-block">
                            <div
                                class="icon-shape rounded-circle d-flex align-items-center justify-content-center me-3">
                                <i class="fas fa-hand-holding-usd fs-3"></i>
                            </div>
                            <div class="px-2 mt-3">
                                <p class="text-uppercase fw-semibold mb-1 text-muted"
                                    style="font-size: 0.7rem; letter-spacing: 0.5px;">Transaksi DP semua Cabang</p>
                                <h5 class="fw-bold mb-0 text-warning">{{ formatCurrency(widgets.total_revenue_dp) }}
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm widget-card widget-red rounded-4">
                        <div class="card-body p-3 d-block">
                            <div
                                class="icon-shape rounded-circle d-flex align-items-center justify-content-center me-3">
                                <i class="fas fa-times-circle fs-3"></i>
                            </div>
                            <div class="px-2 mt-3">
                                <p class="text-uppercase fw-semibold mb-1 text-muted"
                                    style="font-size: 0.7rem; letter-spacing: 0.5px;">Transaksi Dibatalkan</p>
                                <h5 class="fw-bold mb-0 text-danger">{{ formatCurrency(widgets.total_revenue_batal) }}
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="row g-4 pb-5">

                <div class="col-12">
                    <div class="card border shadow-sm rounded-4 h-100 overflow-hidden">
                        <div
                            class="card-header bg-white border-0 py-4 px-4 d-flex align-items-center justify-content-between">
                            <h5 class="m-0 fw-bold text-dark">
                                <i class="bi bi-bar-chart-line me-2 text-primary"></i>Grafik Pendapatan 7 Hari Terakhir
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-wrapper">
                                <chart-bar type="pie" :height="400" :labels="chartLabels" :datasets="chartData" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden">
                        <div
                            class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-store text-primary me-2"></i>Top 10
                                Cabang Terbaik</h6>
                            <span class="badge bg-light text-muted border">{{ props.current_month ?? 'Bulan Ini'
                            }}</span>
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
                                            <small class="text-muted">{{ formatNumber(branch.transactions_count) }}
                                                Transaksi</small>
                                        </div>
                                    </div>

                                    <div class="text-end d-flex flex-column gap-1 align-items-end">
                                        <span
                                            class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-10 px-2 py-1"
                                            style="font-size: 0.7rem; width: fit-content;">
                                            Lunas: {{ formatCurrency(branch.total_lunas || 0) }}
                                        </span>
                                        <span
                                            class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-10 px-2 py-1"
                                            style="font-size: 0.7rem; width: fit-content;">
                                            DP: {{ formatCurrency(branch.total_dp || 0) }}
                                        </span>
                                        <span
                                            class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-10 px-2 py-1"
                                            style="font-size: 0.7rem; width: fit-content;">
                                            Dibatalkan: {{ formatCurrency(branch.total_batal || 0) }}
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
                            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-box-open text-warning me-2"></i>Top 10
                                Produk Terlaris</h6>
                            <span class="badge bg-light text-muted border">{{ props.current_month ?? 'Bulan Ini'
                            }}</span>
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
.hover-bg {
    transition: background-color 0.2s ease;
}

.hover-bg:hover {
    background-color: #f8f9fa;
}

/* Icon Shapes for Widgets */
.icon-shape {
    width: 55px;
    height: 55px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.widget-card {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
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

.widget-red .icon-shape {
    background-color: #fee2e2;
    /* Light Red */
    color: #b91c1c;
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
