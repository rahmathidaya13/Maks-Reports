<script setup>
import { computed, ref } from "vue";
import { Head, usePage, router, Link } from '@inertiajs/vue3';
import { debounce } from "lodash";
const props = defineProps({
    stats: Object,
    chart_data: Object,
    top_products: Array,
    filters: Object,
})
const selectedMonth = ref(props.filters?.month || new Date().getMonth() + 1);
const selectedYear = ref(props.filters?.year || new Date().getFullYear());
const isLoading = ref(false);


// List bulan untuk dropdown
const months = [
    { value: 1, label: 'Januari' },
    { value: 2, label: 'Februari' },
    { value: 3, label: 'Maret' },
    { value: 4, label: 'April' },
    { value: 5, label: 'Mei' },
    { value: 6, label: 'Juni' },
    { value: 7, label: 'Juli' },
    { value: 8, label: 'Agustus' },
    { value: 9, label: 'September' },
    { value: 10, label: 'Oktober' },
    { value: 11, label: 'November' },
    { value: 12, label: 'Desember' }
];
// List tahun (5 tahun terakhir)
const years = Array.from({ length: 5 }, (_, i) => new Date().getFullYear() - i);

const applyFilter = debounce(() => {
    isLoading.value = true
    router.get(route('home'), { // Pastikan 'dashboard' adalah nama route kamu
        month: selectedMonth.value,
        year: selectedYear.value
    }, {
        preserveState: true, // Menjaga scroll dan state komponen
        replace: true, // Agar tidak memenuhi history browser
        preserveScroll: true,
        only: ['stats', 'chart_data', 'top_products', 'filters'],
        onFinish: () => isLoading.value = false
    });
}, 500);
function formatCurrency(value) {
    if (!value) return "0";
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value);
}
const reset = () => {
    isLoading.value = true
    router.get(route("home.reset"), {}, {
        preserveScroll: false,
        replace: true,
        onFinish: () => isLoading.value = false
    });
}
</script>
<template>

    <Head title="Dashboard" />
    <app-layout>
        <template #content>
            <bread-crumbs :home="false" icon="fas fa-tachometer-alt" title="Dashboard Penjualan"
                :items="[{ text: 'Dashboard' }]" />
            <callout />

            <div v-if="$page.props.pending_request_count > 0" class="col-12 mb-4">
                <div class="alert alert-warning border-warning border-opacity-25 d-flex align-items-center shadow-sm rounded-4"
                    role="alert">
                    <div class="bg-warning bg-opacity-25 text-warning rounded-circle p-2 me-3 d-flex align-items-center justify-content-center"
                        style="width: 45px; height: 45px;">
                        <i class="fas fa-bell fs-4"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="fw-bold text-dark mb-1">Ada Permintaan Baru!</h6>
                        <p class="mb-0 small text-dark text-opacity-75">
                            Terdapat <strong>{{ $page.props.pending_request_count }}</strong> permintaan harga/stok
                            dari cabang yang menunggu persetujuan Anda.
                        </p>
                    </div>
                    <Link :href="route('admin.request.index')" class="btn btn-warning btn-sm fw-bold px-3 ms-3">
                        Cek Sekarang <i class="fas fa-arrow-right ms-1"></i>
                    </Link>
                </div>
            </div>

            <div class="row pb-5">
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-body p-4">
                            <div
                                class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3 text-dark">

                                <div>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3 me-3">
                                            <i class="fas fa-chart-pie fs-3"></i>
                                        </div>
                                        <div>
                                            <h5 class="fw-bold mb-0 text-dark">Analisis Performa Penjualan</h5>
                                            <p class="text-muted small mb-0">
                                                <i class="bi bi-calendar3 me-1"></i> Periode:
                                                <span class="fw-semibold text-primary">{{ stats.current_month_name
                                                    }}</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="d-flex gap-2 bg-white p-2 rounded-3 shadow-sm border text-dark align-items-center">
                                    <select name="month" id="month" v-model="selectedMonth"
                                        class="form-select form-select-sm border-0 focus-none fw-semibold">
                                        <option v-for="month in months" :key="month.value" :value="month.value">{{
                                            month.label
                                            }}
                                        </option>
                                    </select>
                                    <div class="vr my-1"></div>
                                    <select name="year" id="year" v-model="selectedYear"
                                        class="form-select form-select-sm border-0 focus-none fw-semibold">
                                        <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
                                    </select>
                                    <button type="button" title="Apply"
                                        class="btn btn-primary btn-sm rounded-3 px-3 ms-1" @click.prevent="applyFilter">
                                        <i class="bi bi-filter"></i>
                                    </button>
                                    <div class="vr my-1"></div>
                                    <button type="button" title="Segarkan"
                                        class="btn btn-success btn-sm rounded-3 px-3 ms-1" @click.prevent="reset">
                                        <i class="bi bi-arrow-clockwise fw-bold"></i>
                                    </button>
                                </div>
                            </div>



                            <div class="position-relative">
                                <div v-if="isLoading"
                                    class="loading-overlay d-flex justify-content-center align-items-start pt-5">
                                    <div
                                        class="loader-content shadow-lg rounded-pill px-4 py-2 bg-white d-flex align-items-center border">
                                        <div class="spinner-border spinner-border-sm text-primary me-3"></div>
                                        <span class="fw-bold text-dark small">Memproses...</span>
                                    </div>
                                </div>
                                <div class="row g-3" :class="['dashboard-content', isLoading ? 'is-loading' : '']">

                                    <div class="col-md-12 col-xl-4 col-12">
                                        <div class="card border shadow-sm overflow-hidden rounded-4 h-100">
                                            <div class="card-body p-4 position-relative text-bg-white">

                                                <div class="d-flex align-items-center ">
                                                    <div
                                                        class="bg-success bg-opacity-10 text-success rounded-4 me-3 icon-shape">
                                                        <i class="bi bi-wallet2 fs-1"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="text-muted small fw-bold text-uppercase mb-1">
                                                            Estimasi
                                                            Bonus</h6>
                                                        <h5 class="fw-extrabold mb-1">{{
                                                            formatCurrency(stats.bonus) }}</h5>
                                                        <span
                                                            class="mb-1 badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill">1%
                                                            Bonus</span>
                                                        <p class="text-muted small mb-0"
                                                            title="Hanya dihitung jika transaksi penjualan sudah lunas">
                                                            <i class="bi bi-info-circle me-1"></i>
                                                            Hanya transaksi lunas
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="position-absolute bottom-0 end-0 opacity-05 mb-n3 me-n2">
                                                    <i class="bi bi-currency-dollar display-2"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12 col-xl-4 col-12">
                                        <div class="card border shadow-sm overflow-hidden rounded-4 h-100">
                                            <div class="card-body p-4 position-relative text-bg-white">

                                                <div>
                                                    <div class="d-flex align-items-center ">
                                                        <div
                                                            class="bg-warning bg-opacity-10 text-warning rounded-4 me-3 icon-shape">
                                                            <i class="bi bi-clock-history fs-1"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="text-muted small fw-bold text-uppercase mb-1">
                                                                Transaksi
                                                                DP</h6>
                                                            <h5 class="fw-extrabold mb-1">{{
                                                                formatCurrency(stats.remaining_payment) }}</h5>
                                                            <span
                                                                class="mb-1 badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill">
                                                                {{ props.stats.products_sold_dp
                                                                    ?? 0 }} Unit dari {{
                                                                    props.stats.transactions_count_dp_inv
                                                                }} Invoice</span>
                                                            <p class="text-muted small mb-0"
                                                                title="Terhitung dari transaksi yang melakukan pembayaran dengan DP (Down Payment)">
                                                                <i class="bi bi-info-circle me-1"></i>
                                                                Omset Penjualan DP
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 col-xl-4 col-12">
                                        <div class="card border shadow-sm overflow-hidden rounded-4 h-100">
                                            <div class="card-body p-4 position-relative text-bg-white">

                                                <div>
                                                    <div class="d-flex align-items-center ">
                                                        <div
                                                            class="bg-primary bg-opacity-10 text-primary rounded-4 me-3 icon-shape">
                                                            <i class="bi bi-cash-coin fs-1"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="text-muted small fw-bold text-uppercase mb-1">
                                                                Transaksi
                                                                Lunas</h6>
                                                            <h5 class="fw-extrabold mb-1">{{
                                                                formatCurrency(stats.sales_volume) }}</h5>
                                                            <span
                                                                class="mb-1 badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill">
                                                                {{ stats.products_sold
                                                                    ?? 0 }} Unit dari {{ props.stats.transactions_count_inv
                                                                }} Invoice </span>
                                                            <p class="text-muted small mb-0"
                                                                title="Terhitung dari transaksi yang sudah lunas atau yang sudah melunasi transaksi pembayaran">
                                                                <i class="bi bi-info-circle me-1"></i>
                                                                Omset Penjualan Lunas
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12 col-xl-8 col-12">
                                        <div class="card border shadow-sm rounded-4 h-100 overflow-hidden">
                                            <div
                                                class="card-header bg-white border-0 py-4 px-4 d-flex align-items-center justify-content-between">
                                                <h5 class="m-0 fw-bold text-dark">
                                                    <i class="bi bi-bar-chart-line me-2 text-primary"></i>Statistik
                                                    Penjualan
                                                    Harian
                                                </h5>
                                            </div>
                                            <div class="card-body px-4 pb-4">
                                                <div class="chart-wrapper">
                                                    <chart-bar type="bar" :height="300"
                                                        :labels="props.chart_data.labels"
                                                        :data="props.chart_data.values" label="Total Omset (Rp)" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12 col-xl-4 col-12">
                                        <div class="card border shadow-sm overflow-hidden h-100 rounded-4">
                                            <div class="card-body p-4">
                                                <div class="row align-items-center">
                                                    <div class="col-7">
                                                        <h6 class=" small fw-bold text-uppercase mb-2">
                                                            <i
                                                                class="bi bi-graph-up-arrow me-2 fs-4 text-primary rounded-3"></i>
                                                            <span class="text-muted">Pertumbuhan</span>
                                                        </h6>
                                                        <h4 class="fw-extrabold mb-1"
                                                            :class="stats.growth >= 0 ? 'text-success' : 'text-danger'">
                                                            {{ stats.growth >= 0 ? '+' : '' }}{{ stats.growth }}%
                                                        </h4>
                                                        <span class="text-muted" style="font-size: 0.8rem;">Vs bulan
                                                            lalu</span>
                                                    </div>
                                                </div>

                                                <div class="mt-3 pt-3 border-top">
                                                    <div
                                                        class="d-flex align-items-center justify-content-between small">
                                                        <span class="text-muted">Status:</span>
                                                        <span
                                                            :class="stats.growth >= 0 ? 'text-success' : 'text-danger'"
                                                            class="fw-bold">
                                                            <i
                                                                :class="stats.growth >= 0 ? 'bi bi-caret-up-fill' : 'bi bi-caret-down-fill'"></i>
                                                            {{ stats.growth >= 0 ? 'Profit Naik' : 'Target Turun' }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>



                                    <div class="col-lg-12">
                                        <div class="card border shadow-sm rounded-4 h-100 overflow-hidden">
                                            <div class="card-header bg-white border-0 py-4 px-4">
                                                <h5 class="m-0 fw-bold text-dark">
                                                    <i class="bi bi-star me-2 text-warning"></i>Top Produk
                                                </h5>
                                            </div>
                                            <div class="card-body p-0">
                                                <div class="table-responsive">
                                                    <table class="table table-hover align-middle mb-0">
                                                        <thead class="bg-light border-0">
                                                            <tr>
                                                                <th
                                                                    class="ps-4 py-3 text-muted fw-bold small text-uppercase">
                                                                    Produk
                                                                </th>
                                                                <th
                                                                    class="text-end pe-4 py-3 text-muted fw-bold small text-uppercase">
                                                                    Terjual</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr v-for="(prod, index) in top_products" :key="index"
                                                                class="border-transparent">
                                                                <td class="ps-4 py-3">
                                                                    <div class="d-flex align-items-center">
                                                                        <div class="rank-circle me-3">{{ index + 1 }}
                                                                        </div>
                                                                        <span class="fw-semibold text-dark">{{ prod.name
                                                                            }}</span>
                                                                    </div>
                                                                </td>
                                                                <td class="text-end pe-4 py-3">
                                                                    <span
                                                                        class="badge bg-primary-subtle text-primary border border-primary border-opacity-10 px-3 py-2 rounded-pill fw-bold">
                                                                        {{ prod.total }} unit
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                            <tr v-if="top_products.length === 0">
                                                                <td colspan="2" class="text-center py-5">
                                                                    <p class="text-muted small">Belum ada data penjualan
                                                                    </p>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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

/* Menambah ketebalan font agar lebih premium */
.fw-extrabold {
    font-weight: 800;
}

.ls-wide {
    letter-spacing: 0.05em;
}

.opacity-05 {
    opacity: 0.05;
}

/* Custom Badge colors (Bootstrap 5.3+ style) */
.bg-primary-subtle {
    background-color: #e7f1ff;
}

/* Rank Circle untuk Top Products */
.rank-circle {
    width: 35px;
    height: 35px;
    background: #f8f9fa;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: bold;
    color: #6c757d;
    border: 1px solid #dee2e6;
}

/* Custom Background for Primary Card */
.bg-gradient {
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%) !important;
}

/* Hilangkan outline biru default bootstrap pada select agar lebih clean */
.focus-none:focus {
    border-color: transparent;
    box-shadow: none;
}

.form-select-sm {
    cursor: pointer;
    background-color: transparent;
}

/* Card Styling */
.stat-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05) !important;
    border: 1px solid #e6e4e4 !important;
}

.icon-shape {
    width: 70px;
    height: 70px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    font-size: 1.25rem;
}

.badge-growth {
    padding: 4px 12px;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 700;
}

.fw-extrabold {
    font-weight: 800;
}
</style>
