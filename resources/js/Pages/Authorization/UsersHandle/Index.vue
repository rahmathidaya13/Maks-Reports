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

// CRUD OPERATION
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

const edit = (id) => {
    loaderActive.value?.show("Sedang memuat data...");
    router.get(route("users.edit", id), {}, {
        onFinish: () => loaderActive.value?.hide()
    });
}
const goDetail = (id) => {
    loaderActive.value?.show("Sedang memuat data...");
    router.get(route("users.detail", id), {}, {
        onFinish: () => loaderActive.value?.hide()
    });
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

    <Head title="Halaman Izin Hak Pengguna" />
    <app-layout>
        <template #content>
            <loader-page ref="loaderActive" />
            <bread-crumbs :home="false" icon="fas fa-user-cog" title="Izin Pengguna"
                :items="[{ text: 'Izin Pengguna' }]" />
            <alert :duration="10" :message="message" />
            <div class="row">
                <div class="col-xl-12 col-sm-12">
                    <swal-toast ref="toast" />
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">

                            <div class="d-flex">
                                <button :disabled="!isVisible" @click="deleteSelected" type="button"
                                    class="btn btn-danger position-relative bg-gradient">
                                    <i class="fas fa-trash"></i> Hapus
                                    <span v-if="selectedRow.length > 0"
                                        class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">
                                        {{ selectedRow.length }}
                                    </span>
                                </button>
                            </div>

                            <div class="row g-1 align-items-center ms-auto">
                                <div class="col-auto">
                                    <div class="input-group">
                                        <text-input input-class="border-dark border-1 border" :is-valid="false"
                                            autofocus v-model="filters.keyword" name="keyword"
                                            placeholder="Cari pengguna..." />
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <div class="input-group">
                                        <select-input select-class="border-dark border-1 border" :is-valid="false"
                                            v-model="filters.limit" name="limit" :options="[
                                                { value: 5, label: '5' },
                                                { value: 10, label: '10' },
                                                { value: 25, label: '25' },
                                                { value: 50, label: '50' },
                                                { value: 100, label: '100' },
                                            ]" />
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <div class="input-group">
                                        <select-input select-class="border-dark border-1 border" :is-valid="false"
                                            v-model="filters.order_by" name="order_by" :options="[
                                                { value: 'desc', label: 'Terbaru' },
                                                { value: 'asc', label: 'Terlama' },
                                            ]" />
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <div class="input-group">
                                        <select-input select-class="border border-1 border-dark" :is-valid="false"
                                            v-model="filters.active_emp" name="active_emp" :options="[
                                                { value: 'active', label: 'Aktif' },
                                                { value: 'inactive', label: 'Non-Aktif' },
                                            ]" />
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <button :class="{ 'btn-dark': isLoading, 'btn-primary': !isLoading }"
                                        :disabled="isLoading" @click="sync" class="btn bg-gradient"><i class="fas me-2"
                                            :class="{ 'fa-spinner fa-spin': isLoading, 'fa-sync': !isLoading }"></i>
                                        <span>{{ !isLoading ? 'Segarkan' : 'Diproses' }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card mb-4 overflow-hidden rounded-3">
                        <div v-if="isLoading">
                            <loader-horizontal message="Sedang memproses data" />
                        </div>
                        <div class="card-body p-0" :class="['blur-area', isLoading ? 'is-blurred' : '']">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered table-striped text-nowrap">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>
                                                <div class="form-check d-flex justify-content-center">
                                                    <input :disabled="!users?.data.length" type="checkbox"
                                                        class="form-check-input" :checked="isAllSelected"
                                                        @change="toggleAll($event)" />
                                                </div>
                                            </th>
                                            <th v-for="col in header" :key="col.key" class="text-center">
                                                {{ col.label }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="!users?.data.length">
                                            <td :colspan="header.length + 1" class="text-center py-3 text-muted">
                                                Tidak ada data tersedia.
                                            </td>
                                        </tr>

                                        <tr class="align-middle" v-for="(item, index) in users?.data" :key="index"
                                            :class="{ 'table-info': isSelected(item.id) }">

                                            <td class="text-center">
                                                <div class="form-check d-flex justify-content-center">
                                                    <input type="checkbox" class="form-check-input" :name="item.id"
                                                        :id="item.id" :value="item.id" v-model="selectedRow" />
                                                </div>
                                            </td>

                                            <td class="text-center">
                                                {{
                                                    index +
                                                    1 +
                                                    (users?.current_page - 1) * users?.per_page
                                                }}
                                            </td>

                                            <td class="text-center">
                                                <Link @click.prevent="goDetail(item.id)"
                                                    :href="route('users.detail', item.id)" class="text-decoration-none">
                                                    <i class="fas fa-user"></i> <span
                                                        v-html="highlight(item.name, filters.keyword)" />
                                                </Link>
                                            </td>
                                            <td class="text-center">
                                                {{ item.email }}
                                            </td>
                                            <td class="text-center">
                                                <span :class="[
                                                    'badge text-capitalize px-3 py-2',
                                                    item.is_active == true ? 'bg-success' : 'bg-secondary',
                                                ]">
                                                    {{ formatTextFromSlug(item.is_active == true ? 'Online' : 'Offline')
                                                    }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span :class="[
                                                    'badge text-capitalize px-3 py-2',
                                                    item.status == 'active' ? 'bg-primary' : 'bg-danger',
                                                ]">
                                                    {{ formatTextFromSlug(item.status) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                {{ formatTextFromSlug(item.roles.join(', ')) }}
                                            </td>
                                            <td class="text-center">
                                                {{ item.first_login ?? '-' }}
                                            </td>
                                            <td class="text-center">
                                                {{ item.last_login ?? '-' }}
                                            </td>
                                            <td class="text-center">
                                                <div class="dropdown dropstart">
                                                    <button class="btn btn-secondary bg-gradient" type="button"
                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="fas fa-cog"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <button @click.prevent="edit(item.id)"
                                                                class="dropdown-item fw-semibold d-flex justify-content-between align-items-center">
                                                                Ubah <i class="bi bi-pencil-square text-info fs-5"></i>
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <button @click.prevent="deleted('users.delete', item)"
                                                                class="dropdown-item fw-semibold d-flex justify-content-between align-items-center">
                                                                Hapus <i class="bi bi-trash-fill text-danger fs-5"></i>
                                                            </button>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div
                                class="d-flex flex-wrap justify-content-lg-between align-items-center flex-column flex-lg-row p-3">
                                <div class="mb-2 order-1 order-xl-0">
                                    Menampilkan <strong>{{ props.users?.from ?? 0 }}</strong> sampai
                                    <strong>{{ props.users?.to ?? 0 }}</strong> dari total
                                    <strong>{{ props.users?.total ?? 0 }}</strong> data
                                </div>
                                <pagination size="pagination-sm" :links="props.users?.links" routeName="users"
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
        </template>
    </app-layout>

</template>
<style scoped>
.blur-area {
    transition: all 0.3s ease;
}

.blur-area.is-blurred {
    filter: blur(3px);
    pointer-events: none;
    user-select: none;
    opacity: 0.6;
}

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
