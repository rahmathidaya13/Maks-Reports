<script setup>
import { computed, reactive, ref, watch } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { debounce } from "lodash";
import { highlight } from "@/helpers/highlight";
import { formatText } from "@/helpers/formatText";
import { hasRole, hasPermission } from "@/composables/useAuth";
import { useConfirm } from "@/helpers/useConfirm.js"
const confirm = useConfirm(); // Memanggil fungsi confirm untuk alert delete

import moment from "moment";
moment.locale('id');

const props = defineProps({
    branch: Object,
    filters: Object,
});

const filters = reactive({
    keyword: props.filters.keyword ?? '',
    limit: props.filters.limit ?? 10,
    order_by: props.filters.order_by ?? "desc",
    page: props.filters?.page ?? 1,
})

const isLoading = ref(false)
const liveSearch = debounce((e) => {
    isLoading.value = true
    router.get(route("branch"), filters, {
        preserveScroll: true,
        replace: true,
        preserveState: true,
        only: ["branch", "filters"],
        onFinish: () => isLoading.value = false
    });
}, 1000);


const header = [
    {
        label: "No",
        key: "__index",
        attrs: { class: "text-center align-middle", style: "width: 50px" }
    },
    {
        label: "Identitas Cabang", // Gabungan Kode + Nama + Status Official
        key: "name",
        attrs: { class: "text-start align-middle" }
    },
    {
        label: "Kontak & Lokasi", // Gabungan Telepon + Alamat
        key: "contact",
        attrs: { class: "text-start align-middle", style: "width: 300px" }
    },
    {
        label: "Status Operasional", // Status Aktif/Tidak
        key: "status",
        attrs: { class: "text-center align-middle" }
    },
    {
        label: "Pembuat", // Creator + Tanggal
        key: "created_by",
        attrs: { class: "text-center align-middle" }
    },
    {
        label: "",
        key: "actions",
        attrs: { class: "text-center align-middle" }
    },
];
watch(
    () => [
        filters.keyword,
        filters.limit,
        filters.order_by,],
    () => {
        liveSearch();
    }
);


