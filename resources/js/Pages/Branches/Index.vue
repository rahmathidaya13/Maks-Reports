<script setup>
import { computed, reactive, ref, watch } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { debounce } from "lodash";
import { swalAlert, swalConfirmDelete } from "../../helpers/swalHelpers";
import { highlight } from "@/helpers/highlight";
import { formatText } from "@/helpers/formatText";
import moment from "moment";
moment.locale('id');


const page = usePage();
const message = computed(() => page.props.flash.message || "");
const props = defineProps({
    branch: Object,
    filters: Object,
});

// cek permission
const perm = page.props.auth.user.permissions


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
        only: ["branch", "filters"], // optional: lebih hemat bandwidth jika kamu pakai Inertia partial reload
        onFinish: () => isLoading.value = false
    });
}, 1000);


const header = [
    { label: "No", key: "__index" },
    { label: "Nama Cabang", key: "name" },
    { label: "No Telepon", key: "phone" },
    { label: "Alamat", key: "address" },
    { label: "Status", key: "status" },
    { label: "Dibuat Oleh", key: "created_by" },
    { label: "Dibuat", key: "created_at" },
    { label: "Diperbarui", key: "updated_at" },
    { label: "Aksi", key: "-" },
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
const create = () => {
    loaderActive.value?.show("Memproses...");
    router.get(route("branch.create"), {}, {
        onFinish: () => {
            loaderActive.value?.hide()
        }
    });
}

const edit = (id) => {
    loaderActive.value?.show("Sedang memuat data...");
    router.get(route("branch.edit", id), {}, {
        onFinish: () => loaderActive.value?.hide()
    });
}

const deleted = (nameRoute, data) => {
    swalConfirmDelete({
        title: 'Hapus',
        text: `Kamu ingin menghapus Cabang ${formatText(data.name)} ?`,
        confirmText: 'Ya, Hapus!',
        onConfirm: () => {
            loaderActive.value?.show("Sedang memuat data...");
            router.delete(route(nameRoute, data.branches_id), {
                onFinish: () => loaderActive.value?.hide(),
                preserveScroll: false,
                replace: true
            });
        },
    })
}
// end CRUD OPERATION

// MULTIPLE DELETE
const selectedRow = ref([]);
const isVisible = ref(false);

const isAllSelected = computed(() => {
    const rows = props.branch?.data ?? [];
    return rows.length > 0 && selectedRow.value.length === rows.length;
})

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
            router.post(route('branch.destroy_all'), { all_id: selectedRow.value }, {
                onFinish: () => loaderActive.value?.hide(),
                preserveScroll: true,
                preserveState: false,
            })
        },
    })
}
const isSelected = (id) => {
    return selectedRow.value.includes(id);
}
const toggleAll = (evt) => {
    if (evt.target.checked) {
        selectedRow.value = props.branch?.data.map(r => r.branches_id);
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


</script>
<template>

    <Head title="Halaman Jabatan" />
    <app-layout>
        <template #content>
            <loader-page ref="loaderActive" />
            <bread-crumbs :home="false" icon="fas fa-building" title="Daftar Cabang"
                :items="[{ text: 'Daftar Cabang' }]" />
            <alert :duration="10" :message="message" />
            <div class="row">
                <div class="col-xl-12">

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">

                            <div class="d-flex" v-if="perm.includes('branches.delete')">
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
                                            placeholder="Pencarian....." />
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <div class="input-group">
                                        <select-input select-class="border-dark border-1 border" :is-valid="false"
                                            v-model="filters.limit" name="limit" :options="[
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
                                <div class="col-auto" v-if="perm.includes('branches.create')">
                                    <button type="button" @click.prevent="create" class="btn btn-success bg-gradient">
                                        <i class="fas fa-plus"></i> Buat Baru
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
                                            <th v-if="perm.includes('branches.delete')">
                                                <div class="form-check d-flex justify-content-center">
                                                    <input :disabled="!branch?.data.length" type="checkbox"
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
                                        <tr v-if="!branch?.data.length">
                                            <td :colspan="header.length + 1" class="text-center py-3 text-muted">
                                                Tidak ada data tersedia.
                                            </td>
                                        </tr>

                                        <tr class="align-middle" v-for="(item, index) in branch?.data" :key="index"
                                            :class="{ 'table-info': isSelected(item.branches_id) }">

                                            <td class="text-center" v-if="perm.includes('branches.delete')">
                                                <div class="form-check d-flex justify-content-center">
                                                    <input type="checkbox" class="form-check-input"
                                                        :name="item.branches_id" :id="item.branches_id"
                                                        :value="item.branches_id" v-model="selectedRow" />
                                                </div>
                                            </td>

                                            <td class="text-center">
                                                {{
                                                    index +
                                                    1 +
                                                    (branch?.current_page - 1) * branch?.per_page
                                                }}
                                            </td>
                                            <td class="text-center text-capitalize">
                                                <div v-html="highlight(item[header[1].key], filters.keyword)"></div>
                                            </td>
                                            <td class="text-start phone">
                                                <ul class="list-group">
                                                    <li class="list-group-item" v-for="item in item.branch_phone"
                                                        :key="item.branch_phone_id">
                                                        <div v-html="highlight(item.phone, filters.keyword)">
                                                        </div>
                                                    </li>
                                                </ul>
                                                <div class="text-center" v-if="!item.branch_phone.length">-</div>
                                            </td>
                                            <td class="text-start">
                                                <div v-html="item[header[3].key]"></div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge py-2 px-4" :class="{
                                                    'text-bg-success': item[header[4].key] === 'active',
                                                    'text-bg-danger': item[header[4].key] === 'inactive'
                                                }">
                                                    {{ item[header[4].key] === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                                                </span>

                                            </td>
                                            <td class="text-center text-capitalize">
                                                {{ item.creator?.name ?? '-' }}
                                            </td>
                                            <td class="text-center">{{ daysTranslate(item[header[6].key]) }}</td>
                                            <td class="text-center">{{ daysTranslate(item[header[7].key]) }}</td>
                                            <td class="text-center">
                                                <div class="dropdown dropstart">
                                                    <button class="btn btn-secondary bg-gradient" type="button"
                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="fas fa-cog"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li v-if="perm.includes('branches.edit')">
                                                            <button @click.prevent="edit(item.branches_id)"
                                                                class="dropdown-item fw-semibold d-flex justify-content-between align-items-center">
                                                                Ubah <i class="bi bi-pencil-square text-info fs-5"></i>
                                                            </button>
                                                        </li>
                                                        <li v-if="perm.includes('branches.delete')">
                                                            <button @click.prevent="deleted('branch.deleted', item)"
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
                                    Menampilkan <strong>{{ props.branch.from ?? 0 }}</strong> sampai
                                    <strong>{{ props.branch.to ?? 0 }}</strong> dari total
                                    <strong>{{ props.branch.total ?? 0 }}</strong> data
                                </div>
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
</style>
