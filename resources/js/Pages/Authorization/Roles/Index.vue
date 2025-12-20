<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { debounce } from "lodash";
import moment from "moment";
import { highlight } from "@/helpers/highlight";
import { swalConfirmDelete } from "@/helpers/swalHelpers";
import { formatTextFromSlug } from "@/helpers/formatTextFromSlug";
import { cleanTextCapitalize } from "@/helpers/cleanTextCapitalize";
import axios from "axios";
moment.locale('id');
const page = usePage();
const message = computed(() => page.props.flash.message || "");
const props = defineProps({
    roles: Object,
    filters: Object,
});
const filters = reactive({
    keyword: props.filters.keyword ?? '',
    limit: props.filters.limit ?? 10,
    order_by: props.filters.order_by ?? "desc",
    page: props.filters?.page ?? 1,
})

const liveSearch = debounce((e) => {
    router.get(route("roles"), filters, {
        preserveScroll: true,
        replace: true,
        preserveState: true,
        only: ["roles", "filters"], // optional: lebih hemat bandwidth jika kamu pakai Inertia partial reload
    });
}, 1000);


const header = [
    { label: "No", key: "__index" },
    { label: "Role", key: "name" },
    { label: "Guard Name", key: "guard_name" },
    { label: "Created", key: "created_at" },
    { label: "Updated", key: "updated_at" },
    { label: "Action", key: "-" },
];

watch(
    () => [
        filters.keyword,
        filters.limit,
        filters.order_by,],
    () => liveSearch()
);

const selectedRow = ref([]);
const isVisible = ref(false);
function deleteSelected() {
    if (!selectedRow.value.length) {
        return swalAlert('Peringatan', 'Tidak ada data yang dipilih.', 'warning');
    }
    swalConfirmDelete({
        title: 'Hapus Data Terpilih',
        text: `Yakin ingin menghapus ${selectedRow.value.length} data terpilih?`,
        confirmText: 'Ya, Hapus Semua!',
        onConfirm: () => {
            router.post(route('roles.destroy_all'), { all_id: selectedRow.value }, {
                preserveScroll: true,
                preserveState: false,
            })
        },
    })
}

watch(selectedRow, (val) => {
    if (val.length > 0) {
        isVisible.value = true
    } else {
        isVisible.value = false
    }
})
// atur warna badge sesuai jenis permission
const getBadgeClass = (permName) => {
    switch (true) {
        case /create|export|import/i.test(permName):
            return 'bg-success'
        case /edit|update/i.test(permName):
            return 'bg-warning text-dark'
        case /delete|remove/i.test(permName):
            return 'bg-danger'
        case /view|show/i.test(permName):
            return 'bg-info text-dark'
        case /manage|access|assign|share/i.test(permName):
            return 'bg-primary'
        default:
            return 'bg-secondary'
    }
}
const deleted = (nameRoute, data) => {
    swalConfirmDelete({
        title: 'Hapus',
        text: `Yakin ingin menghapus ${formatTextFromSlug(data.name)} data terpilih?`,
        confirmText: 'Ya, Hapus!',
        onConfirm: () => {
            router.delete(route(nameRoute, data.id), { preserveScroll: true, replace: true });
        },
    })
}

// =========Tampilkan Modal========== //
const showModal = ref(false);
const rolesById = ref(null);
const rolesByName = ref(null);
const permissions = ref([]);
function openModal(id) {
    showModal.value = true
    rolesById.value = id;
}
// tutup modal SETELAH Bootstrap selesai animasi
function closeModal() {
    showModal.value = false
}

watch(() => rolesById.value,
    async (newId) => {
        if (!newId) {
            rolesById.value = null;
            return;
        }
        const { data } = await axios.get(route('roles.show', newId));
        if (data.status) {
            permissions.value = data.roles.permissions;
            rolesByName.value = data.roles.name;
        }
    })
