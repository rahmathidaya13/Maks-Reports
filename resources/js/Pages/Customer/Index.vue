<script setup>
import { computed, reactive, ref, watch } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { debounce } from "lodash";
import { highlight } from "@/helpers/highlight";
import { formatText } from "@/helpers/formatText";
import { hasRole, hasPermission } from "@/composables/useAuth";
import moment from "moment";
moment.locale('id');

import { useConfirm } from "@/helpers/useConfirm.js"
const confirm = useConfirm(); // Memanggil fungsi confirm untuk alert delete

const props = defineProps({
    customers: Object,
    filters: Object,
});

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
}, 500);


const header = [
    {
        label: "No",
        key: "__index",
        attrs: {
            class: "text-center",
            style: "width:50px"
        }
    },
    {
        label: "ID Pelanggan",
        key: "customer_id",
        attrs: {
            class: "text-center"
        },
    },
    {
        label: "Informasi Pelanggan",
        key: "customer_name",
        attrs: {
            class: "text-start ps-4"
        },
    },
    {
        label: "Jenis Usaha",
        key: "type_bussiness",
        attrs: {
            class: "text-start"
        },
    },
    {
        label: "Kontak",
        key: "number_phone_customer",
        attrs: {
            class: "text-center"
        },
    },
    {
        label: "Domisili/Lokasi",
        key: "city",
        attrs: {
            class: "text-start"
        },
    },
    {
        label: "Alamat",
        key: "address",
        attrs: {
            class: "text-center"
        },
    },

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

const deleted = async (nameRoute, data) => {
    const setConfirm = await confirm.ask({
        title: 'Hapus',
        message: `Kamu ingin menghapus Data Pelanggan ${formatText(data.customer_name)} ?`,
        confirmText: 'Ya, Hapus',
        variant: 'danger' // Memberikan warna merah pada tombol konfirmasi
    });

    if (setConfirm) {
        loaderActive.value?.show("Sedang menghapus data...");
        router.delete(route(nameRoute, data.customer_id), {
            onFinish: () => loaderActive.value?.hide(),
            preserveScroll: false,
            replace: true
        });
    }
}
// end CRUD OPERATION

// MULTIPLE DELETE
const selectedRow = ref([]);
const deleteSelected = async () => {
    // 1. Kondisi Tidak Ada Data (Berfungsi sebagai Alert)
    if (!selectedRow.value.length) {
        return await confirm.ask({
            title: 'Perhatian',
            message: 'Silakan pilih minimal satu data untuk dihapus.',
            cancelText: 'Mengerti', // Ubah teks tombol tutup
            showButtonConfirm: false,
            variant: 'warning' // Gunakan warna kuning/orange untuk warning
        });
    }

    // 2. Kondisi Konfirmasi Hapus
    const setConfirm = await confirm.ask({
        title: 'Konfirmasi Hapus',
        message: `Apakah Anda yakin ingin menghapus ${selectedRow.value.length} data terpilih?`,
        confirmText: 'Ya, Hapus',
        cancelText: 'Batal',
        variant: 'danger'
    });

    // 3. Eksekusi
    if (setConfirm) {
        loaderActive.value?.show("Sedang menghapus data...");
        router.post(route('customers.destroy_all'), {
            all_id: selectedRow.value
        }, {
            onFinish: () => {
                loaderActive.value?.hide();
                selectedRow.value = []; // Bersihkan pilihan setelah sukses
            },
            preserveScroll: true,
            preserveState: false,
        });
    }
}


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
        type: 'search',
        icon: 'fas fa-search',
        autofocus: true,
        props: {
            placeholder: 'Masukan nama,nik dan no handphone...',
            inputClass: 'border-start-0 ps-2 shadow-none',
            isValid: false,
        }
    },
    {
        key: 'limit',
        label: 'Batas',
        type: 'select',
        col: 'col-xl-2 col-md-6 col-6',
        icon: 'fas fa-list-ul',
        props: {
            selectClass: 'border-start-0 ps-2 shadow-none',
            isValid: false,
        },
        options: [
            { value: null, label: 'Pilih Batas Data' },
            { value: 10, label: '10 Baris' },
            { value: 20, label: '20 Baris' },
            { value: 30, label: '30 Baris' },
            { value: 50, label: '50 Baris' },
            { value: 100, label: '100 Baris' },
        ]
    },
    {
        key: 'order_by',
        label: 'Urutan',
        type: 'select',
        col: 'col-xl-2 col-md-6 col-6',
        icon: 'fas fa-sort',
        props: {
            selectClass: 'border-start-0 ps-2 shadow-none',
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
        icon: 'fas fa-sync-alt',
        disabled: !hasActiveFilter.value,
        handler: () => handleReset()
    },
    {
        key: 'apply',
        label: 'Terapkan',
        type: 'button',
        name: 'apply',
        class: !hasActiveFilter.value ? 'btn-secondary' : 'btn-primary',
        icon: 'fas fa-check',
        disabled: !hasActiveFilter.value,
        handler: () => handleApply()
    },

]);

