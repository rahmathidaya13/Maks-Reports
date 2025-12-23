<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { debounce } from "lodash";
import { swalAlert, swalConfirmDelete } from "@/helpers/swalHelpers";
import { highlight } from "@/helpers/highlight";
import { formatText } from "@/helpers/formatText";
import moment from "moment";
moment.locale('id');

const page = usePage();
const messageTheme = ref('')
const message = computed(() => {
    if (page.props.flash.message) {
        messageTheme.value = 'success'
        page.props.flash.message
    } else if (page.props.flash.warning) {
        messageTheme.value = 'warning'
        page.props.flash.warning
    } else {
        messageTheme.value = ''
    }

    return page.props.flash.message || page.props.flash.warning
});

const props = defineProps({
    transaction: Object,
    filters: Object,
});
// cek permission
const perm = page.props.auth.user.permissions

const filters = reactive({
    date_filter: props.filters.date_filter ?? null,
    status: props.filters.status ?? null,
    keyword: props.filters.keyword ?? '',
    limit: props.filters.limit ?? null,
    order_by: props.filters.order_by ?? null,
    page: props.filters?.page ?? 1,
})

const isLoading = ref(false)
const liveSearch = debounce((e) => {
    isLoading.value = true
    router.get(route("transaction"), filters, {
        preserveScroll: true,
        replace: true,
        preserveState: true,
        only: ["transaction", "filters"],
        onFinish: () => isLoading.value = false
    });
}, 500);
watch(
    () =>
        filters.keyword,
    () => {
        liveSearch();
    }
);
const handleApply = () => {
    liveSearch();
}
const handleReset = () => {
    filters.keyword = ''
    filters.status = null
    filters.date_filter = null
    filters.limit = null
    filters.order_by = null

    // Langsung cari data bersih
    liveSearch()
}


const header = [
    { label: "No", key: "__index" },
    { label: "Tgl.Transaksi", key: "transaction_date" },
    { label: "Tgl.Pembayaran", key: "-te" },
    { label: "Nama Pelanggan", key: "customer_id" },
    { label: "Nama Barang/Produk", key: "product_id" },
    { label: "Harga Asli", key: "price_original" },
    { label: "Harga Diskon", key: "price_discount" },
    { label: "Total Harga", key: "price_final" },
    { label: "Sudah Dibayar", key: "total_paid" },
    { label: "Sisa Pembayaran", key: "-" },
    { label: "Status", key: "status" },
    { label: "Aksi", key: "-" },
];



// CRUD OPERATION
const loaderActive = ref(null)
const create = () => {
    loaderActive.value?.show("Memproses...");
    router.get(route("transaction.create"), {}, {
        onFinish: () => {
            loaderActive.value?.hide()
        }
    });
}

const edit = (id) => {
    loaderActive.value?.show("Sedang memuat data...");
    router.get(route("transaction.edit", id), {}, {
        onFinish: () => loaderActive.value?.hide()
    });
}

const deleted = (nameRoute, data) => {
    swalConfirmDelete({
        title: 'Hapus',
        text: `Kamu ingin menghapus Data Transaksi ${formatText(data)} ?`,
        confirmText: 'Ya, Hapus!',
        onConfirm: () => {
            loaderActive.value?.show("Sedang memuat data...");
            router.delete(route(nameRoute, data.transaction_id), {
                onFinish: () => loaderActive.value?.hide(),
                preserveScroll: false,
                replace: true
            });
        },
    })
}
// end CRUD OPERATION

// MULTIPLE DELETE
const selectedRow = ref([]);
const isVisible = ref(false);

const isAllSelected = computed(() => {
    const rows = props.transaction?.data ?? [];
    return rows.length > 0 && selectedRow.value.length === rows.length;
})

function deleteSelected() {
    if (!selectedRow.value.length) {
        return swalAlert('Peringatan', 'Tidak ada data yang dipilih.', 'warning');
    }
    swalConfirmDelete({
        title: 'Hapus Data Terpilih',
        text: `Yakin ingin menghapus ${selectedRow.value.length} data terpilih?`,
        confirmText: 'Ya, Hapus Semua!',
        onConfirm: () => {
            loaderActive.value?.show("Sedang memuat data...");
            router.post(route('transaction.destroy_all'), { all_id: selectedRow.value }, {
                onFinish: () => loaderActive.value?.hide(),
                preserveScroll: true,
                preserveState: false,
            })
        },
    })
}
const isSelected = (id) => {
    return selectedRow.value.includes(id);
}
const toggleAll = (evt) => {
    if (evt.target.checked) {
        selectedRow.value = props.transaction?.data.map(r => r.transaction_id);
    } else {
        selectedRow.value = [];
    }
}
watch(selectedRow, (val) => {
    if (val.length > 0) {
        isVisible.value = true
    } else {
        isVisible.value = false
    }
})
// END MULTIPLE DELETE

