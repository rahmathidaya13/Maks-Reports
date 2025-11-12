<script setup>
import { computed, reactive, ref, watch } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { debounce } from "lodash";
import moment from "moment";

moment.locale('id');
const page = usePage();
const message = computed(() => page.props.flash.message || "");
const props = defineProps({
    dailyReport: Object,
    filters: Object,
    can_search: Boolean
});

const filters = reactive({
    keyword: props.filters.keyword ?? '',
    limit: props.filters.limit ?? 10,
    order_by: props.filters.order_by ?? "desc",
    page: props.filters?.page ?? 1,
    start_date: props.filters.start_date ?? '',
    end_date: props.filters.end_date ?? '',
})

const liveSearch = debounce((e) => {
    router.get(route("daily_report"), filters, {
        preserveScroll: true,
        replace: true,
        preserveState: true,
        only: ["dailyReport", "filters"], // optional: lebih hemat bandwidth jika kamu pakai Inertia partial reload
    });
}, 1000);


const header = [
    { label: "No", key: "__index" },
    { label: "Aksi", key: "-" },
];

watch(
    () => [
        filters.keyword,
        filters.limit,
        filters.order_by,
        filters.page,
        filters.start_date,
        filters.end_date
    ],
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
            router.post(route('daily_report.destroy_all'), { all_id: selectedRow.value }, {
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

    <Head title="Halaman User" />
    <app-layout>
        <template #content>
            <bread-crumbs :home="false" icon="fas fa-clipboard" title="Laporan Harian"
                :items="[{ text: 'Laporan Harian' }]" />
            <alert :duration="10" :message="message" />
            <div class="row">
                <div class="col-xl-12 col-sm-12">
                    <div class="card mb-3 overflow-hidden rounded-4 p-1">
                        <div class="row align-items-center p-3 g-2">

                            <div v-if="props.can_search" class="col-xl-4 col-12 mb-0">
                                <input-label class="fw-bold" for="keyword" value="Pencarian :" />
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <text-input :is-valid="false" autofocus v-model="filters.keyword" name="keyword"
                                        placeholder="Pencarian....." />
                                </div>
                            </div>

                            <div :class="[{ 'col-xl-3 col-12 order-xl-2': !props.can_search }]"
                                class="col-xl-2 col-12 mb-xl-0 mb-0">
                                <div class="position-relative">
                                    <input-label class="fw-bold" for="limit" value="Batas :" />
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-sort"></i></span>
                                        <select-input :is-valid="false" v-model="filters.limit" name="limit" :options="[
                                            { value: 10, label: '10' },
                                            { value: 25, label: '25' },
                                            { value: 50, label: '50' },
                                            { value: 100, label: '100' },
                                        ]" />
                                    </div>
                                </div>

                            </div>
                            <div :class="[{ 'col-xl-3 col-12 order-xl-3': !props.can_search }]"
                                class="col-xl-2 col-12 mb-xl-0 mb-0 ">
                                <div class="position-relative">
                                    <input-label class="fw-bold" for="order_by" value="Urutkan :" />
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-filter"></i></span>
                                        <select-input :is-valid="false" v-model="filters.order_by" name="order_by"
                                            :options="[
                                                { value: 'desc', label: 'Terbaru' },
                                                { value: 'asc', label: 'Terlama' },
                                            ]" />
                                    </div>
                                </div>
                            </div>

                            <div :class="[{ 'col-xl-3 col-12 order-xl-0': !props.can_search }]"
                                class="col-xl-2 col-12 mb-xl-0 mb-0 ">
                                <div class="position-relative">
                                    <input-label class="fw-bold" for="start_date" value="Tanggal Awal :" />
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                        <text-input type="date" :is-valid="false" v-model="filters.start_date"
                                            name="start_date" />
                                    </div>
                                </div>
                            </div>
                            <div :class="[{ 'col-xl-3 col-12 order-xl-1': !props.can_search }]"
                                class="col-xl-2 col-12 mb-xl-0 mb-0">
                                <div class="position-relative">
                                    <input-label class="fw-bold" for="end_date" value="Tanggal Akhir :" />
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                        <text-input type="date" :is-valid="false" v-model="filters.end_date"
                                            name="end_date" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3 d-flex justify-content-end flex-wrap gap-2">
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-download"></i> Unduh Laporan
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item fw-semibold d-flex justify-content-between align-items-center"
                                        href="#">PDF <i class="fas fa-file-pdf text-danger"></i>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item fw-semibold d-flex justify-content-between align-items-center"
                                        href="#">Excel
                                        <i class="fas fa-file-excel text-success"></i>
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="position-relative">
                            <Link :href="route('daily_report.create')" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Buat Laporan
                            </Link>
                        </div>
                    </div>
                    <button-delete-all text="Hapus" :isVisible="isVisible" :deleted="deleteSelected" />

                    <div class="card mb-4 overflow-hidden rounded-4">
                        <div class="table-responsive">
                            <base-table @update:selected="selectedRow = $event"
                                :attributes="{ id: 'daily_report_id', name: '' }" :data="props.dailyReport"
                                :headers="header">


                            </base-table>
                        </div>
                        <div
                            class="d-flex flex-wrap justify-content-lg-between align-items-center flex-column flex-lg-row p-3">
                            <div class="mb-2 order-1 order-xl-0">
                                Menampilkan <strong>{{ props.dailyReport?.from ?? 0 }}</strong> sampai
                                <strong>{{ props.dailyReport?.to ?? 0 }}</strong> dari total
                                <strong>{{ props.dailyReport?.total ?? 0 }}</strong> data
                            </div>
                            <pagination :links="props.dailyReport?.links" :keyword="filters.keyword"
                                routeName="dailyReport" :additionalQuery="{
                                    order_by: filters.order_by,
                                    limit: filters.limit,
                                    keyword: filters.keyword,
                                    start_date: filters.start_date,
                                    end_date: filters.end_date
                                }" />
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </app-layout>
</template>
