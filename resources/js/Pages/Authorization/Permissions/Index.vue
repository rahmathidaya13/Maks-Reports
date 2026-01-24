<script setup>
import { computed, reactive, ref, watch } from "vue";
import { Head, Link, router, useForm, usePage } from "@inertiajs/vue3";
import { debounce, filter } from "lodash";
import { highlight } from "@/helpers/highlight";
import { swalConfirmDelete } from "@/helpers/swalHelpers";
import { formatTextFromSlug } from "@/helpers/formatTextFromSlug";
import { cleanTextCapitalize } from "@/helpers/cleanTextCapitalize";
import moment from "moment";
moment.locale('id');
import { useConfirm } from "@/helpers/useConfirm.js"
const confirm = useConfirm(); // Memanggil fungsi confirm untuk alert delete

const page = usePage();
const message = computed(() => page.props.flash.message || "");
const props = defineProps({
    permissions: Object,
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
    router.get(route("permissions"), filters, {
        preserveScroll: true,
        replace: true,
        preserveState: true,
        only: ["permissions", "filters"], // optional: lebih hemat bandwidth jika kamu pakai Inertia partial reload
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
        label: "Nama Izin Akses",
        key: "name",
        attrs: {
            class: "text-center pe-xl-4",

        }
    },
    {
        label: "Dibuat",
        key: "created_at",
        attrs: {
            class: "text-center"
        }
    },
    {
        label: "Diperbarui",
        key: "updated_at",
        attrs: {
            class: "text-center"
        }
    },
    {
        label: "Aksi",
        key: "-",
        attrs: {
            class: "text-center",
            style: "width:100px"
        }
    }
];
watch(
    () => [
        filters.keyword,
        filters.limit,
        filters.order_by,],
    () => liveSearch()
);

// crud operation
const selectedRow = ref([]);
const isVisible = ref(false);
const loaderActive = ref(null)
const create = () => {
    loaderActive.value?.show("Memproses...");
    router.get(route("permissions.create"), {}, {
        onFinish: () => {
            loaderActive.value?.hide()
        }
    });
}


const edit = (id) => {
    loaderActive.value?.show("Sedang memuat data...");
    router.get(route("permissions.edit", id), {}, {
        onFinish: () => loaderActive.value?.hide()
    });
}
const deleteSelected = async () => {
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
        router.post(route('permissions.destroy_all'), {
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
const goEditMultiple = () => {
    loaderActive.value?.show("Sedang memuat data...");
    if (!selectedRow.value.length) {
        return swalAlert('Peringatan', 'Tidak ada data yang dipilih.', 'warning');
    }
    router.get(route('permissions.edited_all'), { all_id: selectedRow.value }, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
        onFinish: () => loaderActive.value?.hide(),
    })
}
watch(selectedRow, (val) => {
    if (val.length > 0) {
        isVisible.value = true
    } else {
        isVisible.value = false
    }
})

const deleted = async (nameRoute, data) => {
    const setConfirm = await confirm.ask({
        title: 'Hapus',
        message: `Yakin ingin menghapus ${formatTextFromSlug(data.name)}?`,
        confirmText: 'Ya, Hapus',
        variant: 'danger' // Memberikan warna merah pada tombol konfirmasi
    });

    if (setConfirm) {
        loaderActive.value?.show("Sedang menghapus data...");
        router.delete(route(nameRoute, data.id), {
            onFinish: () => loaderActive.value?.hide(),
            preserveScroll: false,
            replace: true
        });
    }
}
// end crud operation
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

// filter berdasarkan field yang dimau
const filterFields = [
    {
        key: 'keyword',
        label: 'Pencarian',
        col: 'col-xl-8 col-md-12 col-12',
        type: 'search',
        icon: 'fas fa-search',
        autofocus: true,
        props: {
            placeholder: 'Masukan pencarian...',
            inputClass: 'border-start-0 ps-2 shadow-none',
            isValid: false,
        }
    },
    {
        key: 'limit',
        label: 'Tampilkan',
        type: 'select',
        col: 'col-xl-2 col-md-6 col-12',
        icon: 'fas fa-list-ol',
        props: {
            selectClass: 'border-start-0 ps-2 shadow-none',
            isValid: false,
        },
        options: [
            { value: null, label: 'Pilih Batas' },
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
        col: 'col-xl-2 col-md-6 col-12',
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
];

const reset = () => {
    isLoading.value = true
    router.get(route("permissions.reset"), {}, {
        preserveScroll: false,
        replace: true,
        onFinish: () => isLoading.value = false
    });
}
const toolbarActions = computed(() => [

    {
        label: `Hapus (${selectedRow.value.length})`,
        icon: 'fas fa-trash-alt',
        iconColor: 'text-danger',
        labelColor: 'text-danger',
        disabled: !selectedRow.value.length > 0,
        click: deleteSelected
    },

    {
        label: `Ubah (${selectedRow.value.length})`,
        icon: 'fas fa-edit',
        iconColor: 'text-success',
        labelColor: 'text-success',
        disabled: !selectedRow.value.length > 0,
        click: goEditMultiple
    },
    {
        label: 'Izin Akses Baru',
        icon: 'fas fa-plus-circle',
        isPrimary: true, // Prioritas Utama
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

    <Head title="Halaman Izin Akses" />
    <app-layout>
        <template #content>
            <loader-page ref="loaderActive" />

            <bread-crumbs :home="false" icon="fas fa-user-lock" title="Daftar Izin Akses"
                :items="[{ text: 'Daftar Izin Akses' }]" />
            <callout />
            <div class="row pb-3">
                <div class="col-12">
                    <base-filters title="Filter" v-model="filters" :fields="filterFields" />
                </div>
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div
                            class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3 me-3">
                                    <i class="fas fa-user-lock fs-5"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0 text-dark">Data Izin Akses</h5>
                                    <p class="text-muted small mb-0">
                                        Kelola daftar izin akses yang tersedia
                                    </p>
                                </div>
                            </div>

                            <action-toolbar :actions="toolbarActions" />

                        </div>
                        <div class="card-body p-0 position-relative">
                            <base-table markAll :loader="isLoading" loaderText="Sedang memuat data..." :headers="header"
                                :items="permissions?.data" row-key="id" @update:selected="(val) => selectedRow = val">

                                <template #empty>
                                    <div class="py-4">
                                        <i class="fas fa-key fa-3x text-muted opacity-25 mb-3"></i>
                                        <p class="text-muted fw-semibold">Izin akses tidak ditemukan.</p>
                                    </div>
                                </template>

                                <template #row="{ item, index }">
                                    <td class="text-center text-muted fw-bold small">
                                        {{ index + 1 + (permissions?.current_page - 1) * permissions?.per_page
                                        }}
                                    </td>

                                    <td class="text-capitalize ps-xl-5">
                                        <div class="d-flex align-items-center gap-1">
                                            <div class="avatar-circle bg-primary bg-opacity-10 text-primary fw-bold">
                                                <i class="fas fa-key"></i>
                                            </div>
                                            <span style="font-size: 0.9rem;"
                                                class="badge text-dark bg-secondary bg-opacity-10 border fw-semibold"
                                                v-html="highlight(item.name, filters.keyword)"></span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="text-muted fw-medium">{{ daysTranslate(item.created_at)
                                            }}</div>
                                    </td>
                                    <td class="text-center">
                                        <div class="text-muted fw-medium">
                                            {{ daysTranslate(item.updated_at)
                                            }}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center">
                                            <dropdown-action :item="item" :actions="[
                                                {
                                                    label: 'Ubah Data',
                                                    icon: 'fas fa-pen',
                                                    color_icon: 'success',
                                                    action: 'edit',
                                                },
                                                {
                                                    type: 'divider'
                                                },
                                                {
                                                    label: 'Hapus',
                                                    icon: 'fas fa-trash',
                                                    color: 'danger',
                                                    action: 'delete',

                                                }
                                            ]" @edit="edit(item.id)" @delete="deleted('permissions.delete', item)" />
                                        </div>
                                    </td>

                                </template>
                            </base-table>
                        </div>
                        <div class="card-footer bg-white border-0 py-3" v-if="permissions?.data.length">
                            <div class="d-flex flex-wrap justify-content-between align-items-center">
                                <div class="text-muted small mb-2 mb-md-0">
                                    Menampilkan <strong>{{ props.permissions?.from ?? 0 }}</strong> -
                                    <strong>{{ props.permissions?.to ?? 0 }}</strong> dari
                                    <strong>{{ props.permissions?.total ?? 0 }}</strong> data
                                </div>
                                <pagination size="pagination-sm" :links="props.permissions?.links"
                                    routeName="permissions" :additionalQuery="{
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
.avatar-circle {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    border: 1px solid #ccc;
}
</style>
