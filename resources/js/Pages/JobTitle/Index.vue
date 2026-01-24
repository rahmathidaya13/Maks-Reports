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
        attrs: {
            class: "text-center",
            style: "width:50px"
        }
    },
    {
        label: "Kode Jabatan",
        key: "job_title_code",
        attrs: {
            class: "text-center",
        }
    },
    {
        label: "Nama Jabatan",
        key: "title",
        attrs: {
            class: "text-center",
        }
    },
    {
        label: "Jabatan Alias",
        key: "title_alias",
        attrs: {
            class: "text-center",
        }
    },
    {
        label: "Deskripsi",
        key: "description",
        attrs: {
            class: "text-center",
        }
    },
    {
        label: "Dibuat Oleh",
        key: "created_by",
        attrs: {
            class: "text-center",
        }
    },
    {
        label: "Dibuat",
        key: "created_at",
        attrs: {
            class: "text-center",
        }
    },
    {
        label: "Diperbarui",
        key: "updated_at",
        attrs: {
            class: "text-center",
        }
    },
    {
        label: "Aksi",
        key: "-",
        attrs: {
            class: "text-center",
        }
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
const create = () => {
    loaderActive.value?.show("Memproses...");
    router.get(route("job_title.create"), {}, {
        onFinish: () => {
            loaderActive.value?.hide()
        }
    });
}

const edit = (id) => {
    loaderActive.value?.show("Sedang memuat data...");
    router.get(route("job_title.edit", id), {}, {
        onFinish: () => loaderActive.value?.hide()
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
    router.get(route("job_title.reset"), {}, {
        preserveScroll: true,
        replace: true,
        onFinish: () => isLoading.value = false
    });
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
        type: 'text',
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
        click: deleteSelected
    },

    {
        label: 'Jabatan Baru',
        icon: 'fas fa-plus-circle',
        isPrimary: true, // Prioritas Utama
        show: hasPermission('job.title.create'),
        click: create
    },
    {
        label: 'Segarkan',
        icon: 'fas fa-redo-alt',
        iconColor: 'text-primary',
        loading: isLoading.value,
        click: reset
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
                <div class="col-xl-12 col-12 mb-3">
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
                                    <div class="text-muted d-flex flex-column align-items-center">
                                        <i class="far fa-folder-open fs-1 mb-3 opacity-25"></i>
                                        <span>Tidak ada data ditemukan</span>
                                    </div>
                                </template>

                                <template #row="{ item, index }">
                                    <td class="ps-3 text-muted">{{ index + 1 + (jobTitle?.current_page
                                        - 1) * jobTitle?.per_page }}</td>

                                    <td class="ps-3">
                                        <div class="fw-bold text-dark"
                                            v-html="highlight(item.job_title_code, filters.keyword)"></div>
                                    </td>
                                    <td class="ps-3">
                                        <div v-html="highlight(item.title, filters.keyword)"></div>
                                    </td>
                                    <td class="text-center">
                                        <div v-html="highlight(item.title_alias, filters.keyword)"></div>
                                    </td>
                                    <td class="ps-3">
                                        <div class="text-muted" v-html="item.description"></div>
                                    </td>

                                    <td class="ps-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-circle bg-light text-primary fw-bold">
                                                {{ item.creator?.name ?
                                                    item.creator.name.substring(0, 2).toUpperCase() : '??' }}
                                            </div>
                                            <span>{{ item.creator?.name ?? '-' }}</span>
                                        </div>
                                    </td>

                                    <td class="ps-3 small text-muted">{{ daysTranslate(item.created_at) }}
                                    </td>
                                    <td class="ps-3 small text-muted">{{ daysTranslate(item.updated_at) }}
                                    </td>

                                    <td class="text-center">
                                        <dropdown-action :item="item" :actions="[
                                            {
                                                label: 'Ubah Jabatan',
                                                icon: 'bi bi-pencil-square fs-6',
                                                color_icon: 'success',
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
                                        ]" @edit="edit(item.job_title_id)"
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

/* --- DROPDOWN & ICON --- */
.btn-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.btn-icon:hover {
    background-color: #e9ecef;
    transform: rotate(90deg);
}

/* Animation Utilities */
.animate-pop {
    animation: popIn 0.3s cubic-bezier(0.68, -0.55, 0.27, 1.55);
}

@keyframes popIn {
    0% {
        transform: scale(0);
        opacity: 0;
    }

    100% {
        transform: scale(1);
        opacity: 1;
    }
}

.fs-7 {
    font-size: 0.75rem;
}
</style>
