<script setup>
import { computed, reactive, ref, watch } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { debounce } from "lodash";
import Swal from "sweetalert2";
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
// const routes = {
//     edit: "brand.edit",
//     delete: "brand.delete",
// };
// const liveSearch = debounce((e) => {
//     router.get(route("brand"), filters, {
//         preserveScroll: true,
//         replace: true,
//         preserveState: true,
//         only: ["brand", "filters"], // optional: lebih hemat bandwidth jika kamu pakai Inertia partial reload
//     });
// }, 1000);
const header = [
    { label: "No", key: "__index" },
    { label: "Logo", key: "logo" },
    { label: "Brand/Merek", key: "name" },
    { label: "Deskripsi", key: "description" },
];
const highlight = (text, keyword) => {
    if (!keyword || !text) return text;
    const regex = new RegExp(`(${keyword})`, "gi");
    return text.toString().replace(regex, `<mark class="highlight">$1</mark>`);
};

watch(
    () => [
        filters.keyword,
        filters.limit,
        filters.order_by,],
    () => liveSearch()
);

const selectedRow = ref([]);
const isVisible = ref(false);
// function deleteSelected() {
//     if (!selectedRow.value.length) {
//         Swal.fire("Peringatan", "Tidak ada data yang dipilih.", "warning");
//         return;
//     }

//     Swal.fire({
//         title: "Hapus Data Terpilih",
//         text: `Yakin ingin menghapus ${selectedRow.value.length} data terpilih?`,
//         icon: "warning",
//         showCancelButton: true,
//         confirmButtonText: "Ya, Hapus Semua!",
//         cancelButtonText: "Batal",
//     }).then((result) => {
//         if (result.isConfirmed) {
//             // kirim ke route mass delete
//             router.post(route('brand.destroy_all'), { all_id: selectedRow.value }, {
//                 preserveScroll: true,
//                 preserveState: false,
//             });
//         }
//     });
// }

watch(selectedRow, (val) => {
    if (val.length > 0) {
        isVisible.value = true
    } else {
        isVisible.value = false
    }
})
</script>
<template>

    <Head title="Halaman Jabatan" />
    <app-layout>
        <template #content>
            <bread-crumbs icon="fas fa-briefcase" title="Daftar Jabatan" :items="[{ text: 'Daftar Jabatan' }]" />
            <alert :duration="10" :message="message" />
            <div class="row">
                <div class="col-xl-12">
                    <div class="card mb-3 overflow-hidden rounded-4">
                        <div class="row align-items-center p-3 g-2">
                            <div class="col-xl-6 col-12 mb-xl-0 mb-3">
                                <div class="position-relative">
                                    <i class="fas fa-search input-icon-left"></i>
                                    <text-input autofocus class="input-fixed-height" v-model="filters.keyword"
                                        name="keyword" placeholder="Cari merek..." />
                                </div>
                            </div>
                            <div class="col-xl-2 col-4 mb-xl-0 mb-3">
                                <div class="position-relative">
                                    <i class="fas fa-sort input-icon-left"></i>
                                    <select-input :is-valid="false" selectClass="input-fixed-select"
                                        v-model="filters.limit" name="limit" :options="[
                                            { value: 10, label: '10' },
                                            { value: 25, label: '25' },
                                            { value: 50, label: '50' },
                                            { value: 100, label: '100' },
                                        ]" />
                                </div>
                            </div>
                            <div class="col-xl-2 col-4 mb-xl-0 mb-3">
                                <div class="position-relative">
                                    <i class="fas fa-sort input-icon-left"></i>
                                    <select-input :is-valid="false" selectClass="input-fixed-select"
                                        v-model="filters.order_by" name="order_by" :options="[
                                            { value: 'desc', label: 'Terbaru' },
                                            { value: 'asc', label: 'Terlama' },
                                        ]" />
                                </div>
                            </div>
                            <div class="col-xl-2 col-4 mb-xl-0 mb-3 d-flex justify-content-xl-end">
                                <Link :href="route('roles.create')" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Tambah
                                </Link>
                            </div>
                        </div>
                    </div>

                    <button-delete-all text="Hapus" :isVisible="isVisible" :deleted="deleteSelected" />

                    <div class="card mb-4 overflow-hidden rounded-4">
                        <div class="table-responsive">

                        </div>

                    </div>
                </div>
            </div>
        </template>
    </app-layout>
</template>