// CRUD OPERATION
const loaderActive = ref(null)
const deleted = async (nameRoute, data) => {
    const setConfirm = await confirm.ask({
        title: 'Hapus',
        message: `Kamu ingin menghapus Cabang ${formatText(data.name)} ?`,
        confirmText: 'Ya, Hapus',
        variant: 'danger' // Memberikan warna merah pada tombol konfirmasi
    });

    if (setConfirm) {
        loaderActive.value?.show("Sedang menghapus data...");
        router.delete(route(nameRoute, data.branches_id), {
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
        router.post(route('branch.destroy_all'), {
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
// end MULTIPLE DELETE

// date convert
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
    const dateFormat = moment(dayValue).format('DD-MM-YYYY, HH:mm:ss');
    return dayConvert[dayName] + ", " + dateFormat ?? dayName;
}


const filterFields = computed(() => [
    {
        key: 'keyword',
        label: 'Pencarian',
        type: 'search',
        col: 'col-xl-8 col-12',
        autofocus: true,
        icon: 'fas fa-search',
        props: {
            placeholder: 'Masukan Pencarian...',
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
]);

const navigateTo = (routeName, params = {}, message = "Sedang membuka...") => {
    if (message) loaderActive.value?.show(message);
    router.get(route(routeName, params), {}, {
        onFinish: () => message && loaderActive.value?.hide(),
        preserveScroll: false,
        replace: true,
    });

}
const reset = () => {
    isLoading.value = true
    navigateTo("branch.reset", {}, false);
}
const toolbarActions = computed(() => [

    {
        label: `Hapus (${selectedRow.value.length})`,
        icon: 'fas fa-trash-alt',
        iconColor: 'text-danger',
        labelColor: 'text-danger',
        disabled: !selectedRow.value.length > 0,
        show: hasPermission('branches.delete'),
        click: () => deleteSelected()
    },

    {
        label: 'Cabang Baru',
        icon: 'fas fa-plus-circle',
        isPrimary: true, // Prioritas Utama
        show: hasPermission('branches.create'),
        click: () => navigateTo('branch.create', {}, "Sedang membuka form...")
    },
    {
        label: 'Segarkan',
        icon: 'fas fa-redo-alt',
        iconColor: 'text-primary',
        loading: isLoading.value,
        click: () => reset()
    }
]);
</script>
<template>

    <Head title="Halaman Cabang" />
    <app-layout>
        <template #content>
            <loader-page ref="loaderActive" />
            <bread-crumbs :home="false" icon="fas fa-building" title="Daftar Cabang"
                :items="[{ text: 'Daftar Cabang' }]" />
            <callout />

            <div class="row pb-3">
                <div class="col-xl-12 col-12">
                    <base-filters title="Filter" v-model="filters" :fields="filterFields" />
                </div>

                <div class="col-xl-12 col-12">
                    <div class="card card-modern border-0 shadow-sm rounded-4 overflow-hidden">
                        <div
                            class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">

                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3 me-3">
                                    <i class="fas fa-building fs-5"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0 text-dark">Data Cabang</h5>
                                    <p class="text-muted small mb-0">
                                        informasi Cabang terdaftar
                                    </p>
                                </div>
                            </div>

                            <action-toolbar :actions="toolbarActions" />

                        </div>


                        <div class="card-body p-0 position-relative">
                            <base-table :markAll="hasPermission('branches.delete')" :loader="isLoading"
                                loaderText="Sedang memuat data..." :headers="header" :items="branch?.data"
                                row-key="branches_id" @update:selected="(val) => selectedRow = val">

                                <template #empty>
                                    <div class="text-muted d-flex flex-column align-items-center">
                                        <i class="far fa-folder-open fs-1 mb-3 opacity-25"></i>
                                        <span>Tidak ada data ditemukan</span>
                                    </div>
                                </template>

                                <template #row="{ item, index }">

                                    <td class="text-center text-muted fw-medium align-middle">
                                        {{ index + 1 + (branch?.current_page - 1) * branch?.per_page }}
                                    </td>

                                    <td class="ps-3 align-middle">
                                        <div class="d-flex flex-column">
                                            <div class="d-flex align-items-center mb-1">
                                                <span class="fw-bold text-dark fs-6 text-capitalize me-2"
                                                    v-html="highlight(item.name, filters.keyword)"></span>

                                                <span v-if="item.status_official === 'official'"
                                                    class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10 rounded-pill d-flex align-items-center px-2 py-1"
                                                    style="font-size: 0.65rem;">
                                                    <i class="fas fa-certificate me-1"></i> PUSAT
                                                </span>
                                                <span v-else
                                                    class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-10 rounded-pill px-2 py-1"
                                                    style="font-size: 0.65rem;">
                                                    CABANG
                                                </span>
                                            </div>

                                            <div>
                                                <span
                                                    class="font-monospace text-muted small bg-light border rounded px-2"
                                                    style="font-size: 0.75rem;">
                                                    <i class="fas fa-hashtag me-1 opacity-50"></i> {{ item.branch_code }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="align-middle">
                                        <div class="d-flex flex-column gap-2 py-2">
                                            <div v-if="item.branch_phone && item.branch_phone.length > 0">
                                                <div class="d-flex flex-wrap gap-1">
                                                    <span v-for="phone in item.branch_phone"
                                                        :key="phone.branch_phone_id"
                                                        class="badge bg-white border text-dark fw-normal shadow-sm d-flex align-items-center" style="font-size: 0.8rem;">
                                                        <i class="fas fa-phone-alt text-muted me-2"
                                                            style="font-size: 0.6rem;"></i>
                                                        <span v-html="highlight(phone.phone, filters.keyword)"></span>
                                                    </span>
                                                </div>
                                            </div>
                                            <div v-else class="text-muted small fst-italic">
                                                - Tidak ada no. telepon -
                                            </div>

                                            <div class="d-flex align-items-start small text-secondary"
                                                style="line-height: 1.3;">
                                                <i class="fas fa-map-marker-alt text-danger mt-1 me-2 opacity-75"></i>
                                                <span v-html="item.address || '-'"></span>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="text-center align-middle">
                                        <div class="d-flex justify-content-center">
                                            <span
                                                class="badge rounded-pill px-3 py-2 fw-medium d-flex align-items-center gap-2"
                                                :class="{
                                                    'bg-success bg-opacity-10 text-success border border-success border-opacity-10': item.status === 'active',
                                                    'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-10': item.status === 'inactive'
                                                }">
                                                <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
                                                {{ item.status === 'active' ? 'Aktif' : 'Non-Aktif' }}
                                            </span>
                                        </div>
                                    </td>

                                    <td class="align-middle">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle me-2 bg-gradient-light border text-primary fw-bold small d-flex align-items-center justify-content-center"
                                                style="width: 32px; height: 32px; border-radius: 50%;">
                                                {{ item.creator?.name ? item.creator.name.substring(0, 2).toUpperCase()
                                                : '?' }}
                                            </div>
                                            <div class="d-flex flex-column small" style="line-height: 1.2;">
                                                <span class="fw-semibold text-dark">{{ item.creator?.name || 'Sistem'
                                                    }}</span>
                                                <span class="text-muted" style="font-size: 0.7rem;">
                                                    {{ daysTranslate(item.created_at) }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="text-center align-middle">
                                        <dropdown-action :item="item" :actions="[
                                            {
                                                label: 'Ubah Data',
                                                icon: 'bi bi-pencil-square fs-6',
                                                color: 'success', // Ganti color_icon jadi color agar konsisten
                                                action: 'edit',
                                                permission: 'branches.edit'
                                            },
                                            {
                                                label: 'Hapus',
                                                icon: 'bi bi-trash fs-6',
                                                color: 'danger',
                                                action: 'delete',
                                                permission: 'branches.delete'
                                            }
                                        ]" @edit="navigateTo('branch.edit', { id: item.branches_id }, 'Sedang membuka form edit...')"
                                            @delete="deleted('branch.deleted', item)" />
                                    </td>
                                </template>
                            </base-table>
                        </div>

                        <div
                            class="card-footer bg-white border-0 py-3 px-4 d-flex flex-column flex-md-row justify-content-between align-items-center">
                            <span class="text-muted mb-2 mb-md-0 small">
                                Menampilkan <strong>{{ props.branch?.from ?? 0 }}</strong> - <strong>{{
                                    props.branch?.to ?? 0 }}</strong> dari <strong>{{ props.branch?.total ?? 0
                                    }}</strong>
                            </span>
                            <pagination size="pagination-sm" :links="props.branch?.links" routeName="branch"
                                :additionalQuery="{
                                    order_by: filters.order_by,
                                    limit: filters.limit,
                                    keyword: filters.keyword,
                                }" />
                        </div>

                    </div>
                </div>
            </div>
        </template>
    </app-layout>
</template>
<style scoped>
/* Avatar Circle untuk kolom Creator */
.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
}

/* Font Monospace untuk kode */
.font-monospace {
    font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
    letter-spacing: -0.5px;
}
</style>
