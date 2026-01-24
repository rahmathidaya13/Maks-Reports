<script setup>
import { computed, onMounted, onUnmounted, reactive, ref, watch } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { debounce, filter } from "lodash";
import moment from "moment";
import { highlight } from "@/helpers/highlight";
import { swalConfirmDelete } from "@/helpers/swalHelpers";
import { formatTextFromSlug } from "@/helpers/formatTextFromSlug";
import UserModal from "./UserModal.vue";
import axios from "axios";
moment.locale('id');

import { useConfirm } from "@/helpers/useConfirm.js"
const confirm = useConfirm(); // Memanggil fungsi confirm untuk alert delete

const page = usePage();
const message = computed(() => page.props.flash.message || "");
const props = defineProps({
    users: Object,
    filters: Object,
});


const filters = reactive({
    active_emp: props.filters.active_emp ?? null,
    keyword: props.filters.keyword ?? '',
    limit: props.filters.limit ?? null,
    order_by: props.filters.order_by ?? null,
    page: props.filters?.page ?? 1,
})

// CRUD OPERATION
const isLoading = ref(false)
const liveSearch = debounce(() => {
    isLoading.value = true
    router.get(route("users"), filters, {
        preserveScroll: true,
        replace: true,
        preserveState: true,
        only: ["users", "filters"],
        onFinish: () => {
            isLoading.value = false
        }
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
        label: "Nama Pengguna",
        key: "name",
        attrs: {
            class: "text-center pe-xl-4",

        }
    },
    {
        label: "Status Pegawai",
        key: "status",
        attrs: {
            class: "text-center"
        }
    },
    {
        label: "Daring",
        key: "is_active",
        attrs: {
            class: "text-center"
        }
    },
    {
        label: "Peran",
        key: "roles",
        attrs: {
            class: "text-center"
        }
    },
    {
        label: "Awal Masuk",
        key: "first_login",
        attrs: {
            class: "text-center"
        }
    },
    {
        label: "Terakhir Keluar",
        key: "last_login",
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
        filters.order_by,
        filters.active_emp,
    ],
    () => liveSearch()
);

const loaderActive = ref(null)
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
            loaderActive.value?.show("Sedang memuat data...");
            router.post(route('users.destroy_all'), { all_id: selectedRow.value }, {
                preserveScroll: true,
                preserveState: false,
                onFinish: () => loaderActive.value?.hide(),
            })
        },
    })
}
const isAllSelected = computed(() => {
    const rows = props.users?.data ?? [];
    return rows.length > 0 && selectedRow.value.length === rows.length;
})
const isSelected = (id) => {
    return selectedRow.value.includes(id);
}

