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
    jobTitle: Object,
    filters: Object,
});
const filters = reactive({
    keyword: props.filters.keyword ?? '',
    limit: props.filters.limit ?? 10,
    order_by: props.filters.order_by ?? "desc",
    page: props.filters?.page ?? 1,
})
const routes = {
    edit: "job_title.edit",
    delete: "job_title.deleted",
};
const liveSearch = debounce((e) => {
    router.get(route("job_title"), filters, {
        preserveScroll: true,
        replace: true,
        preserveState: true,
        only: ["jobTitle", "filters"], // optional: lebih hemat bandwidth jika kamu pakai Inertia partial reload
    });
}, 1000);
const header = [
    { label: "No", key: "__index" },
    { label: "Kode Jabatan", key: "job_title_code" },
    { label: "Nama Jabatan", key: "title" },
    { label: "Jabatan Alias", key: "title_alias" },
    { label: "Deskripsi", key: "description" },
    { label: "Dibuat", key: "created_at" },
    { label: "Diperbarui", key: "updated_at" },
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
            router.post(route('job_title.destroy_all'), { all_id: selectedRow.value }, {
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


</script>
<template>

    <Head title="Halaman Jabatan" />
    <app-layout>
        <template #content>
            <bread-crumbs :home="false" icon="fas fa-briefcase" title="Daftar Jabatan"
                :items="[{ text: 'Daftar Jabatan' }]" />
            <alert :duration="10" :message="message" />
            <div class="row">
                <div class="col-xl-12">
                    <div class="card mb-3 overflow-hidden rounded-3 p-1">
                        <div class="row align-items-center p-3 g-2">
                            <div class="col-xl-6 col-12 mb-0">
                                <div class="input-group">
                                    <!-- <span class="input-group-text"><i class="fas fa-search"></i></span> -->
                                    <text-input :is-valid="false" autofocus v-model="filters.keyword" name="keyword"
                                        placeholder="Pencarian....." />
                                </div>
                            </div>
                            <div class="col-xl-4 col-12 mb-xl-0 mb-0 d-flex gap-2">
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
                                    <span class="input-group-text"><i class="fas fa-filter"></i></span>
                                    <select-input :is-valid="false" v-model="filters.order_by" name="order_by" :options="[
                                        { value: 'desc', label: 'Terbaru' },
                                        { value: 'asc', label: 'Terlama' },
                                    ]" />
                                </div>
                            </div>
                            <div class="col-xl-2 mb-xl-0 mb-0 d-flex">
                                <Link :href="route('job_title.create')" class="btn btn-success">
                                <i class="fas fa-plus"></i> Buat Baru
                                </Link>
                            </div>
                        </div>
                    </div>

                    <button-delete-all text="Hapus" :isVisible="isVisible" :deleted="deleteSelected" />

                    <div class="card mb-4 overflow-hidden rounded-4">
                        <div class="table-responsive">
                            <base-table @update:selected="selectedRow = $event" :routes="routes"
                                :attributes="{ id: 'job_title_id', name: 'title' }" :data="props.jobTitle"
                                :headers="header">
                                <template #cell="{ row, keyName }">
                                    <template v-if="keyName === 'job_title_code'">
                                        <span v-html="highlight(row.job_title_code, filters.keyword)" />
                                    </template>
                                    <template v-if="keyName === 'title'">
                                        <span v-html="highlight(formatText(row.title), filters.keyword)" />
                                    </template>
                                    <template v-if="keyName === 'title_alias'">
                                        <span
                                            v-html="highlight(formatText(row.title_alias, 'uppercase'), filters.keyword)" />
                                    </template>
                                    <template v-if="keyName === 'description'">
                                        <div class="text-start"
                                            v-html="row.description || 'Produk tidak memiliki deskripsi'">
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
                                Menampilkan <strong>{{ props.jobTitle.from ?? 0 }}</strong> sampai
                                <strong>{{ props.jobTitle.to ?? 0 }}</strong> dari total
                                <strong>{{ props.jobTitle.total ?? 0 }}</strong> data
                            </div>
                            <pagination :links="props.jobTitle?.links" :keyword="filters.keyword" routeName="job_title"
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
