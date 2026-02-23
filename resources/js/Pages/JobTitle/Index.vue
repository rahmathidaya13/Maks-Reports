<script setup>
import { computed, reactive, ref, watch } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { debounce } from "lodash";
import { highlight } from "@/helpers/highlight";
import { hasRole, hasPermission } from "@/composables/useAuth";
import moment from "moment";
moment.locale('id');

import { useConfirm } from "@/helpers/useConfirm.js"
const confirm = useConfirm(); // Memanggil fungsi confirm untuk alert delete

const props = defineProps({
    jobTitle: Object,
    filters: Object,
});
// console.log(props.jobTitle.data.);
const filters = reactive({
    keyword: props.filters.keyword ?? '',
    limit: props.filters.limit ?? null,
    order_by: props.filters.order_by ?? null,
    page: props.filters?.page ?? 1,
})

const isLoading = ref(false)
const liveSearch = debounce((e) => {
    isLoading.value = true
    router.get(route("job_title"), filters, {
        preserveScroll: true,
        replace: true,
        preserveState: true,
        only: ["jobTitle", "filters"], // optional: lebih hemat bandwidth jika kamu pakai Inertia partial reload
        onFinish: () => isLoading.value = false
    });
}, 1000);


const header = [
    {
        label: "No",
        key: "__index",
        attrs: { class: "text-center align-middle text-muted", style: "width: 50px" }
    },
    {
        label: "Informasi Jabatan",
        key: "title",
        attrs: { class: "text-start align-middle" }
    },
    {
        label: "Total Karyawan",
        key: "users_count",
        attrs: { class: "text-center align-middle", style: "width: 150px" }
    },
    {
        label: "Deskripsi",
        key: "description",
        attrs: { class: "text-center align-middle", style: "width: 35%" }
    },
    {
        label: "Pembuat",
        key: "created_by",
        attrs: { class: "text-center align-middle", style: "width: 280px" }
    },
    {
        label: "",
        key: "actions",
        attrs: { class: "text-center align-middle", style: "width: 80px" }
    },
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
const navigateTo = (routeName, params = {}, message = "Sedang membuka...") => {
    if (message) loaderActive.value?.show(message);
    router.get(route(routeName, params), {}, {
        onFinish: () => message && loaderActive.value?.hide(),
        preserveScroll: false,
        replace: true,
    });

}
const deleted = async (nameRoute, data) => {
    const setConfirm = await confirm.ask({
        title: 'Hapus',
        message: `Kamu ingin menghapus Jabatan ${data.title} ?`,
        confirmText: 'Ya, Hapus',
        variant: 'danger' // Memberikan warna merah pada tombol konfirmasi
    });

    if (setConfirm) {
        loaderActive.value?.show("Sedang menghapus data...");
        router.delete(route(nameRoute, data.job_title_id), {
            onFinish: () => loaderActive.value?.hide(),
            preserveScroll: false,
            replace: true
        });
    }
}

const reset = () => {
    isLoading.value = true
    navigateTo("job_title.reset", {}, false);
}
// end CRUD OPERATION

// MULTIPLE DELETE
const selectedRow = ref([]);
const isVisible = ref(false);
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
        router.post(route('job_title.destroy_all'), {
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

watch(selectedRow, (val) => {
    if (val.length > 0) {
        isVisible.value = true
    } else {
        isVisible.value = false
    }
})
// END MULTIPLE DELETE

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


const fileterFields = computed(() => [
    {
        key: 'keyword',
        label: 'Pencarian',
        col: 'col-xl-8 col-12',
        type: 'search',
        icon: 'fas fa-search',
        autofocus: true,
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

const toolbarActions = computed(() => [

    {
        label: `Hapus (${selectedRow.value.length})`,
        icon: 'fas fa-trash-alt',
        iconColor: 'text-danger',
        labelColor: 'text-danger',
        disabled: !selectedRow.value.length > 0,
        show: hasPermission('job.title.delete'),
        click: () => deleteSelected()
    },

    {
        label: 'Jabatan Baru',
        icon: 'fas fa-plus-circle',
        isPrimary: true, // Prioritas Utama
        show: hasPermission('job.title.create'),
        click: () => navigateTo("job_title.create")
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

    <Head title="Halaman Jabatan" />
    <app-layout>
        <template #content>
            <loader-page ref="loaderActive" />
            <bread-crumbs :home="false" icon="fas fa-briefcase" title="Daftar Jabatan"
                :items="[{ text: 'Daftar Jabatan' }]" />
            <callout />

            <div class="row pb-3">
                <div class="col-xl-12 col-12">
                    <base-filters title="Filter" v-model="filters" :fields="fileterFields" />
                </div>
                <div class="col-xl-12 col-12">
                    <div class="card card-modern border-0 shadow-sm rounded-4 overflow-hidden">
                        <div
                            class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">

                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3 me-3">
                                    <i class="fas fa-briefcase fs-5"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0 text-dark">Data Jabatan</h5>
                                    <p class="text-muted small mb-0">
                                        informasi jabatan tersedia
                                    </p>
                                </div>
                            </div>
                            <action-toolbar :actions="toolbarActions" />
                        </div>


                        <div class="card-body p-0 position-relative">

                            <base-table :markAll="hasPermission('job.title.delete')" :loader="isLoading"
                                loaderText="Sedang memuat data..." :headers="header" :items="jobTitle?.data"
                                row-key="job_title_id" @update:selected="(val) => selectedRow = val">

                                <template #empty>
                                    <div class="text-center py-5">
                                        <div class="bg-transparent rounded-circle d-inline-flex p-2 mb-3">
                                            <i class="fas fa-id-badge text-muted opacity-50 fs-1"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark">Data Jabatan Kosong</h6>
                                        <p class="text-muted small">Belum ada data jabatan yang terdaftar.</p>
                                    </div>
                                </template>

                                <template #row="{ item, index }">
                                    <td class="text-center text-muted fw-medium align-middle">
                                        {{ index + 1 + (jobTitle?.current_page - 1) * jobTitle?.per_page }}
                                    </td>

                                    <td class="ps-3 align-middle py-3">
                                        <div class="d-flex align-items-start">
                                            <div class="bg-primary bg-opacity-10 text-primary rounded-3 d-flex align-items-center justify-content-center me-3 mt-1"
                                                style="width: 36px; height: 36px;">
                                                <i class="fas fa-briefcase"></i>
                                            </div>

                                            <div class="d-flex flex-column">
                                                <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                                                    <span class="fw-bold text-dark fs-6 text-capitalize"
                                                        v-html="highlight(item.title, filters.keyword)"></span>

                                                    <span v-if="item.title_alias"
                                                        class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-10 rounded-pill px-2 py-1"
                                                        style="font-size: 0.65rem; letter-spacing: 0.5px;">
                                                        <span
                                                            v-html="highlight(item.title_alias, filters.keyword)"></span>
                                                    </span>
                                                </div>

                                                <div>
                                                    <span
                                                        class="font-monospace text-muted bg-light border rounded px-2 py-1 d-inline-block"
                                                        style="font-size: 0.75rem;">
                                                        <i class="fas fa-hashtag me-1 opacity-50"></i>
                                                        <span
                                                            v-html="highlight(item.job_title_code, filters.keyword)"></span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="text-center align-middle">
                                        <span class="badge rounded-pill px-3 py-2 fw-medium"
                                            :class="item.profile_count > 0 ? 'bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10' : 'bg-light text-muted border'">
                                            <i class="fas fa-users me-1" style="font-size: 0.7rem;"></i>
                                            {{ item.profile_count || 0 }} Orang
                                        </span>
                                    </td>

                                    <td class="ps-3 align-middle text-muted small" style="line-height: 1.4;">
                                        <span v-if="item.description" v-html="item.description"></span>
                                        <span v-else class="fst-italic opacity-50">Tidak ada deskripsi</span>
                                    </td>

                                    <td class="ps-3 align-middle">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle me-3 bg-gradient-light border text-primary fw-bold small d-flex align-items-center justify-content-center shadow-sm"
                                                style="width: 36px; height: 36px; border-radius: 50%;">
                                                {{ item.creator?.name ? item.creator.name.substring(0, 2).toUpperCase()
                                                    : '??' }}
                                            </div>
                                            <div class="d-flex flex-column small" style="line-height: 1.3;">
                                                <span class="fw-bold text-dark mb-1">{{ item.creator?.name || 'Sistem'
                                                    }}</span>
                                                <div class="text-muted d-flex flex-column" style="font-size: 0.7rem;">
                                                    <span><i class="fas fa-plus-circle me-1 opacity-50"></i> {{
                                                        daysTranslate(item.created_at) }}</span>
                                                    <span v-if="item.updated_at !== item.created_at">
                                                        <i class="fas fa-pencil-alt me-1 opacity-50"></i> {{
                                                            daysTranslate(item.updated_at) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="text-center align-middle">
                                        <dropdown-action :item="item" :actions="[
                                            {
                                                label: 'Ubah Jabatan',
                                                icon: 'bi bi-pencil-square fs-6',
                                                color: 'success', // disamakan pakai parameter color
                                                action: 'edit',
                                                permission: 'job.title.edit'
                                            },
                                            {
                                                label: 'Hapus',
                                                icon: 'bi bi-trash fs-6',
                                                color: 'danger',
                                                action: 'delete',
                                                permission: 'job.title.delete'
                                            }
                                        ]" @edit="navigateTo('job_title.edit', item.job_title_id)"
                                            @delete="deleted('job_title.deleted', item)" />
                                    </td>
                                </template>
                            </base-table>
                        </div>

                        <div
                            class="card-footer bg-white border-0 py-3 px-4 d-flex flex-column flex-md-row justify-content-between align-items-center">
                            <span class="text-muted mb-2 mb-md-0">
                                Menampilkan <strong>{{ props.jobTitle.from ?? 0 }}</strong> - <strong>{{
                                    props.jobTitle.to ?? 0 }}</strong> dari <strong>{{ props.jobTitle.total ?? 0
                                    }}</strong>
                            </span>
                            <pagination size="pagination-sm" :links="props.jobTitle?.links" routeName="job_title"
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
/* Card Modern */
.card-modern {
    background: #ffffff;
    transition: all 0.3s ease;
}

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

.font-monospace {
    font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
    letter-spacing: -0.3px;
}
</style>
