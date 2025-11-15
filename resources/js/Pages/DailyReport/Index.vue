<script setup>
import { computed, reactive, ref, watch } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { debounce } from "lodash";
import moment from "moment";
import { swalAlert, swalConfirmDelete } from "../../helpers/swalHelpers";
import { formatTextFromSlug } from "@/helpers/formatTextFromSlug";

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
    { label: "Tanggal", key: "date" },
    { label: "Leads", key: "leads" },
    { label: "FU Kemarin", key: "fu_yesterday" },
    { label: "FU Kemarinnya", key: "fu_before_yesterday" },
    { label: "FU Minggu Kemarinnya", key: "fu_last_week" },
    { label: "Engage Konsumen Lama", key: "engage_old_customer" },
    { label: "Catatan", key: "notes" },
    { label: "", key: "-" },
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
        text: `Kamu yakin ingin menghapus laporan harian ini yang dibuat pada ${moment(data.date).format('LL')}?`,
        confirmText: 'Ya, Hapus!',
        onConfirm: () => {
            router.delete(route(nameRoute, data.daily_report_id), { preserveScroll: true, replace: true });
        },
    })
}



const handleDownload = (type) => {
    if (type === "pdf") {
        router.get(route("users", { format: "pdf" }));
    } else if (type === "excel") {
        router.get(route("users", { format: "excel" }));
    }
};

const safeNotes = computed(() => {
    const emptyQuillTags = /^(<p><br><\/p>|<p><\/p>|<br>)$/i;
    // Periksa apakah row.notes null atau cocok dengan pola jejak kosong
    if (!props.dailyReport.notes || !props.dailyReport.notes || emptyQuillTags.test(props.dailyReport.notes.trim())) {
        return null; // Kembalikan null jika catatan kosong atau hanya jejak
    }
    // Jika tidak, kembalikan catatan asli
    return props.dailyReport.notes;
})


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

                            <div v-if="props.can_search" class="col-xl-4 col-12 mb-0 order-last order-xl-0">
                                <input-label class="fw-bold d-none d-xl-block" for="keyword" value="Pencarian :" />
                                <div class="input-group mt-2 mt-xl-0">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <text-input :is-valid="false" autofocus v-model="filters.keyword" name="keyword"
                                        placeholder="Pencarian....." />
                                </div>
                            </div>

                            <div :class="[{ 'col-xl-3 col-12 order-xl-2': !props.can_search }]"
                                class="col-xl-2 col-6 mb-xl-0 mb-0">
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
                                class="col-xl-2 col-6 mb-xl-0 mb-0 ">
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
                                class="col-xl-2 col-6 mb-xl-0 mb-0 ">
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
                                class="col-xl-2 col-6 mb-xl-0 mb-0">
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
                        <drop-down @download="handleDownload" />


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
                                <template #cell="{ row, keyName }">
                                    <template v-if="keyName === 'date'">
                                        {{ moment(row.date).format('L') }}
                                    </template>
                                    <template v-if="keyName === 'leads'">
                                        <div class="d-flex flex-column gap-1 text-start">
                                            <span>Jumlah Leads: <b>{{ row.leads ?? 0 }}</b> </span>
                                            <span>Closing Leads: <b>{{ row.closing_leads ?? 0 }}</b></span>
                                        </div>
                                    </template>
                                    <template v-if="keyName === 'fu_yesterday'">
                                        <div class="d-flex flex-column text-start">
                                            <span>FU Konsumen Kemarin: <b>{{ row.fu_yesterday ?? 0 }}</b>
                                            </span>
                                            <span>Closing: <b>{{ row.fu_yesterday_closing ?? 0 }}</b></span>
                                        </div>
                                    </template>
                                    <template v-if="keyName === 'fu_before_yesterday'">
                                        <div class="d-flex flex-column text-start">
                                            <span>FU Konsumen Kemarinnya: <b>{{ row.fu_before_yesterday ?? 0 }}</b>
                                            </span>
                                            <span>Closing: <b>{{ row.fu_before_yesterday_closing ?? 0 }}</b></span>
                                        </div>
                                    </template>
                                    <template v-if="keyName === 'fu_last_week'">
                                        <div class="d-flex flex-column text-start">
                                            <span>FU Konsumen Minggu Kemarinnya: <b>{{ row.fu_last_week ?? 0 }}</b>
                                            </span>
                                            <span>Closing: <b>{{ row.fu_last_week_closing ?? 0 }}</b></span>
                                        </div>
                                    </template>
                                    <template v-if="keyName === 'engage_old_customer'">
                                        <div class="d-flex flex-column text-start">
                                            <span>Engage Konsumen Lama: <b>{{ row.engage_old_customer ?? 0 }}</b>
                                            </span>
                                            <span>Closing: <b>{{ row.engage_closing ?? 0 }}</b></span>
                                        </div>
                                    </template>
                                    <template v-if="keyName === 'notes'">
                                        <div v-if="row.notes !== null && row.notes.trim() !== '<p><br></p>'"
                                            class="notes" v-html="row.notes">
                                        </div>

                                        <div v-else class="notes text-center">{{ 'Tidak ada catatan' }}</div>
                                    </template>

                                    <template v-if="keyName === '-'">
                                        <div class="dropdown dropstart">
                                            <button class="btn btn-secondary" type="button" data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                                <i class="fas fa-cog"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <Link :href="route('daily_report.edit', row.daily_report_id)"
                                                        class="dropdown-item fw-semibold d-flex justify-content-between align-items-center">
                                                    Ubah <i class="fas fa-edit text-info"></i>
                                                    </Link>
                                                </li>
                                                <li>
                                                    <button @click="deleted('daily_report.deleted', row)"
                                                        class="dropdown-item fw-semibold d-flex justify-content-between align-items-center">
                                                        Hapus <i class="fas fa-recycle text-danger"></i>
                                                    </button>
                                                </li>
                                                <li>
                                                    <button @click="deleted('daily_report.deleted', row)"
                                                        class="dropdown-item fw-semibold d-flex justify-content-between align-items-center">
                                                        Bagikan <i class="fas fa-share-alt text-primary"></i>
                                                    </button>
                                                </li>

                                            </ul>
                                        </div>
                                    </template>

                                </template>

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
<style>
.notes {
    width: 450px;
    max-width: 450px;
    /* Sesuaikan nilai ini sesuai kebutuhan Anda */
    text-align: left;
    /* 2. Pastikan konten yang panjang dipaksa untuk melipat */
    white-space: normal;
    /* 3. Gunakan properti untuk memecah kata panjang */
    overflow-wrap: break-word;
    word-break: break-word;
    /* Tambahan: Opsional jika ingin menghilangkan scrollbar horizontal */
    overflow-x: hidden;
    vertical-align: top;
    max-height: 300px;
}
</style>
