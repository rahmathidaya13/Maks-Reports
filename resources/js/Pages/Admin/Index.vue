<script setup>
import { computed, ref, reactive, watch } from "vue";
import { Head, usePage, router, Link } from '@inertiajs/vue3';
import { debounce } from "lodash";
import moment from "moment";
import ExportModal from "./Modal/ExportModal.vue";
moment.locale('id');
const props = defineProps({
    widgets: Object,
    leaderboards: Object,
    charts: Object,
    current_month: String,
    filters: Object
});

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
    return props.charts.daily_sales.map(item => moment(item.date).format('DD MMM YYYY'));
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

    // 🎨 Palette 7 warna modern (bisa kamu ubah sesuai selera)
    const colorPalette = [
        'rgba(59, 130, 246, 0.8)',   // Biru
        'rgba(16, 185, 129, 0.8)',   // Hijau
        'rgba(245, 158, 11, 0.8)',   // Oranye
        'rgba(239, 68, 68, 0.8)',    // Merah
        'rgba(139, 92, 246, 0.8)',   // Ungu
        'rgba(236, 72, 153, 0.8)',   // Pink
        'rgba(20, 184, 166, 0.8)',   // Teal
    ];

    const hoverPalette = [
        'rgba(37, 99, 235, 1)',
        'rgba(5, 150, 105, 1)',
        'rgba(217, 119, 6, 1)',
        'rgba(220, 38, 38, 1)',
        'rgba(124, 58, 237, 1)',
        'rgba(219, 39, 119, 1)',
        'rgba(13, 148, 136, 1)',
    ];

    const backgroundColors = countTrx.map((_, index) =>
        colorPalette[index % colorPalette.length]
    );

    const hoverColors = countTrx.map((_, index) =>
        hoverPalette[index % hoverPalette.length]
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
const isLoading = ref(false);

const reset = () => {
    isLoading.value = true
    router.get(route("admin.dashboard.reset"), {}, {
        onFinish: () => isLoading.value = false,
        preserveScroll: false,
        replace: true,
    });
}

// tampilkan modal
const modals = ref(false)

// filter berdasarkan tanggal
const filterForm = reactive({
    start_date: props.filters?.start_date || '',
    end_date: props.filters?.end_date || '',
});

const applyFilter = debounce(() => {
    isLoading.value = true
    router.get(route('admin.dashboard.index'), { // Pastikan 'dashboard' adalah nama route kamu
        start_date: filterForm.start_date,
        end_date: filterForm.end_date
    }, {
        preserveState: true, // Menjaga scroll dan state komponen
        replace: true, // Agar tidak memenuhi history browser
        preserveScroll: true,
        only: ['widgets', 'leaderboards', 'charts', 'current_month', 'filters'],
        onFinish: () => isLoading.value = false
    });
}, 500);
// aktifkan button bila tanggal diisi
const hasActiveFilter = computed(() => {
    if (!filterForm.start_date && !filterForm.end_date) return true;
    if (new Date(filterForm.start_date) > new Date(filterForm.end_date)) return true;

    const initialStart = props.filters?.start_date;
    const initialEnd = props.filters?.end_date;

    if (filterForm.start_date === initialStart && filterForm.end_date === initialEnd) {
        return true;
    }
    return false
});
console.log(props.filters);
</script>
<template>

    <Head title="Dashboard Manajemen Admin" />
    <app-layout>
        <template #content>

            <div class="d-xl-flex  justify-content-xl-between align-items-center mb-0 mb-xl-3 py-3">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-white text-primary shadow-sm rounded-3 d-flex align-items-center justify-content-center me-2"
                        style="width: 50px; height: 50px; font-size: 1.7rem;">
                        <i class="fas fa-chart-pie text-primary"></i>
                    </div>
                    <div class="w-90">
                        <h4 class="fw-bold text-dark mb-0 ls-tight text-capitalize"> Dashboard Manajemen</h4>
                    </div>
                </div>

                <div class="d-flex gap-2 align-items-center bg-white px-3 p-3 rounded-3 shadow-sm border text-dark">
                    <input type="date" v-model="filterForm.start_date" class="form-control form-control-sm">
                    <span class="text-muted small">s/d</span>
                    <input type="date" v-model="filterForm.end_date" class="form-control form-control-sm">
                    <button :disabled="hasActiveFilter" type="button" @click.prevent="applyFilter"
                        class="btn btn-primary btn-sm px-3 fw-bold">
                        Terapkan
                    </button>
                    <div class="vr my-1"></div>
                    <button type="button" @click.prevent="modals = true" class="btn btn-success border btn-sm"
                        title="Unduh Laporan">
                        Unduh
                    </button>
                    <div class="vr my-1"></div>
                    <button type="button" @click.prevent="reset" class="btn btn-light border btn-sm"
                        title="Reset ke Bulan Ini">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>

            <callout />

            <div class="position-relative">
                <div v-if="isLoading" class="loading-overlay d-flex justify-content-center align-items-start pt-5">
                    <div
                        class="loader-content shadow-lg rounded-pill px-4 py-2 bg-white d-flex align-items-center border">
                        <div class="spinner-border spinner-border-sm text-primary me-3"></div>
                        <span class="fw-bold text-dark small">Sedang memuat data...</span>
                    </div>
                </div>
                <div class="row g-2 pb-3" :class="['dashboard-content', isLoading ? 'is-loading' : '']">

                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm widget-card widget-blue rounded-4">
                            <div class="card-body p-3 d-block">
                                <div
                                    class="icon-shape rounded-circle d-flex align-items-center justify-content-center me-3">
                                    <i class="fas fa-shopping-cart fs-3"></i>
                                </div>
                                <div class="px-2 mt-3">
                                    <p class="text-uppercase fw-semibold mb-1 text-muted"
                                        style="font-size: 0.7rem; letter-spacing: 0.5px;">Total Semua Transaksi Cabang
                                    </p>
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
                                        style="font-size: 0.7rem; letter-spacing: 0.5px;">Transaksi Lunas Semua Cabang
                                    </p>
                                    <h5 class="fw-bold mb-0 text-success">{{ formatCurrency(widgets.total_revenue) }}
                                    </h5>
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
                                    <h5 class="fw-bold mb-0 text-danger">{{ formatCurrency(widgets.total_revenue_batal)
                                        }}
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="row g-4 pb-5" :class="['dashboard-content', isLoading ? 'is-loading' : '']">

                    <div class="col-12">
                        <div class="card border shadow-sm rounded-4 h-100 overflow-hidden">
                            <div
                                class="card-header bg-white border-0 py-4 px-4 d-flex align-items-center justify-content-between">
                                <h5 class="m-0 fw-bold text-dark">
                                    <i class="bi bi-bar-chart-line me-2 text-primary"></i>Grafik Pendapatan 7 Hari
                                    Terakhir
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
                                                <h6 class="mb-0 fw-bold text-dark text-capitalize">{{ branch.name }}
                                                </h6>
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
                                <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-box-open text-warning me-2"></i>Top
                                    10
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
                                                <h6 class="mb-0 fw-bold text-dark text-truncate" :title="product.name">
                                                    {{
                                                        product.name }}</h6>
                                                <small class="text-muted text-uppercase"
                                                    style="font-size: 0.7rem;">Terjual
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
            </div>

            <!-- <TransactionDetailModal :show="modals.transactionDetail" :transaction="selectedData"
                @update:show="modals.transactionDetail = $event" /> -->
            <ExportModal :show="modals" @update:show="modals = $event" />
        </template>
    </app-layout>
</template>
<style scoped>
/* Dashboard Content Blur */
.dashboard-content {
    transition: filter 0.3s ease, opacity 0.3s ease;
}

.dashboard-content.is-loading {
    /* Cegah klik saat loading */
    filter: blur(4px);
    opacity: 0.6;
    pointer-events: none;
}

/* Loader Styling */
.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.4);
    backdrop-filter: blur(2px);
    z-index: 20;
    transition: all 0.3s ease;
}

/* Agar tetap terlihat saat scroll */
.loader-content {
    position: sticky;
    top: 20px;
    z-index: 21;
}

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