// =========Batas untuk Tampilkan Modal========== //

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

    <Head title="Halaman Role" />
    <app-layout>
        <template #content>
            <bread-crumbs :home="false" icon="fas fa-briefcase" title="Daftar Role"
                :items="[{ text: 'Daftar Role' }]" />
            <alert :duration="10" :message="message" />
            <div class="row">
                <div class="col-xl-12 col-12 mb-3">
                    <filter-dynamic title="Filter" v-model="filters" :fields="fileterFields" />
                </div>
                <div class="col-xl-12 col-12">
                    <div class="gap-1 mb-2 d-flex justify-content-start">
                        <button-delete-all :disabled="!isVisible" :variant="[isVisible ? 'danger' : 'secondary']"
                            text="Hapus" :isVisible="true" :deleted="deleteSelected" />
                        <span class="border border-1 border-secondary-subtle"></span>
                        <Link :href="route('roles.create')" class="btn btn-success bg-gradient">
                            <i class="fas fa-plus"></i> Buat Baru
                        </Link>
                    </div>
                    <div class="card mb-4 overflow-hidden rounded-3">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <base-table variant="dark" @update:selected="selectedRow = $event"
                                    :attributes="{ id: 'id', name: 'name' }" :data="props.roles" :headers="header">
                                    <template #cell="{ row, keyName }">
                                        <template v-if="keyName === 'name'">
                                            <div class="text-start">
                                                <button @click="openModal(row.id)"
                                                    class="btn-link btn text-decoration-none">
                                                    <i class="fas fa-role"></i>
                                                    <span
                                                        v-html="highlight(cleanTextCapitalize(row.name), filters.keyword)"></span>
                                                </button>
                                            </div>
                                        </template>
                                        <template v-if="keyName === '-'">
                                            <div class="d-flex gap-1 align-items-center justify-content-center">
                                                <Link :href="route('roles.edit', row.id)"
                                                    class="btn btn-sm btn-info text-white px-3"><i
                                                        class="fas fa-edit"></i>
                                                </Link>
                                                <button class="btn btn-sm btn-outline-danger px-3"
                                                    @click="deleted('roles.delete', row)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
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
                                    Menampilkan <strong>{{ props.roles?.from ?? 0 }}</strong> sampai
                                    <strong>{{ props.roles?.to ?? 0 }}</strong> dari total
                                    <strong>{{ props.roles?.total ?? 0 }}</strong> data
                                </div>
                                <pagination size="pagination-sm" :links="props.roles?.links" :keyword="filters.keyword"
                                    routeName="roles" :additionalQuery="{
                                        order_by: filters.order_by,
                                        limit: filters.limit,
                                        keyword: filters.keyword,
                                    }" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row" v-if="showModal">
                <div class="col-xl-12 col-sm-12">
                    <modal @opened="openModal" size="modal-lg" :footer="false" icon="fas fa-info-circle"
                        v-if="showModal" :show="showModal" title="Detail izin Akses" @update:show="showModal = $event"
                        @closed="closeModal">
                        <template #body>
                            <div class="row py-3">
                                <div class="col-xl-12 mb-3">
                                    <div class="text-bg-grey p-2 px-3 rounded-3 border">
                                        <h5 class="fw-bold">Roles: {{ cleanTextCapitalize(rolesByName) }}</h5>
                                    </div>
                                </div>
                                <div class="col-xl-12">
                                    <div class="text-bg-grey p-2 px-3 rounded-3 border">
                                        <h5 class="fw-bold">Izin Akses</h5>
                                        <ul class="list-unstyled mb-0 d-flex flex-wrap gap-1">
                                            <li v-for="perm in permissions" :key="perm.id">
                                                <span :class="[
                                                    'badge text-capitalize px-2 py-2',
                                                    getBadgeClass(perm.name)
                                                ]">
                                                    {{ cleanTextCapitalize(perm.name) }}
                                                </span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </modal>
                </div>
            </div>
        </template>
    </app-layout>
</template>
