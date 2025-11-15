<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { debounce } from "lodash";
import moment from "moment";
import { highlight } from "@/helpers/highlight";
import { swalConfirmDelete } from "@/helpers/swalHelpers";
import { formatTextFromSlug } from "@/helpers/formatTextFromSlug";
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
    { label: "Permission", key: "permissions" },
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
        case /create/i.test(permName):
            return 'bg-success'
        case /edit|update/i.test(permName):
            return 'bg-warning text-dark'
        case /delete|remove/i.test(permName):
            return 'bg-danger'
        case /view|show/i.test(permName):
            return 'bg-info text-dark'
        case /manage|access|assign/i.test(permName):
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
</script>
<template>

    <Head title="Halaman Role" />
    <app-layout>
        <template #content>
            <bread-crumbs :home="false" icon="fas fa-briefcase" title="Daftar Role"
                :items="[{ text: 'Daftar Role' }]" />
            <alert :duration="10" :message="message" />
            <div class="row">
                <div class="col-xl-12">
                    <div class="card mb-3 overflow-hidden rounded-4 p-1">
                        <div class="row align-items-center p-3 g-2">
                            <div class="col-xl-7 col-12 mb-0">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <text-input :is-valid="false" autofocus v-model="filters.keyword" name="keyword"
                                        placeholder="Pencarian....." />
                                </div>
                            </div>
                            <div class="col-xl-3 col-12 mb-xl-0 mb-0 d-flex gap-2">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-sort"></i></span>
                                    <select-input :is-valid="false" v-model="filters.limit" name="limit" :options="[
                                        { value: 10, label: '10' },
                                        { value: 25, label: '25' },
                                        { value: 50, label: '50' },
                                        { value: 100, label: '100' },
                                    ]" />
                                </div>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-sort"></i></span>
                                    <select-input :is-valid="false" v-model="filters.order_by" name="order_by" :options="[
                                        { value: 'desc', label: 'Terbaru' },
                                        { value: 'asc', label: 'Terlama' },
                                    ]" />
                                </div>
                            </div>
                            <div class="col-xl-2 col-12 mb-xl-0 mb-0 d-flex">
                                <Link :href="route('roles.create')" class="btn btn-primary bg-gradient px-5">
                                <i class="fas fa-plus" style="font-size:18px"></i>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <button-delete-all text="Hapus" :isVisible="isVisible" :deleted="deleteSelected" />

                    <div class="card mb-4 overflow-hidden rounded-4">
                        <div class="table-responsive">
                            <base-table @update:selected="selectedRow = $event" :attributes="{ id: 'id', name: 'name' }"
                                :data="props.roles" :headers="header">
                                <template #cell="{ row, keyName }">
                                    <template v-if="keyName === 'name'">
                                        <div class="fw-bold">
                                            {{ formatTextFromSlug(highlight(row.name)) }}
                                        </div>
                                    </template>
                                    <template v-if="keyName === 'permissions'">
                                        <ul class="list-unstyled mb-0 d-flex flex-wrap gap-2">
                                            <li v-for="perm in row.permissions" :key="perm.id">
                                                <span :class="[
                                                    'badge text-capitalize px-3 py-2',
                                                    getBadgeClass(perm.name)
                                                ]">
                                                    {{ formatTextFromSlug(perm.name) }}
                                                </span>
                                            </li>
                                        </ul>
                                    </template>
                                    <template v-if="keyName === '-'">
                                        <div class="d-flex gap-1 align-items-center justify-content-center">
                                            <Link :href="route('roles.edit', row.id)"
                                                class="btn btn-sm btn-info text-white px-3"><i class="fas fa-edit"></i>
                                            </Link>
                                            <button class="btn btn-sm btn-outline-danger px-3"
                                                @click="deleted('roles.delete', row)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </template>
                                    <template v-if="keyName === 'created_at'">
                                        {{ moment(row.created_at).format('LLL') }}
                                    </template>
                                    <template v-if="keyName === 'updated_at'">
                                        {{ moment(row.updated_at).format('LLL') }}
                                    </template>
                                </template>

                            </base-table>
                        </div>
                        <div
                            class="d-flex flex-wrap justify-content-lg-between align-items-center flex-column flex-lg-row p-3">
                            <div class="mb-2 order-1 order-xl-0">
                                Menampilkan <strong>{{ props.roles?.from ?? 0 }}</strong> sampai
                                <strong>{{ props.roles?.to ?? 0 }}</strong> dari total
                                <strong>{{ props.roles?.total ?? 0 }}</strong> data
                            </div>
                            <pagination :links="props.roles?.links" :keyword="filters.keyword" routeName="roles"
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
