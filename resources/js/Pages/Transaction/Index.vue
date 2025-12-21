<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { debounce } from "lodash";
import { swalAlert, swalConfirmDelete } from "../../helpers/swalHelpers";
import { highlight } from "@/helpers/highlight";
import { formatText } from "@/helpers/formatText";
import moment from "moment";
moment.locale('id');

const page = usePage();
const message = computed(() => page.props.flash.message || "");
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
            router.delete(route(nameRoute, data), {
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

const getPaymentDate = (payments, type) => {
    if (!payments) return '-'
    const p = payments.find(p => p.payment_type === type);
    return p ? moment(p.payment_date).format('DD/MM/YYYY') : '-'
}
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
        class: !hasActiveFilter.value ? 'btn-secondary' : 'btn-outline-danger',
        icon: 'fas fa-undo',
        disabled: !hasActiveFilter.value,
        handler: () => handleReset()
    },
    {
        key: 'apply',
        label: 'Terapkan',
        type: 'button',
        name: 'apply',
        class: !hasActiveFilter.value ? 'btn-secondary' : 'btn-primary',
        icon: 'fas fa-filter',
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
            <callout type="success" :duration="10" :message="message" />
            <div class="row">
                <div class="col-xl-12 col-12 mb-3">
                    <filter-dynamic title="Filter" v-model="filters" :fields="fileterFields" />
                </div>

                <div class="col-xl-12 col-sm-12 col-md-12 col-lg-12">
                    <div class="d-flex justify-content-start gap-1 mb-2">
                        <button v-if="perm.includes('transaction.delete')" :disabled="!isVisible"
                            @click="deleteSelected" type="button" class="btn position-relative bg-gradient"
                            :class="[selectedRow.length > 0 ? 'btn-danger' : 'btn-secondary']">
                            <i class="fas fa-trash"></i> Hapus
                            <span v-if="selectedRow.length > 0"
                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">
                                {{ selectedRow.length }}
                            </span>
                        </button>
                        <span v-if="perm.includes('transaction.delete')"
                            class="border border-1 border-secondary-subtle"></span>
                        <button v-if="perm.includes('transaction.create')" type="button" @click.prevent="create"
                            class="btn btn-success bg-gradient">
                            <i class="fas fa-plus"></i> Buat Transaksi
                        </button>
                    </div>
                    <div class="card mb-4 overflow-hidden rounded-3">
                        <div v-if="isLoading">
                            <loader-horizontal message="Sedang memproses data" />
                        </div>
                        <div class="card-body p-0" :class="['blur-area', isLoading ? 'is-blurred' : '']">
                            <div :class="['table-responsive', transaction?.data.length <= 5 ? 'vh-100' : '']">
                                <table class=" table table-hover table-bordered table-striped text-nowrap">
                                    <thead class="table-dark">
                                        <tr>
                                            <th v-if="perm.includes('transaction.delete')">
                                                <div class="form-check d-flex justify-content-center">
                                                    <input :disabled="!transaction?.data.length" type="checkbox"
                                                        class="form-check-input" :checked="isAllSelected"
                                                        @change="toggleAll($event)" />
                                                </div>
                                            </th>
                                            <th v-for="col in header" :key="col.key" class="text-center">
                                                {{ col.label }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="!transaction?.data.length">
                                            <td :colspan="header.length + 1" class="text-center py-5 text-muted">
                                                Tidak ada data tersedia.
                                            </td>
                                        </tr>

                                        <tr class="align-middle" v-for="(item, index) in transaction?.data" :key="index"
                                            :class="{ 'table-info': isSelected(item.transaction_id) }">

                                            <td class="text-center" v-if="perm.includes('transaction.delete')">
                                                <div class="form-check d-flex justify-content-center">
                                                    <input type="checkbox" class="form-check-input"
                                                        :name="item.transaction_id" :id="item.transaction_id"
                                                        :value="item.transaction_id" v-model="selectedRow" />
                                                </div>
                                            </td>

                                            <td class="text-center">
                                                {{
                                                    index +
                                                    1 +
                                                    (transaction?.current_page - 1) * transaction?.per_page
                                                }}
                                            </td>
                                            <td class="text-center text-capitalize">
                                                {{ moment(item.transaction_date).format('DD/MM/YYYY') }}
                                            </td>
                                            <td class="text-start">
                                                <ul class="list-group lh-1 rounded-0">
                                                    <li
                                                        class="list-group-item d-flex justify-content-between align-items-center fw-semibold">
                                                        <span
                                                            class="badge text-bg-warning rounded-0 me-5">DP(50%)</span>
                                                        {{ getPaymentDate(item.payments, 'payment') }}
                                                    </li>
                                                    <li
                                                        class="list-group-item d-flex justify-content-between align-items-center fw-semibold">
                                                        <span class="badge text-bg-success rounded-0 me-5">Lunas</span>
                                                        {{ getPaymentDate(item.payments, 'repayment') }}
                                                    </li>
                                                </ul>
                                            </td>
                                            <td class="text-center text-capitalize">
                                                <div
                                                    v-html="highlight(item.customer.customer_name, filters.keyword) ?? '-'">
                                                </div>
                                            </td>

                                            <td class="text-center">
                                                <div v-html="highlight(item.product.name, filters.keyword)"></div>
                                            </td>
                                            <td class="text-center fw-bold">
                                                {{ formatCurrency(item.price_original) }}
                                            </td>
                                            <td class="text-center fw-bold">
                                                {{ formatCurrency(item.price_discount) }}
                                            </td>
                                            <td class="text-center fw-bold">
                                                {{ formatCurrency(item.price_final) }}
                                            </td>
                                            <td class="text-center text-success fw-bold">
                                                {{ formatCurrency(item.total_paid) }}
                                            </td>
                                            <td class="fw-bold text-danger text-center">
                                                {{ formatCurrency(item.price_final - item.total_paid) }}
                                            </td>
                                            <td class="text-center">
                                                <span class="badge rounded-0 p-2 "
                                                    :class="item.status === 'repayment' ? 'bg-success' : 'bg-warning'">
                                                    <i
                                                        :class="['me-1', item.status === 'repayment' ? 'fas fa-check-circle' : 'fas fa-circle']"></i>
                                                    {{ item.status === 'repayment' ? 'Lunas' : 'Belum Lunas' }}
                                                </span>
                                            </td>
                                            <td class="text-start">
                                                <div class="d-inline-flex gap-1">
                                                    <button :disabled="item.status !== 'payment'"
                                                        @click.prevent="repayment(item.transaction_id)"
                                                        class="btn bg-gradient"
                                                        :class="[item.status === 'payment' ? ' btn-outline-success' : ' btn-outline-secondary']">
                                                        <i class="fas fa-cash-register"></i>
                                                        Pelunasan
                                                    </button>


                                                    <div class="dropdown dropstart">
                                                        <button class="btn btn-info text-white bg-gradient"
                                                            type="button" data-bs-toggle="dropdown"
                                                            aria-expanded="false">
                                                            <i class="fas fa-cog"></i>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li v-if="perm.includes('transaction.edit')">
                                                                <button @click.prevent="edit(item.transaction_id)"
                                                                    class="dropdown-item fw-semibold d-flex justify-content-between align-items-center">
                                                                    Ubah <i
                                                                        class="bi bi-pencil-square text-info fs-5"></i>
                                                                </button>
                                                            </li>
                                                            <li v-if="perm.includes('transaction.delete')">
                                                                <button
                                                                    @click.prevent="deleted('transaction.deleted', item)"
                                                                    class="dropdown-item fw-semibold d-flex justify-content-between align-items-center">
                                                                    Hapus <i
                                                                        class="bi bi-trash-fill text-danger fs-5"></i>
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
                        <div class="card-footer pb-0" v-if="transaction?.data.length">
                            <div
                                class="d-flex flex-wrap justify-content-lg-between align-items-center flex-column flex-lg-row">
                                <div class="mb-2 order-1 order-xl-0">
                                    Menampilkan <strong>{{ props.transaction?.from ?? 0 }}</strong> -
                                    <strong>{{ props.transaction?.to ?? 0 }}</strong> dari total
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
.blur-area {
    transition: all 0.3s ease;
}

.blur-area.is-blurred {
    filter: blur(3px);
    pointer-events: none;
    user-select: none;
    opacity: 0.6;
}
</style>
