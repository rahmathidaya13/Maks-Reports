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
    customers: Object,
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
    router.get(route("customers"), filters, {
        preserveScroll: true,
        replace: true,
        preserveState: true,
        only: ["customers", "filters"],
        onFinish: () => isLoading.value = false
    });
}, 1000);


const header = [
    { label: "No", key: "__index" },
    { label: "NIK/SIM", key: "national_id_number" },
    { label: "Nama Pelanggan", key: "customer_name" },
    { label: "No Handphone", key: "number_phone_customer" },
    { label: "Kota", key: "city" },
    { label: "Provinsi", key: "province" },
    { label: "Alamat", key: "address" },
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
    router.get(route("customers.create"), {}, {
        onFinish: () => {
            loaderActive.value?.hide()
        }
    });
}

const edit = (id) => {
    loaderActive.value?.show("Sedang memuat data...");
    router.get(route("customers.edit", id), {}, {
        onFinish: () => loaderActive.value?.hide()
    });
}

const deleted = (nameRoute, data) => {
    swalConfirmDelete({
        title: 'Hapus',
        text: `Kamu ingin menghapus Data Pelanggan ${formatText(data.customer_name)} ?`,
        confirmText: 'Ya, Hapus!',
        onConfirm: () => {
            loaderActive.value?.show("Sedang memuat data...");
            router.delete(route(nameRoute, data.customer_id), {
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
    const rows = props.customers?.data ?? [];
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
            router.post(route('customers.destroy_all'), { all_id: selectedRow.value }, {
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
        selectedRow.value = props.customers?.data.map(r => r.customer_id);
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



</script>
<template>

    <Head title="Halaman Daftar Pelanggan" />
    <app-layout>
        <template #content>
            <loader-page ref="loaderActive" />
            <bread-crumbs :home="false" icon="fas fa-user-tag" title="Daftar Pelanggan"
                :items="[{ text: 'Daftar Pelanggan' }]" />
            <callout type="success" :duration="10" :message="message" />
            <div class="row">
                <div class="col-xl-12 col-12 mb-3">
                    <div class="card overflow-hidden pb-2">
                        <div class="card-header p-2 text-bg-grey">
                            <h5 class="card-title fw-bold mb-0 text-uppercase px-2">
                                <i class="fas fa-filter"></i> Filter
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-2 row-cols-1 justify-content-end">
                                <div class="col-xl-8 col-md-12">
                                    <input-label for="keyword" value="Pencarian" class="fw-semibold" />
                                    <div class="input-group mb-xl-0">
                                        <text-input input-class="border-dark border-1 border input-height-1"
                                            :is-valid="false" autofocus v-model="filters.keyword" name="keyword"
                                            placeholder="Masukan pencarian berdasarkan Nama, Nik/Sim, No Handphone....." />
                                    </div>
                                </div>
                                <div class="col-xl-2 col-md-6 col-6">
                                    <input-label for="limit" value="Batas" class="fw-semibold" />
                                    <div class="input-group">
                                        <select-input select-class="border-dark border-1 border input-height-1"
                                            :is-valid="false" v-model="filters.limit" name="limit" :options="[
                                                { value: null, label: 'Pilih Batas Data' },
                                                { value: 10, label: '10' },
                                                { value: 20, label: '20' },
                                                { value: 30, label: '30' },
                                                { value: 50, label: '50' },
                                                { value: 100, label: '100' },
                                            ]" />
                                    </div>
                                </div>
                                <div class="col-xl-2 col-md-6 col-6">
                                    <input-label for="order_by" value="Urutkan" class="fw-semibold" />
                                    <div class="input-group">
                                        <select-input select-class="border-dark border-1 border input-height-1"
                                            :is-valid="false" v-model="filters.order_by" name="order_by" :options="[
                                                { value: null, label: 'Pilih Urutan' },
                                                { value: 'desc', label: 'Terbaru' },
                                                { value: 'asc', label: 'Terlama' },
                                            ]" />
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-12 col-sm-12 col-md-12 col-lg-12">
                    <div class="d-flex justify-content-start gap-1 mb-2">
                        <button v-if="perm.includes('customers.delete')" :disabled="!isVisible" @click="deleteSelected" type="button"
                            class="btn btn-secondary position-relative bg-gradient"
                            :class="{ 'btn-danger': selectedRow.length > 0 }">
                            <i class="fas fa-trash"></i> Hapus
                            <span v-if="selectedRow.length > 0"
                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">
                                {{ selectedRow.length }}
                            </span>
                        </button>
                        <span v-if="perm.includes('customers.delete')" class="border border-1 border-secondary-subtle"></span>
                        <button v-if="perm.includes('customers.create')" type="button" @click.prevent="create" class="btn btn-success bg-gradient">
                            <i class="fas fa-plus"></i> Buat Baru
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
                                            <th v-if="perm.includes('customers.delete')">
                                                <div class="form-check d-flex justify-content-center">
                                                    <input :disabled="!customers?.data.length" type="checkbox"
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
                                        <tr v-if="!customers?.data.length">
                                            <td :colspan="header.length + 1" class="text-center py-5 text-muted">
                                                Tidak ada data tersedia.
                                            </td>
                                        </tr>

                                        <tr class="align-middle" v-for="(item, index) in customers?.data" :key="index"
                                            :class="{ 'table-info': isSelected(item.customer_id) }">

                                            <td class="text-center" v-if="perm.includes('customers.delete')">
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
                                                    (customers?.current_page - 1) * customers?.per_page
                                                }}
                                            </td>
                                            <td class="text-center text-capitalize">
                                                <div
                                                    v-html="highlight(item.national_id_number, filters.keyword) ?? '-'">
                                                </div>
                                            </td>

                                            <td class="text-center">
                                                <div v-html="highlight(item.customer_name, filters.keyword)"></div>
                                            </td>
                                            <td class="text-center">
                                                <div v-html="highlight(item.number_phone_customer, filters.keyword)">
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                {{ item.city }}
                                            </td>
                                            <td class="text-center">
                                                {{ item.province }}
                                            </td>
                                            <td class="text-start">
                                                {{ item.address }}
                                            </td>

                                            <td class="text-center">
                                                <div class="dropdown dropstart">
                                                    <button class="btn btn-secondary bg-gradient" type="button"
                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="fas fa-cog"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li v-if="perm.includes('customers.edit')">
                                                            <button @click.prevent="edit(item.customer_id)"
                                                                class="dropdown-item fw-semibold d-flex justify-content-between align-items-center">
                                                                Ubah <i class="bi bi-pencil-square text-info fs-5"></i>
                                                            </button>
                                                        </li>
                                                        <li v-if="perm.includes('customers.delete')">
                                                            <button @click.prevent="deleted('customers.deleted', item)"
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
                            <div
                                class="d-flex flex-wrap justify-content-lg-between align-items-center flex-column flex-lg-row px-3">
                                <div class="mb-2 order-1 order-xl-0">
                                    Menampilkan <strong>{{ props.customers?.from ?? 0 }}</strong> -
                                    <strong>{{ props.customers?.to ?? 0 }}</strong> dari total
                                    <strong>{{ props.customers?.total ?? 0 }}</strong> data
                                </div>
                                <pagination size="pagination-sm" :links="props.customers?.links" routeName="customers"
                                    :additionalQuery="{
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
