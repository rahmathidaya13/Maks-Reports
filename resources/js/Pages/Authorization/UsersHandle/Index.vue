<script setup>
import { computed, onMounted, onUnmounted, reactive, ref, watch } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { debounce, filter } from "lodash";
import moment from "moment";
import { highlight } from "@/helpers/highlight";
import { swalConfirmDelete } from "@/helpers/swalHelpers";
import { formatTextFromSlug } from "@/helpers/formatTextFromSlug";

moment.locale('id');
const page = usePage();
const message = computed(() => page.props.flash.message || "");
const props = defineProps({
    users: Object,
    filters: Object,
});

const filters = reactive({
    active_emp: props.filters.active_emp ?? 'active',
    keyword: props.filters.keyword ?? '',
    limit: props.filters.limit ?? 5,
    order_by: props.filters.order_by ?? "desc",
    page: props.filters?.page ?? 1,
})
const liveSearch = debounce((e) => {
    router.get(route("users"), filters, {
        preserveScroll: true,
        replace: true,
        preserveState: true,
        only: ["users", "filters"], // optional: lebih hemat bandwidth jika kamu pakai Inertia partial reload
    });
}, 1000);


const header = [
    { label: "No", key: "__index" },
    { label: "Nama", key: "name" },
    { label: "Email", key: "email" },
    { label: "Status Pegawai", key: "status" },
    { label: "Daring", key: "is_active" },
    { label: "Peran", key: "roles" },
    { label: "Izin Akses", key: "permissions" },
    { label: "Awal Masuk", key: "first_login" },
    { label: "Terakhir Masuk", key: "last_login" },
    { label: "Aksi", key: "-" },
];

