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
    ],
    () => {
        liveSearch();
    }
);

const handleApply = () => {
    liveSearch();
}
const handleReset = () => {
    filters.keyword = ''
    filters.limit = null
    filters.order_by = null

    // Langsung cari data bersih
    liveSearch()
}



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
// start MULTIPLE DELETE
watch(selectedRow, (val) => {
    if (val.length > 0) {
        isVisible.value = true
    } else {
        isVisible.value = false
    }
})
// END MULTIPLE DELETE

// cek apakah ada filter yang terpilih
const hasActiveFilter = computed(() => {
    return (
        filters.keyword !== '' ||
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
            placeholder: 'Masukan nama,nik dan no handphone...',
            inputClass: 'border-0 border input-height-1',
            isValid: false,
            autofocus: true
        }
    },
    {
        key: 'limit',
        label: 'Batas',
        type: 'select',
        col: 'col-xl-2 col-md-6 col-6',
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
        col: 'col-xl-2 col-md-6 col-6',
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

    <Head title="Halaman Daftar Pelanggan" />
    <app-layout>
        <template #content>
            <loader-page ref="loaderActive" />
            <bread-crumbs :home="false" icon="fas fa-user-tag" title="Daftar Pelanggan"
                :items="[{ text: 'Daftar Pelanggan' }]" />
            <callout type="success" :duration="10" :message="message" />
            <div class="row pb-3">
                <div class="col-xl-12 col-12 mb-3">
                    <filter-dynamic title="Filter" v-model="filters" :fields="fileterFields" />
                </div>

                <div class="col-xl-12 col-12">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">

                        <div
                            class="card-header bg-white py-3 px-4 border-bottom-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h5 class="fw-bold mb-0 text-dark">Data Pelanggan</h5>
                                <p class="text-muted small mb-0">Kelola data pelanggan dan informasi kontak.</p>
                            </div>

                            <div class="d-flex gap-2">
                                <transition name="fade">
                                    <button v-if="perm.includes('customers.delete') && selectedRow.length > 0"
                                        @click="deleteSelected" type="button"
                                        class="btn btn-danger rounded-3 shadow-sm px-3 d-flex align-items-center animate__animated animate__fadeIn">
                                        <i class="fas fa-trash-alt me-2"></i>
                                        Hapus ({{ selectedRow.length }})
                                    </button>
                                </transition>

                                <button v-if="perm.includes('customers.create')" type="button" @click.prevent="create"
                                    class="btn btn-primary rounded-3 shadow-sm px-3 fw-bold">
                                    <i class="fas fa-plus me-1"></i> Pelanggan Baru
                                </button>
                            </div>
                        </div>

                        <div v-if="isLoading"
                            class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-flex justify-content-center align-items-center z-3">
                            <loader-horizontal message="Memuat..." />
                        </div>

                        <div class="card-body p-0" :class="['blur-area', isLoading ? 'is-blurred' : '']">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 text-nowrap">
                                    <thead class="bg-light text-uppercase text-secondary fw-bold">
                                        <tr>
                                            <th class="text-center" v-if="perm.includes('customers.delete')">
                                                <div class="form-check d-flex justify-content-center">
                                                    <input :disabled="!customers?.data.length" type="checkbox"
                                                        class="form-check-input shadow-none cursor-pointer"
                                                        :checked="isAllSelected" @change="toggleAll($event)" />
                                                </div>
                                            </th>

                                            <th class=" text-center" width="50">No</th>

                                            <th class="text-start ps-4">Informasi Pelanggan</th>

                                            <th class="text-start">Kontak</th>

                                            <th class="text-start">Domisili / Lokasi</th>

                                            <th class="text-start" style="width: 25%;">Alamat Lengkap</th>

                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-top-0">

                                        <tr v-if="!customers?.data.length">
                                            <td :colspan="perm.includes('customers.delete') ? 7 : 6"
                                                class="text-center py-5">
                                                <div class="py-4">
                                                    <i class="fas fa-users-slash fa-3x text-muted opacity-25 mb-3"></i>
                                                    <p class="text-muted fw-semibold">Belum ada data pelanggan.</p>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr v-for="(item, index) in customers?.data" :key="index" class="align-middle"
                                            :class="{ 'bg-primary bg-opacity-10': isSelected(item.customer_id) }">

                                            <td class="text-center" v-if="perm.includes('customers.delete')">
                                                <div class="form-check d-flex justify-content-center">
                                                    <input type="checkbox"
                                                        class="form-check-input shadow-none cursor-pointer"
                                                        :value="item.customer_id" v-model="selectedRow" />
                                                </div>
                                            </td>

                                            <td class="text-center text-muted fw-bold small">
                                                {{ index + 1 + (customers?.current_page - 1) * customers?.per_page }}
                                            </td>

                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold shadow-sm"
                                                        style="width: 40px; height: 40px; min-width: 40px;">
                                                        {{ item.customer_name.charAt(0).toUpperCase() }}
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-dark text-capitalize"
                                                            v-html="highlight(item.customer_name, filters.keyword)">
                                                        </div>
                                                        <div class="text-muted d-flex align-items-center gap-1">
                                                            <i class="fas fa-id-card text-secondary"></i>
                                                            <span
                                                                v-html="highlight(item.national_id_number, filters.keyword) ?? '-'"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span
                                                        class="bg-success bg-opacity-10 text-success rounded px-2 py-1">
                                                        <i class="fas fa-phone-alt fa-xs"></i>
                                                    </span>
                                                    <span class="fw-semibold text-dark"
                                                        v-html="highlight(item.number_phone_customer, filters.keyword)"></span>
                                                </div>
                                            </td>

                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-semibold text-dark">{{ item.city }}</span>
                                                    <small class="text-muted">{{ item.province }}</small>
                                                </div>
                                            </td>

                                            <td>
                                                <div class="text-muted text-wrap lh-sm">
                                                    <i class="fas fa-map-marker-alt text-danger me-1 opacity-50"></i>
                                                    {{ item.address }}
                                                </div>
                                            </td>

                                            <td class="text-center">
                                                <div class="dropdown dropstart">
                                                    <button class="btn btn-light border shadow-sm text-secondary"
                                                        type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="fas fa-cog"></i>
                                                    </button>
                                                    <ul
                                                        class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                                                        <li v-if="perm.includes('customers.edit')">
                                                            <button @click.prevent="edit(item.customer_id)"
                                                                class="dropdown-item py-2 d-flex align-items-center gap-2 fw-semibold">
                                                                <i class="fas fa-pencil-alt text-info"></i> Ubah Data
                                                            </button>
                                                        </li>
                                                        <li v-if="perm.includes('customers.delete')">
                                                            <div class="dropdown-divider"></div>
                                                            <button @click.prevent="deleted('customers.deleted', item)"
                                                                class="dropdown-item py-2 d-flex align-items-center gap-2 text-danger fw-semibold">
                                                                <i class="fas fa-trash-alt"></i> Hapus Pelanggan
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

                        <div class="card-footer bg-white border-top py-3" v-if="customers?.data.length">
                            <div class="d-flex flex-wrap justify-content-between align-items-center">
                                <div class="text-muted small mb-2 mb-md-0">
                                    Menampilkan <strong>{{ props.customers?.from ?? 0 }}</strong> -
                                    <strong>{{ props.customers?.to ?? 0 }}</strong> dari
                                    <strong>{{ props.customers?.total ?? 0 }}</strong> data
                                </div>
                                <pagination size="pagination-sm" :links="props.customers?.links" routeName="customers"
                                    :additionalQuery="{
                                        order_by: filters.order_by,
                                        limit: filters.limit,
                                        keyword: filters.keyword
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