const repayment = (id) => {
    loaderActive.value?.show("Sedang memuat data...");
    router.get(route("transaction.show", id), {}, {
        onFinish: () => loaderActive.value?.hide()
    });
}

function formatCurrency(value) {
    if (!value) return "0";
    return new Intl.NumberFormat('id-ID', {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value)
}

// SETUP DATE
const getPaymentDate = (payments, type) => {
    if (!payments) return '-'
    const p = payments.find(p => p.payment_type === type);
    return p ? moment(p.payment_date).format('DD/MM/YYYY') : '-'
}
function daysTranslate(dayValue) {
    const dayConvert = {
        "Sunday": "Minggu",
        "Monday": "Senin",
        "Tuesday": "Selasa",
        "Wednesday": "Rabu",
        "Thursday": "Kamis",
        "Friday": "Jumat",
        "Saturday": "Sabtu",
    };
    const dayName = moment(dayValue).format('dddd');
    const dateFormat = moment(dayValue).format('DD/MM/YYYY');
    return dayConvert[dayName] + ", " + dateFormat ?? dayName;
}
// END DATE

// cek apakah ada filter yang terpilih
const hasActiveFilter = computed(() => {
    return (
        filters.keyword !== '' ||
        filters.status !== null ||
        filters.date_filter !== null ||
        filters.limit !== null ||
        filters.order_by !== null
    )
})
const fileterFields = computed(() => [
    {
        key: 'keyword',
        label: 'Pencarian',
        type: 'text',
        col: 'col-xl-8 col-12',
        props: {
            placeholder: 'Masukan nama dan produk...',
            inputClass: 'border-0 border input-height-1',
            isValid: false,
            autofocus: true
        }
    },
    {
        key: 'status',
        label: 'Status',
        type: 'select',
        col: 'col-xl-4 col-md-6 col-6',
        props: {
            selectClass: 'border-0 border input-height-1',
            isValid: false,
        },
        options: [
            { value: null, label: 'Pilih Status Pembayaran' },
            { value: 'payment', label: 'Belum Lunas' },
            { value: 'repayment', label: 'Lunas' },
        ]
    },
    {
        key: 'date_filter',
        label: 'Filter Tanggal',
        type: 'date',
        col: 'col-xl-4 col-md-6 col-6',
        props: {
            inputClass: 'border-0 border input-height-1',
            isValid: false,
        }
    },
    {
        key: 'limit',
        label: 'Batas',
        type: 'select',
        col: 'col-xl-4 col-md-6 col-6',
        props: {
            selectClass: 'border-0 border input-height-1',
            isValid: false,
        },
        options: [
            { value: null, label: 'Pilih Batas Data' },
            { value: 10, label: '10' },
            { value: 20, label: '20' },
            { value: 30, label: '30' },
            { value: 50, label: '50' },
            { value: 100, label: '100' },
        ]
    },
    {
        key: 'order_by',
        label: 'Urutan',
        type: 'select',
        col: 'col-xl-4 col-md-6 col-6',
        props: {
            selectClass: 'border-0 border input-height-1',
            isValid: false,
        },
        options: [
            { value: null, label: 'Pilih Urutan' },
            { value: 'desc', label: 'Terbaru' },
            { value: 'asc', label: 'Terlama' },
        ]
    },
    //  button trigger
    {
        key: 'reset',
        label: 'Bersihkan',
        type: 'button',
        name: 'reset',
        icon: 'fas fa-undo',
        class: !hasActiveFilter.value ? 'btn-secondary' : 'btn-outline-danger',
        disabled: !hasActiveFilter.value,
        handler: () => handleReset()
    },
    {
        key: 'apply',
        label: 'Terapkan',
        type: 'button',
        name: 'apply',
        icon: 'fas fa-filter',
        class: !hasActiveFilter.value ? 'btn-secondary' : 'btn-primary',
        disabled: !hasActiveFilter.value,
        handler: () => handleApply()
    },
]);
</script>
<template>

    <Head title="Halaman Transaksi" />
    <app-layout>
        <template #content>
            <loader-page ref="loaderActive" />
            <bread-crumbs :home="false" icon="fas fa-money-bill" title="Daftar Transaksi"
                :items="[{ text: 'Daftar Transaksi' }]" />

            <callout :type="messageTheme" :duration="10" :message="message" />

            <div class="row pb-3">
                <div class="col-xl-12 col-12 mb-3">
                    <filter-dynamic title="Filter" v-model="filters" :fields="fileterFields" />
                </div>

                <div class="col-xl-12 col-12">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">

                        <div
                            class="card-header bg-white py-3 px-4 border-bottom-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h5 class="fw-bold mb-0 text-dark">Data Transaksi</h5>
                                <p class="text-muted small mb-0">Kelola riwayat transaksi dan pembayaran pelanggan.</p>
                            </div>

                            <div class="d-flex gap-2">
                                <transition name="fade">
                                    <button v-if="perm.includes('transaction.delete') && selectedRow.length > 0"
                                        @click="deleteSelected" type="button"
                                        class="btn btn-danger rounded-3 shadow-sm px-3 d-flex align-items-center">
                                        <i class="fas fa-trash-alt me-2"></i>
                                        Hapus ({{ selectedRow.length }})
                                    </button>
                                </transition>

                                <button v-if="perm.includes('transaction.create')" type="button" @click.prevent="create"
                                    class="btn btn-primary rounded-3 shadow-sm px-3 fw-bold">
                                    <i class="fas fa-plus me-1"></i> Transaksi Baru
                                </button>
                            </div>
                        </div>

                        <div v-if="isLoading"
                            class="position-absolute w-100 h-100 bg-white opacity-75 d-flex align-items-center justify-content-center"
                            style="z-index: 10;">
                            <div class="text-center">
                                <div class="spinner-border text-primary mb-2" role="status"></div>
                                <p class="fw-bold text-dark">Memproses...</p>
                            </div>
                        </div>

                        <div class="card-body p-0" :class="['blur-area', isLoading ? 'is-blurred' : '']">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle text-nowrap">
                                    <thead class="bg-light text-uppercase text-secondary fw-bold">
                                        <tr>
                                            <th class="text-center" v-if="perm.includes('transaction.delete')">
                                                <div class="form-check d-flex justify-content-center">
                                                    <input :disabled="!transaction?.data.length" type="checkbox"
                                                        class="form-check-input shadow-none cursor-pointer"
                                                        :checked="isAllSelected" @change="toggleAll($event)" />
                                                </div>
                                            </th>
                                            <th v-for="col in header" :key="col.key" class="text-center text-nowrap ">
                                                {{ col.label }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-top-0">
                                        <tr v-if="!transaction?.data.length">
                                            <td :colspan="header.length + (perm.includes('transaction.delete') ? 1 : 0)"
                                                class="text-center py-5">
                                                <div class="py-4">
                                                    <i class="fas fa-inbox fa-3x text-muted opacity-25 mb-3"></i>
                                                    <p class="text-muted fw-semibold">Belum ada data transaksi.</p>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr v-for="(item, index) in transaction?.data" :key="index"
                                            :class="{ 'bg-primary bg-opacity-10': isSelected(item.transaction_id) }">

                                            <td class="text-center" v-if="perm.includes('transaction.delete')">
                                                <div class="form-check d-flex justify-content-center">
                                                    <input type="checkbox"
                                                        class="form-check-input shadow-none cursor-pointer"
                                                        :value="item.transaction_id" v-model="selectedRow" />
                                                </div>
                                            </td>

                                            <td class="text-center text-muted fw-bold small">
                                                {{ index + 1 + (transaction?.current_page - 1) * transaction?.per_page
                                                }}
                                            </td>

                                            <td class="text-center">
                                                <div class="fw-semibold text-dark">{{
                                                    daysTranslate(item.transaction_date).split(',')[0] }}</div>
                                                <div class="small text-muted" style="font-size: 0.9rem;">{{
                                                    daysTranslate(item.transaction_date).split(',')[1] }}</div>
                                            </td>

                                            <td class="text-start" style="min-width: 200px;">
                                                <div class="d-flex flex-column gap-2">
                                                    <div
                                                        class="d-flex justify-content-between align-items-center border rounded-3 p-1 px-2 bg-light bg-opacity-50">
                                                        <span
                                                            class="badge bg-warning text-dark bg-opacity-25 rounded-pill"
                                                            style="font-size: 0.65rem;">
                                                            DP 50%
                                                        </span>
                                                        <span class="small fw-bold text-dark">{{
                                                            getPaymentDate(item.payments, 'payment') }}
                                                        </span>
                                                    </div>
                                                    <div v-if="item.status === 'repayment'"
                                                        class="d-flex justify-content-between align-items-center border rounded-3 p-1 px-2 bg-success bg-opacity-10">
                                                        <span
                                                            class="badge bg-success text-success bg-opacity-25 rounded-pill"
                                                            style="font-size: 0.65rem;">Lunas</span>
                                                        <span class="small fw-bold text-success">{{
                                                            getPaymentDate(item.payments, 'repayment') }}</span>
                                                    </div>
                                                </div>
                                            </td>

                                            <td class="text-center">
                                                <div class="d-flex align-items-center justify-content-center gap-2">
                                                    <div class="avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center"
                                                        style="width: 30px; height: 30px;">
                                                        <i class="fas fa-user-circle"></i>
                                                    </div>
                                                    <span class="fw-semibold text-capitalize text-dark"
                                                        v-html="highlight(item.customer?.customer_name, filters.keyword) ?? '-'"></span>
                                                </div>
                                            </td>

                                            <td class="text-start">
                                                <span
                                                    class="badge bg-secondary bg-opacity-25 text-dark border border-secondary border-opacity-10 fw-normal text-capitalize"
                                                    style="font-size:0.9rem;">
                                                    <span
                                                        v-html="highlight(item.product?.name, filters.keyword)"></span>
                                                </span>
                                            </td>

                                            <td class="text-end pe-4">
                                                <div class="text-dark fw-bold">{{ formatCurrency(item.price_original) }}
                                                </div>
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="text-danger text-decoration-line-through">
                                                    {{ formatCurrency(item.price_discount) }}
                                                </div>
                                            </td>

                                            <td class="text-end fw-bold text-primary pe-4" style="font-size: 1rem;">
                                                {{ formatCurrency(item.price_final) }}
                                            </td>

                                            <td class="text-end fw-bold text-success pe-4">
                                                {{ formatCurrency(item.total_paid) }}
                                            </td>

                                            <td class="text-end pe-4">
                                                <span class="fw-bold"
                                                    :class="(item.price_final - item.total_paid) > 0 ? 'text-danger' : 'text-muted'">
                                                    {{ formatCurrency(item.price_final - item.total_paid) }}
                                                </span>
                                            </td>

                                            <td class="text-center">
                                                <span class="badge rounded-pill px-3 py-2 fw-bold"
                                                    :class="item.status === 'repayment' ? 'bg-success bg-opacity-10 text-success' : 'bg-warning text-warning-emphasis'">
                                                    <i
                                                        :class="['me-1', item.status === 'repayment' ? 'fas fa-check-circle' : 'fas fa-clock']"></i>
                                                    {{ item.status === 'repayment' ? 'Lunas' : 'Belum Lunas' }}
                                                </span>
                                            </td>

                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-1">

                                                    <button :disabled="item.status !== 'payment'"
                                                        @click.prevent="repayment(item.transaction_id)"
                                                        class="btn d-flex align-items-center gap-1 shadow-sm px-3"
                                                        :class="[item.status === 'payment' ? 'btn-success text-white' : 'btn-light text-muted border']"
                                                        title="Pelunasan">
                                                        <i class="fas fa-cash-register me-1"></i>
                                                        <span class="d-none d-xl-inline">Bayar</span>
                                                    </button>

                                                    <div class="dropdown dropstart">
                                                        <button class="btn btn-light border shadow-sm text-secondary"
                                                            type="button" data-bs-toggle="dropdown"
                                                            aria-expanded="false">
                                                            <i class="fas fa-cog"></i>
                                                        </button>
                                                        <ul
                                                            class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                                                            <li v-if="perm.includes('transaction.edit')">
                                                                <button @click.prevent="edit(item.transaction_id)"
                                                                    class="dropdown-item py-2 d-flex align-items-center gap-2 fw-semibold">
                                                                    <i class="fas fa-edit text-info"></i> Ubah
                                                                    Transaksi
                                                                </button>
                                                            </li>
                                                            <li v-if="perm.includes('transaction.delete')">
                                                                <div class="dropdown-divider"></div>
                                                                <button
                                                                    @click.prevent="deleted('transaction.deleted', item)"
                                                                    class="dropdown-item py-2 d-flex align-items-center gap-2 text-danger fw-semibold">
                                                                    <i class="fas fa-trash-alt"></i> Hapus
                                                                </button>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="card-footer bg-white border-top py-3" v-if="transaction?.data.length">
                            <div class="d-flex flex-wrap justify-content-between align-items-center">
                                <div class="text-muted mb-2 mb-md-0">
                                    Menampilkan <strong>{{ props.transaction?.from ?? 0 }}</strong> -
                                    <strong>{{ props.transaction?.to ?? 0 }}</strong> dari
                                    <strong>{{ props.transaction?.total ?? 0 }}</strong> data
                                </div>
                                <pagination size="pagination-sm" :links="props.transaction?.links"
                                    routeName="transaction" :additionalQuery="{
                                        order_by: filters.order_by,
                                        limit: filters.limit,
                                        keyword: filters.keyword,
                                        status: filters.status,
                                        date_filter: filters.date_filter
                                    }" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </app-layout>
</template>
<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s ease, transform 0.5s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(-5px);
}

.fade-enter-to,
.fade-leave-from {
    opacity: 1;
    transform: translateY(0);
}

.vh-50 {
    height: 50vh;
}

.blur-area {
    transition: all 0.3s ease;
}

.blur-area.is-blurred {
    filter: blur(3px);
    pointer-events: none;
    user-select: none;
    opacity: 0.6;
}



.list-group {
    --bs-list-group-bg: none;
    --bs-list-group-border-width: none;
}
</style>
