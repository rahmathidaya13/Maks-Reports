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
}, 1000);
const header = [
    { label: "No", key: "__index" },
    { label: "Nama Izin Akses", key: "name" },
    { label: "Status", key: "guard_name" },
    { label: "Dibuat", key: "created_at" },
    { label: "Diperbarui", key: "updated_at" },
    { label: "Aksi", key: "-" },
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
function deleteSelected() {
    if (!selectedRow.value.length) {
        return swalAlert('Peringatan', 'Tidak ada data yang dipilih.', 'warning');
    }
    swalConfirmDelete({
        title: 'Hapus Data Terpilih',
        text: `Yakin ingin menghapus ${selectedRow.value.length} data terpilih?`,
        confirmText: 'Ya, Hapus Semua!',
        onConfirm: () => {
            router.post(route('permissions.destroy_all'), { all_id: selectedRow.value }, {
                preserveScroll: true,
                preserveState: false,
            })
        },
    })
}
const goEditMultiple = () => {
    if (!selectedRow.value.length) {
        return swalAlert('Peringatan', 'Tidak ada data yang dipilih.', 'warning');
    }
    router.get(route('permissions.edited_all'), { all_id: selectedRow.value }, {
        preserveScroll: true,
        preserveState: true,
    })
}
watch(selectedRow, (val) => {
    if (val.length > 0) {
        isVisible.value = true
    } else {
        isVisible.value = false
    }
})

const deleted = (nameRoute, data) => {
    swalConfirmDelete({
        title: 'Hapus',
        text: `Yakin ingin menghapus ${formatTextFromSlug(data.name)}?`,
        confirmText: 'Ya, Hapus!',
        onConfirm: () => {
            router.delete(route(nameRoute, data.id), { preserveScroll: true, replace: true });
        },
    })
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
const fileterFields = [
    {
        key: 'keyword',
        label: 'Pencarian',
        type: 'text',
        col: 'col-xl-8 col-12',
        props: {
            placeholder: 'Masukan pencarian...',
            inputClass: 'border-dark border-1 border input-height-1',
            autofocus: true,
            isValid: false,
        }
    },
    {
        key: 'limit',
        label: 'Batas',
        type: 'select',
        col: 'col-xl-2 col-md-6 col-6',
        props: {
            selectClass: 'border-dark border-1 border input-height-1',
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
        label: 'Urutkan',
        type: 'select',
        col: 'col-xl-2 col-md-6 col-6',
        props: {
            selectClass: 'border-dark border-1 border input-height-1',
            isValid: false,
        },
        options: [
            { value: null, label: 'Pilih Urutan' },
            { value: 'desc', label: 'Terbaru' },
            { value: 'asc', label: 'Terlama' },
        ]
    },
];
</script>
<template>

    <Head title="Halaman Izin Akses" />
    <app-layout>
        <template #content>
            <loader-page ref="loaderActive" />

            <bread-crumbs :home="false" icon="fas fa-cog" title="Daftar Izin Akses"
                :items="[{ text: 'Daftar Izin Akses' }]" />
            <callout type="success" :duration="10" :message="message" />
            <div class="row">
                <div class="col-xl-12 col-12 mb-3">
                    <filter-dynamic title="Filter" v-model="filters" :fields="fileterFields" />
                </div>
                <div class="col-xl-12 col-12">
                    <div class="gap-1 mb-2 d-flex justify-content-between">
                        <div class="gap-1 d-flex">
                            <base-button :variant="[isVisible ? 'primary' : 'secondary']" :disabled="!isVisible"
                                @click.prevent="goEditMultiple" label="Ubah" icon="fas fa-edit" />
                            <button-delete-all :disabled="!isVisible" :variant="[isVisible ? 'danger' : 'secondary']"
                                text="Hapus" :isVisible="true" :deleted="deleteSelected" />
                        </div>
                        <div class="ms-auto d-flex">
                            <button type="button" @click.prevent="create" class="btn btn-success bg-gradient">
                                <i class="fas fa-plus"></i> Buat Baru
                            </button>
                        </div>
                    </div>
                    <div class="card mb-4 overflow-hidden rounded-3 shadow">
                        <div v-if="isLoading">
                            <loader-horizontal message="Sedang memproses data" />
                        </div>
                        <div class="card-body p-0" :class="['blur-area', isLoading ? 'is-blurred' : '']">
                            <div class="table-responsive">
                                <base-table @update:selected="selectedRow = $event"
                                    :attributes="{ id: 'id', name: 'name' }" :data="props.permissions"
                                    :headers="header">
                                    <template #cell="{ row, keyName }">
                                        <template v-if="keyName === 'name'">
                                            <div class="fw-semibold text-start"
                                                v-html="highlight(row.name, filters.keyword)">
                                            </div>
                                        </template>
                                        <template v-if="keyName === '-'">
                                            <div class="dropdown dropstart">
                                                <button class="btn btn-secondary bg-gradient" type="button"
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-cog"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <Link :href="route('permissions.edit', row.id)"
                                                            class="dropdown-item fw-semibold d-flex justify-content-between align-items-center">
                                                            Ubah <i class="bi bi-pencil-square text-info fs-5"></i>
                                                        </Link>
                                                    </li>
                                                    <li>
                                                        <button @click.prevent="deleted('permissions.delete', row)"
                                                            class="dropdown-item fw-semibold d-flex justify-content-between align-items-center">
                                                            Hapus <i class="bi bi-trash-fill text-danger fs-5"></i>
                                                        </button>
                                                    </li>
                                                </ul>
                                            </div>
                                        </template>
                                        <template v-if="keyName === 'created_at'">
                                            {{ daysTranslate(row.created_at) }}
                                        </template>
                                        <template v-if="keyName === 'updated_at'">
                                            {{ daysTranslate(row.updated_at) }}
                                        </template>
                                    </template>
                                </base-table>
                            </div>
                        </div>
                        <div class="card-footer pb-0">
                            <div
                                class="d-flex flex-wrap justify-content-lg-between align-items-center flex-column flex-lg-row">
                                <div class="mb-2 order-1 order-xl-0">
                                    Menampilkan <strong>{{ props.permissions?.from ?? 0 }}</strong> sampai
                                    <strong>{{ props.permissions?.to ?? 0 }}</strong> dari total
                                    <strong>{{ props.permissions?.total ?? 0 }}</strong> data
                                </div>
                                <pagination size="pagination-sm" :links="props.permissions?.links"
                                    :keyword="filters.keyword" routeName="permissions" :additionalQuery="{
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
</style>
