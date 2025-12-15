<script setup>
import { computed, reactive, ref, watch } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { debounce } from "lodash";
import { swalAlert, swalConfirmDelete } from "../../helpers/swalHelpers";
import { highlight } from "@/helpers/highlight";
import moment from "moment";
moment.locale('id');


const page = usePage();
const message = computed(() => page.props.flash.message || "");
const props = defineProps({
    jobTitle: Object,
    filters: Object,
});

// cek permission
const perm = page.props.auth.user.permissions;

const filters = reactive({
    keyword: props.filters.keyword ?? '',
    limit: props.filters.limit ?? 10,
    order_by: props.filters.order_by ?? "desc",
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
    { label: "No", key: "__index" },
    { label: "Kode Jabatan", key: "job_title_code" },
    { label: "Nama Jabatan", key: "title" },
    { label: "Jabatan Alias", key: "title_alias" },
    { label: "Deskripsi", key: "description" },
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

const deleted = (nameRoute, data) => {
    swalConfirmDelete({
        title: 'Hapus',
        text: `Kamu ingin menghapus Jabatan ${data.title} ? Tindakan ini tidak dapat kembalikan data yang terhapus!`,
        confirmText: 'Ya, Hapus!',
        onConfirm: () => {
            loaderActive.value?.show("Sedang memuat data...");
            router.delete(route(nameRoute, data.job_title_id), {
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
    const rows = props.jobTitle?.data ?? [];
    return rows.length > 0 && selectedRow.value.length === rows.length;
})

function deleteSelected() {
    if (!selectedRow.value.length) {
        return swalAlert('Peringatan', 'Tidak ada data yang dipilih.', 'warning');
    }
    console.log(selectedRow.value);
    swalConfirmDelete({
        title: 'Hapus Data Terpilih',
        text: `Yakin ingin menghapus ${selectedRow.value.length} data terpilih?`,
        confirmText: 'Ya, Hapus Semua!',
        onConfirm: () => {
            loaderActive.value?.show("Sedang memuat data...");
            router.post(route('job_title.destroy_all'), { all_id: selectedRow.value }, {
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
        selectedRow.value = props.jobTitle?.data.map(r => r.job_title_id);
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
            <bread-crumbs :home="false" icon="fas fa-briefcase" title="Daftar Jabatan"
                :items="[{ text: 'Daftar Jabatan' }]" />
            <alert :duration="10" :message="message" />
            <div class="row">
                <div class="col-xl-12">

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div v-if="perm.includes('job.title.delete')" class="d-flex gap-1">
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
                                <div class="col-auto" v-if="perm.includes('job.title.create')">
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
                                            <th v-if="perm.includes('job.title.delete')">
                                                <div class="form-check d-flex justify-content-center">
                                                    <input :disabled="!jobTitle?.data.length" type="checkbox" class="form-check-input"
                                                        :checked="isAllSelected" @change="toggleAll($event)" />
                                                </div>
                                            </th>
                                            <th v-for="col in header" :key="col.key" class="text-center">
                                                {{ col.label }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="!jobTitle?.data.length">
                                            <td :colspan="header.length + 1" class="text-center py-3 text-muted">
                                                Tidak ada data tersedia.
                                            </td>
                                        </tr>

                                        <tr class="align-middle" v-for="(item, index) in jobTitle?.data" :key="index"
                                            :class="{ 'table-info': isSelected(item.job_title_id) }">

                                            <td class="text-center" v-if="perm.includes('job.title.delete')">
                                                <div class="form-check d-flex justify-content-center">
                                                    <input type="checkbox" class="form-check-input"
                                                        :name="item.job_title_id" :id="item.job_title_id"
                                                        :value="item.job_title_id" v-model="selectedRow" />
                                                </div>
                                            </td>

                                            <td class="text-center">
                                                {{
                                                    index +
                                                    1 +
                                                    (jobTitle?.current_page - 1) * jobTitle?.per_page
                                                }}
                                            </td>
                                            <td class="text-center">
                                                <div v-html="highlight(item[header[1].key], filters.keyword)"></div>
                                            </td>
                                            <td class="text-center">
                                                <div v-html="highlight(item[header[2].key], filters.keyword)"></div>
                                            </td>
                                            <td class="text-center">
                                                <div v-html="highlight(item[header[3].key], filters.keyword)"></div>
                                            </td>
                                            <td class="text-start">
                                                <div v-html="item[header[4].key]"></div>
                                            </td>
                                            <td class="text-center">
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
                                                        <li v-if="perm.includes('job.title.edit')">
                                                            <button @click.prevent="edit(item.job_title_id)"
                                                                class="dropdown-item fw-semibold d-flex justify-content-between align-items-center">
                                                                Ubah <i class="bi bi-pencil-square text-info fs-5"></i>
                                                            </button>
                                                        </li>
                                                        <li v-if="perm.includes('job.title.delete')">
                                                            <button @click.prevent="deleted('job_title.deleted', item)"
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
                                    Menampilkan <strong>{{ props.jobTitle.from ?? 0 }}</strong> sampai
                                    <strong>{{ props.jobTitle.to ?? 0 }}</strong> dari total
                                    <strong>{{ props.jobTitle.total ?? 0 }}</strong> data
                                </div>
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