const toggleAll = (evt) => {
    if (evt.target.checked) {
        selectedRow.value = props.users?.data.map(r => r.id);
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
// atur warna badge sesuai jenis permission

const deleted = async (nameRoute, data) => {
    const setConfirm = await confirm.ask({
        title: 'Hapus',
        message: `Yakin ingin menghapus data pengguna ${formatTextFromSlug(data.name)}?`,
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

const edit = (id) => {
    loaderActive.value?.show("Sedang memuat data...");
    router.get(route("users.edit", id), {}, {
        onFinish: () => loaderActive.value?.hide()
    });
}


const sync = () => {
    isLoading.value = true
    router.get(route('users.refresh'), {}, {
        preserveScroll: true,
        onSuccess: () => {
            router.reload({ only: ['users', 'filters'] });
        },
        onFinish: () => {
            // Selesai apapun hasilnya → loader hilang
            isLoading.value = false
        }
    });
}

const filterFields = [
    {
        key: 'keyword',
        label: 'Pencarian',
        col: 'col-xl-6 col-md-12 col-12',
        type: 'search',
        icon: 'fas fa-search',
        autofocus: true,
        props: {
            placeholder: 'Masukan nama pengguna...',
            inputClass: 'border-start-0 ps-2 shadow-none',
            isValid: false,
        }
    },
    {
        key: 'limit',
        label: 'Tampilkan',
        type: 'select',
        col: 'col-xl-2 col-md-4 col-12',
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
        col: 'col-xl-2 col-md-4 col-12',
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
    {
        key: 'active_emp',
        label: 'Status',
        type: 'select',
        col: 'col-xl-2 col-md-4 col-12',
        icon: 'fas fa-dot-circle',
        props: {
            selectClass: 'border-start-0 ps-2 shadow-none',
            isValid: false,
        },
        options: [
            { value: null, label: 'Pilih Status' },
            { value: 'active', label: 'Aktif' },
            { value: 'inactive', label: 'Non-Aktif' },
        ]
    },
];
// =========Tampilkan Modal========== //
const showModal = ref(false);
const selectedData = ref(null);
const openModalUser = async (id) => {
    if (!id) {
        selectedData.value = null;
        return;
    }
    const { data } = await axios.get(route('users.detail', id));
    if (data.status) {
        selectedData.value = data.users;
    }
    console.log(data.users)
    showModal.value = true;
}
// =========Batas untuk Tampilkan Modal========== //

const goDetail = (id) => {
    loaderActive.value?.show("Sedang memuat data...");
    router.get(route("users.detail", id), {}, {
        onFinish: () => loaderActive.value?.hide()
    });
}

const rowClass = ({ active }) => (console.log(active), {

    'bg-primary': active,
})

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
        label: 'Segarkan',
        icon: 'fas fa-redo-alt',
        iconColor: 'text-primary',
        loading: isLoading.value,
        click: sync
    }
]);
</script>
<template>

    <Head title="Halaman Izin Hak Pengguna" />
    <app-layout>
        <template #content>
            <loader-page ref="loaderActive" />
            <bread-crumbs :home="false" icon="fas fa-user-cog" title="Izin Pengguna"
                :items="[{ text: 'Izin Pengguna' }]" />
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
                                    <i class="fas fa-user-cog fs-5"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0 text-dark">Daftar Pengguna</h5>
                                    <p class="text-muted small mb-0">
                                        Kelola data pengguna yang telah terdaftar.
                                    </p>
                                </div>
                            </div>
                            <action-toolbar :actions="toolbarActions" />
                        </div>

                        <div class="card-body position-relative p-0">

                            <base-table markAll :loader="isLoading" loaderText="Sedang memuat data..." :headers="header"
                                :items="users.data" row-key="id" @update:selected="(val) => selectedRow = val">

                                <template #empty>
                                    <i class="fas fa-user-cog fa-3x text-muted opacity-25 mb-3"></i>
                                    <p class="text-muted fw-semibold">Data pengguna tidak ditemukan.</p>
                                </template>

                                <template #row="{ item, index }">
                                    <td class="text-center text-muted fw-bold small">
                                        {{
                                            index +
                                            1 +
                                            (users?.current_page - 1) * users?.per_page
                                        }}
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div
                                                class="avatar-circle me-3 bg-primary-subtle text-primary fw-bold shadow-sm">
                                                {{ item.name.substring(0, 2).toUpperCase() }}
                                            </div>
                                            <div>
                                                <a href="#" @click.prevent="openModalUser(item.id)"
                                                    class="text-decoration-none fw-bold d-block hover-primary">
                                                    <span v-html="highlight(item.name, filters.keyword)" />
                                                </a>
                                                <small class="text-muted d-block"
                                                    v-html="highlight(item.email, filters.keyword)"></small>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="text-center">
                                        <span
                                            :class="item.status === 'active' ? 'badge-soft-primary' : 'badge-soft-danger'">
                                            {{ formatTextFromSlug(item.status) }}
                                        </span>
                                    </td>

                                    <td class="text-center">
                                        <div class="d-flex align-items-center justify-content-center gap-2">
                                            <span class="status-dot"
                                                :class="item.is_active ? 'bg-success' : 'bg-secondary'"></span>
                                            <span class="small fw-semibold"
                                                :class="item.is_active ? 'text-success' : 'text-secondary'">
                                                {{ item.is_active ? 'Online' : 'Offline' }}
                                            </span>
                                        </div>
                                    </td>

                                    <td class="text-center">
                                        <div class="d-flex flex-wrap gap-1 justify-content-center">
                                            <span v-for="role in item.roles" :key="role" class="badge-role">
                                                {{ formatTextFromSlug(role) }}
                                            </span>
                                        </div>
                                    </td>

                                    <td class="text-center small text-muted">
                                        <div class="d-flex flex-column">
                                            <span><i class="far fa-clock me-1 text-xs"></i> {{ item.first_login
                                                ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center small text-muted">
                                        <div class="d-flex flex-column">
                                            <span><i class="far fa-clock me-1 text-xs"></i> {{ item.last_login
                                                ?? '-' }}</span>
                                        </div>
                                    </td>

                                    <td class="text-center">
                                        <div class="d-flex justify-content-center">
                                            <dropdown-action :item="item" :actions="[
                                                {
                                                    label: 'Ubah Data',
                                                    icon: 'bi bi-pencil-square fs-6',
                                                    color: 'success',
                                                    action: 'edit',
                                                },
                                                {
                                                    type: 'divider'
                                                },
                                                {
                                                    label: 'Hapus Akun',
                                                    icon: 'bi bi-trash-fill fs-6',
                                                    color: 'danger',
                                                    action: 'delete',

                                                }
                                            ]" @edit="edit(item.id)" @delete="deleted('users.delete', item)" />
                                        </div>
                                    </td>
                                </template>
                            </base-table>

                        </div>
                        <div class="card-footer bg-white border-0 py-3" v-if="users?.data.length">
                            <div class="d-flex flex-wrap justify-content-between align-items-center">
                                <div class="text-muted small mb-2 mb-md-0">
                                    Menampilkan <strong>{{ users?.from ?? 0 }}</strong> -
                                    <strong>{{ users?.to ?? 0 }}</strong> dari
                                    <strong>{{ users?.total ?? 0 }}</strong> data
                                </div>
                                <pagination size="pagination-sm" :links="users?.links" routeName="users"
                                    :additionalQuery="{
                                        order_by: filters.order_by,
                                        limit: filters.limit,
                                        keyword: filters.keyword,
                                        active_emp: props.filters.active_emp,
                                    }" />
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <UserModal :users="selectedData" :show="showModal" @update:show="showModal = $event" />

        </template>
    </app-layout>

</template>
<style scoped>
/* Animasi saat loading muncul/hilang */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}



/* Custom Scrollbar */
.custom-scroll::-webkit-scrollbar {
    height: 8px;
}

.custom-scroll::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}

/* Row Hover & Selected */
.transition-all {
    transition: all 0.2s ease;
}

.table-active-row {
    background-color: rgba(79, 70, 229, 0.04) !important;
}

/* Avatar Style */
.avatar-circle {
    width: 38px;
    height: 38px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
}

/* Soft Badge Style (Lebih Elegan dari Solid Badge) */
.badge-soft-primary {
    background-color: #e0e7ff;
    color: #4338ca;
    padding: 5px 12px;
    border-radius: 8px;
    font-size: 0.75rem;
    font-weight: 700;
}

.badge-soft-danger {
    background-color: #fee2e2;
    color: #b91c1c;
    padding: 5px 12px;
    border-radius: 8px;
    font-size: 0.75rem;
    font-weight: 700;
}

.badge-role {
    background-color: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
    padding: 2px 8px;
    border-radius: 6px;
    font-size: 0.7rem;
    font-weight: 600;
}

/* Status Indicator */
.status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}


.hover-scale {
    transition: transform 0.2s;
}

.hover-scale:hover {
    transform: scale(1.02);
}

.pop-enter-active,
.pop-leave-active {
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.pop-enter-from,
.pop-leave-to {
    opacity: 0;
    transform: scale(0.8);
}

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

/* Row Selected State */
.row-selected {
    /* Biru muda jika checkbox dicentang */
    background-color: #d7e0ec !important;
}

/* Custom Checkbox Size */
.custom-checkbox {
    width: 1.1em;
    height: 1.1em;
    cursor: pointer;
}
</style>