watch(
    () => [
        filters.keyword,
        filters.limit,
        filters.order_by,
        filters.active_emp,],
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
            router.post(route('users.destroy_all'), { all_id: selectedRow.value }, {
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
        case /create|download/i.test(permName):
            return 'bg-success'
        case /edit|update/i.test(permName):
            return 'bg-warning text-dark'
        case /delete|remove|cancel/i.test(permName):
            return 'bg-danger'
        case /read|share|export|import/i.test(permName):
            return 'bg-info text-white'
        case /manage|access|assign/i.test(permName):
            return 'bg-primary'
        default:
            return 'bg-secondary'
    }
}
const deleted = (nameRoute, data) => {
    swalConfirmDelete({
        title: 'Hapus',
        text: `Yakin ingin menghapus data pengguna ${formatTextFromSlug(data.name)}?`,
        confirmText: 'Ya, Hapus!',
        onConfirm: () => {
            router.delete(route(nameRoute, data.id), { preserveScroll: true, replace: true });
        },
    })
}

const toast = ref(null) // hanya tampil jika ada user baru
// cek user jika ada yang registrasi, masuk ataupun keluar
onMounted(() => {
    window.Echo.channel('user-status')
        .listen('.status.changed', (data) => {
            const shouldClearCache = data.new_user_registered || data.recently_logged_in || data.recently_logged_out;
            const new_user_registered = data.new_user_registered; // hanya tampil toast untuk user baru
            if (shouldClearCache) {
                if (new_user_registered) {
                    const names = data.new_user_name.slice(0, 3).join(", ");
                    const remaining = data.new_user_name.length - 3;
                    const message = remaining > 0 ? `Terdaftar pengguna baru ${names} dan ${remaining} lainnya` : `Terdaftar pengguna baru ${names}`;
                    // tampilkan toast
                    toast.value.show('success', message, { timer: 10000 });
                }
            }
        });
});
onUnmounted(() => {
    window.Echo.leaveChannel('user-status');
});

const isLoading = ref(false)
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


</script>
<template>

    <Head title="Halaman Hak Pengguna" />
    <app-layout>
        <template #content>
            <bread-crumbs :home="false" icon="fas fa-user-cog" title="Izin Pengguna"
                :items="[{ text: 'Izin Pengguna' }]" />
            <alert :duration="10" :message="message" />
            <div class="row">
                <div class="col-xl-12 col-sm-12">
                    <swal-toast ref="toast" />
                    <div class="card mb-3 overflow-hidden rounded-4 p-1">
                        <div class="row align-items-center p-3 g-2">
                            <div class="col-xl-6 col-12 mb-0">
                                <div class="input-group">
                                    <text-input :is-valid="false" autofocus v-model="filters.keyword" name="keyword"
                                        placeholder="Pencarian....." />
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                </div>
                            </div>
                            <div class="col-xl-4 col-12 mb-xl-0 mb-0 d-flex gap-2">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-sort"></i></span>
                                    <select-input :is-valid="false" v-model="filters.limit" name="limit" :options="[
                                        { value: 5, label: '5' },
                                        { value: 10, label: '10' },
                                        { value: 25, label: '25' },
                                        { value: 50, label: '50' },
                                        { value: 100, label: '100' },
                                    ]" />
                                </div>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-filter"></i></span>
                                    <select-input :is-valid="false" v-model="filters.order_by" name="order_by" :options="[
                                        { value: 'desc', label: 'Terbaru' },
                                        { value: 'asc', label: 'Terlama' },
                                    ]" />
                                </div>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-dot-circle"></i></span>
                                    <select-input :is-valid="false" v-model="filters.active_emp" name="active_emp"
                                        :options="[
                                            { value: 'active', label: 'Aktif' },
                                            { value: 'inactive', label: 'Non-Aktif' },
                                        ]" />
                                </div>
                            </div>
                            <div class="col-xl-2 col-12 mb-xl-0 mb-0 d-flex">
                                <button :class="{ 'btn-dark': isLoading, 'btn-primary': !isLoading }"
                                    :disabled="isLoading" @click="sync" class="btn  bg-gradient px-5"><i class="fas"
                                        :class="{ 'fa-spinner fa-spin': isLoading, 'fa-sync': !isLoading }"
                                        style="font-size:18px"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <button-delete-all text="Hapus" :isVisible="isVisible" :deleted="deleteSelected" />
                    </div>
                    <div class="card mb-4 overflow-hidden rounded-4" :class="{ 'h-100': isLoading }">
                        <div v-if="isLoading">
                            <loader-horizontal />
                        </div>
                        <div class="table-responsive" v-else>
                            <base-table @update:selected="selectedRow = $event" :attributes="{ id: 'id', name: 'name' }"
                                :data="props.users" :headers="header">
                                <template #cell="{ row, keyName }">
                                    <template v-if="keyName == 'name'">
                                        <div class="text-start">
                                            <Link :href="route('users.detail', row.id)" class="text-decoration-none"><i
                                                class="fas fa-user"></i> <span
                                                v-html="highlight(row.name, filters.keyword)" />
                                            </Link>
                                        </div>
                                    </template>

                                    <template v-if="keyName == 'roles'">
                                        <div class="fw-bold">
                                            {{ formatTextFromSlug(row.roles.join(', ')) }}
                                        </div>
                                    </template>

                                    <template v-if="keyName == 'is_active'">
                                        <span :class="[
                                            'badge text-capitalize px-3 py-2',
                                            row.is_active == true ? 'bg-success' : 'bg-secondary',
                                        ]">
                                            {{ formatTextFromSlug(row.is_active == true ? 'Online' : 'Offline')
                                            }}
                                        </span>
                                    </template>
                                    <template v-if="keyName == 'status'">
                                        <span :class="[
                                            'badge text-capitalize px-3 py-2',
                                            row.status == 'active' ? 'bg-primary' : 'bg-danger',
                                        ]">
                                            {{ formatTextFromSlug(row.status) }}
                                        </span>
                                    </template>

                                    <template v-if="keyName === 'permissions'">
                                        <ul class="list-unstyled mb-0 d-flex flex-wrap gap-2">
                                            <li v-for="permission in row.permissions" :key="permission">
                                                <span :class="[
                                                    'badge text-capitalize px-3 py-2',
                                                    getBadgeClass(permission)
                                                ]">
                                                    {{ formatTextFromSlug(permission) }}
                                                </span>
                                            </li>
                                        </ul>
                                    </template>

                                    <template v-if="keyName === '-'">
                                        <div class="d-flex gap-1 align-items-center justify-content-center">
                                            <Link :href="route('users.edit', row.id)"
                                                class="btn btn-sm btn-info text-white px-3"><i class="fas fa-edit"></i>
                                            </Link>
                                            <button class="btn btn-sm btn-outline-danger px-3"
                                                @click="deleted('users.delete', row)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </template>
                                </template>
                            </base-table>
                        </div>
                        <div v-if="!isLoading"
                            class="d-flex flex-wrap justify-content-lg-between align-items-center flex-column flex-lg-row p-3">
                            <div class="mb-2 order-1 order-xl-0">
                                Menampilkan <strong>{{ props.users?.from ?? 0 }}</strong> sampai
                                <strong>{{ props.users?.to ?? 0 }}</strong> dari total
                                <strong>{{ props.users?.total ?? 0 }}</strong> data
                            </div>
                            <pagination :links="props.users?.links" :keyword="filters.keyword" routeName="users"
                                :additionalQuery="{
                                    order_by: filters.order_by,
                                    limit: filters.limit,
                                    keyword: filters.keyword,
                                    active_emp: filters.active_emp
                                }" />
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </app-layout>

</template>
<style>
.colored-toast.swal2-icon-success {
    background-color: #a5dc86 !important;
}

.colored-toast.swal2-icon-error {
    background-color: #f27474 !important;
}

.colored-toast.swal2-icon-warning {
    background-color: #f8bb86 !important;
}

.colored-toast.swal2-icon-info {
    background-color: #3fc3ee !important;
}

.colored-toast.swal2-icon-question {
    background-color: #87adbd !important;
}

.colored-toast .swal2-title {
    color: white;
}

.colored-toast .swal2-close {
    color: white;
}
</style>