function subString(strValue, length) {
    const clean = strValue.replace(/-/g, "")
    return clean.length > length
        ? clean.substring(0, length)
        : clean
}

const reset = () => {
    isLoading.value = true
    router.get(route('customers.reset'), {}, {
        preserveScroll: true,
        onSuccess: () => {
            router.reload({ only: ['customers', 'filters'] });
        },
        onFinish: () => {
            // Selesai apapun hasilnya → loader hilang
            isLoading.value = false
        }
    });
}

const toolbarActions = computed(() => [
    {
        label: `Hapus (${selectedRow.value.length})`,
        icon: 'fas fa-trash-alt',
        iconColor: 'text-danger',
        labelColor: 'text-danger',
        disabled: !selectedRow.value.length > 0,
        show: hasPermission('customers.delete'),
        click: deleteSelected
    },
    {
        label: 'Pelanggan Baru',
        icon: 'fas fa-plus-circle',
        isPrimary: true, // Prioritas Utama
        show: hasPermission('customers.create'),
        click: create
    },
    {
        label: 'Segarkan',
        icon: 'fas fa-redo-alt',
        iconColor: 'text-primary',
        loading: isLoading.value,
        click: reset
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
            <callout />
            <div class="row pb-3">
                <div class="col-xl-12 col-12 mb-3">
                    <base-filters title="Filter" v-model="filters" :fields="fileterFields" />
                </div>

                <div class="col-xl-12 col-12">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">

                        <div
                            class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">

                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3 me-3">
                                    <i class="fas fa-user-tag fs-5"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0 text-dark">Data Pelanggan</h5>
                                    <p class="text-muted small mb-0">
                                        Kelola data pelanggan dan informasi kontak.
                                    </p>
                                </div>
                            </div>
                            <action-toolbar :actions="toolbarActions" />
                        </div>

                        <div class="card-body p-0 position-relative">
                            <base-table :markAll="hasPermission('customers.delete')" :loader="isLoading"
                                loaderText="Sedang memuat data..." :headers="header" :items="customers?.data"
                                row-key="customer_id" @update:selected="(val) => selectedRow = val">

                                <template #empty>
                                    <i class="fas fa-users-slash fa-3x text-muted opacity-25 mb-3"></i>
                                    <p class="text-muted fw-semibold">Belum ada data pelanggan.</p>
                                </template>

                                <template #row="{ item, index }">
                                    <td class="text-center text-muted fw-bold small">
                                        {{ index + 1 + (customers?.current_page - 1) * customers?.per_page }}
                                    </td>

                                    <td class="text-center">
                                        <span
                                            class="badge text-bg-info bg-opacity-10 border border-secondary border-opacity-10 fw-normal text-capitalize"
                                            style="font-size: 0.9rem">
                                            <span
                                                v-html="highlight(subString(item.customer_id, 8), filters.keyword)"></span>
                                        </span>
                                    </td>

                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold shadow-sm"
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

                                    <td class="text-center">
                                        {{ item.type_bussiness ?? '-' }}
                                    </td>

                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="bg-success bg-opacity-10 text-success rounded px-2 py-1">
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
                                        <div class="text-muted lh-sm">
                                            <i class="fas fa-map-marker-alt text-danger me-1 opacity-50"></i>
                                            {{ item.address }}
                                        </div>
                                    </td>

                                    <td class="text-center">
                                        <dropdown-action :item="item" :actions="[
                                            {
                                                label: 'Ubah Data',
                                                icon: 'bi bi-pencil-square fs-6',
                                                color_icon: 'success',
                                                action: 'edit',
                                                permission: 'customers.edit'
                                            },
                                            {
                                                type: 'divider',
                                            },
                                            {
                                                label: 'Hapus',
                                                icon: 'bi bi-trash fs-6',
                                                color: 'danger',
                                                action: 'delete',
                                                permission: 'customers.delete'

                                            }
                                        ]" @edit="edit(item.customer_id)"
                                            @delete="deleted('customers.deleted', item)" />
                                    </td>
                                </template>
                            </base-table>
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
/* Transition for Bulk Delete Button */
.fade-enter-active,
.fade-leave-active {
    transition: all 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: scale(0.9);
}
</style>
