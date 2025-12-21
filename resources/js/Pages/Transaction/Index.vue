<script setup>
import { computed, reactive, ref, watch } from "vue";
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
}, 1000);


const header = [
    { label: "No", key: "__index" },
    { label: "Nama Pelanggan", key: "customer_id" },
    { label: "Nama Barang/Produk", key: "product_id" },
    { label: "Harga Asli", key: "price_original" },
    { label: "Harga Diskon", key: "price_discount" },
    { label: "Harga Akhir", key: "price_final" },
    { label: "Status", key: "status" },
    { label: "Aksi", key: "-" },
];
watch(
    () => [
        filters.keyword,
        filters.limit,
        filters.order_by,
    ],
    () => {
        liveSearch();
    }
);


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
        selectedRow.value = props.transaction?.data.map(r => r.customer_id);
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

const repayment = (id)=>{
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
                    <!-- filter in here -->
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
                            class="btn btn-primary bg-gradient">
                            <i class="fas fa-plus"></i> Buat Transaksi
                        </button>
                    </div>
                    <div class="card mb-4 overflow-hidden rounded-3">
                        <div v-if="isLoading">
                            <loader-horizontal message="Sedang memproses data" />
                        </div>
                        <div class="card-body p-0" :class="['blur-area', isLoading ? 'is-blurred' : '']">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered table-striped text-nowrap">
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
                                            :class="{ 'table-info': isSelected(item.customer_id) }">

                                            <td class="text-center" v-if="perm.includes('transaction.delete')">
                                                <div class="form-check d-flex justify-content-center">
                                                    <input type="checkbox" class="form-check-input"
                                                        :name="item.customer_id" :id="item.customer_id"
                                                        :value="item.customer_id" v-model="selectedRow" />
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
                                                <div
                                                    v-html="highlight(item.customer.customer_name, filters.keyword) ?? '-'">
                                                </div>
                                            </td>

                                            <td class="text-center">
                                                <div v-html="highlight(item.product.name, filters.keyword)"></div>
                                            </td>
                                            <td class="text-center">
                                              {{ formatCurrency(item.price_original) }}
                                            </td>
                                            <td class="text-center">
                                                {{ formatCurrency(item.price_discount) }}
                                            </td>
                                            <td class="text-center">
                                                {{ formatCurrency(item.price_final) }}
                                            </td>
                                            <td class="text-center">
                                                {{ item.status === 'repayment' ? 'Lunas' : 'Belum Lunas' }}
                                            </td>

                                            <td class="text-center">
                                                <div class="dropdown dropstart">
                                                    <button class="btn btn-secondary bg-gradient" type="button"
                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="fas fa-cog"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li v-if="item.status === 'payment'">
                                                            <button @click.prevent="repayment(item.transaction_id)"
                                                                class="dropdown-item fw-semibold d-flex justify-content-between align-items-center">
                                                                Pelunasan <i class="bi bi-currency-dollar text-success fs-5"></i>
                                                            </button>
                                                        </li>
                                                        <li v-if="perm.includes('transaction.edit')">
                                                            <button @click.prevent="edit(item.transaction_id)"
                                                                class="dropdown-item fw-semibold d-flex justify-content-between align-items-center">
                                                                Ubah <i class="bi bi-pencil-square text-info fs-5"></i>
                                                            </button>
                                                        </li>
                                                        <li v-if="perm.includes('transaction.delete')">
                                                            <button
                                                                @click.prevent="deleted('transaction.deleted', item)"
                                                                class="dropdown-item fw-semibold d-flex justify-content-between align-items-center">
                                                                Hapus <i class="bi bi-trash-fill text-danger fs-5"></i>
                                                            </button>
                                                        </li>
                                                    </ul>
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
