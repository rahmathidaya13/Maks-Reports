<script setup>
import { computed, reactive, ref, watch } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { debounce } from "lodash";
import moment from "moment";
import { swalAlert, swalConfirmDelete } from "@/helpers/swalHelpers";

moment.locale('id');

const page = usePage();
const message = computed(() => page.props.flash.message || "");
const props = defineProps({
    dailyReport: Object,
    filters: Object,
});

const filters = reactive({
    limit: props.filters.limit ?? 5,
    order_by: props.filters.order_by ?? "desc",
    page: props.filters?.page ?? 1,
    start_date: props.filters.start_date ?? '',
    end_date: props.filters.end_date ?? '',
})




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
                replace: true
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
            router.delete(route(nameRoute, data.daily_report_id), { preserveScroll: false, replace: true });
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


function daysOnlyConvert(dayValue) {
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
    const dateFormat = moment(dayValue).format('DD/MM/YYYY');
    return dayConvert[dayName] + ", " + dateFormat ?? dayName;
}

// trigger button bila tanggal diisi
const isDisableBtnDatePicker = computed(() => {
    return !(filters.start_date && filters.end_date);
})
const isLoading = ref(false)
const searchByDate = debounce((e) => {
    router.get(route("daily_report"), filters, {
        preserveScroll: true,
        replace: true,
        preserveState: true,
        only: ["dailyReport", "filters"], // optional: lebih hemat bandwidth jika kamu pakai Inertia partial reload
        onFinish: () => {
            // Selesai apapun hasilnya → loader hilang
            isLoading.value = false
        }
    });
}, 1000);

const applyDateRange = (e) => {
    isLoading.value = true
    console.log(e);
    if (filters.start_date && filters.end_date) {
        filters.page = 1;
        searchByDate()
        return
    }
    filters.page = 1;
    searchByDate()
}

// Watcher untuk tanggal saja bila tanggal direset dari field
watch(
    [() => filters.start_date, () => filters.end_date],
    () => {
        if (!filters.start_date && !filters.end_date) {
            filters.page = 1;
            isLoading.value = true
            searchByDate();
        }
    }, {
    deep: true
}
);
// Watcher untuk limit dan order_by saja
watch([() => filters.limit, () => filters.order_by], () => {
    filters.page = 1;
    searchByDate()
}, {
    deep: true
})


</script>
<template>

    <Head title="Halaman Laporan Harian" />
    <app-layout>
        <template #content>
            <bread-crumbs :home="false" icon="fas fa-clipboard" title="Laporan Harian"
                :items="[{ text: 'Laporan Harian' }]" />
            <alert :duration="10" :message="message" />
            <div class="row">
                <div class="col-xl-12 col-sm-12">
                    <div class="card mb-4 overflow-hidden rounded-3 p-1 bg-light">
                        <div class="row align-items-center p-2 g-2 pb-3">
                            <div class="col-xl-4 col-sm-6 col-md-3">
                                <input-label class="fw-bold mb-1" for="start_date" value="Tanggal Awal" />
                                <div class="input-group">
                                    <text-input name="start_date" v-model="filters.start_date" type="date"
                                        :is-valid="false" />
                                </div>
                            </div>
                            <div class="col-xl-4 col-sm-6 col-md-3">
                                <input-label class="fw-bold mb-1" for="end_date" value="Tanggal Akhir" />
                                <div class="input-group">
                                    <text-input name="end_date" v-model="filters.end_date" type="date"
                                        :is-valid="false" />
                                    <base-button :disabled="isDisableBtnDatePicker" @click="applyDateRange"
                                        class="bg-gradient" :variant="isDisableBtnDatePicker ? 'secondary' : 'primary'"
                                        name="set" label="Atur" />
                                </div>
                            </div>
                            <div class="col-xl-2 col-sm-6 col-md-3">
                                <input-label class="fw-bold mb-1" for="limit" value="Batas" />
                                <div class="input-group">
                                    <select-input :is-valid="false" v-model="filters.limit" name="limit" :options="[
                                        { value: 5, label: '5' },
                                        { value: 10, label: '10' },
                                        { value: 25, label: '25' },
                                        { value: 50, label: '50' },
                                        { value: 100, label: '100' },
                                    ]" />
                                </div>
                            </div>
                            <div class="col-xl-2 col-sm-6 col-md-3">
                                <input-label class="fw-bold mb-1" for="order_by" value="Urutkan" />
                                <div class="input-group">
                                    <select-input :is-valid="false" v-model="filters.order_by" name="order_by" :options="[
                                        { value: 'desc', label: 'Terbaru' },
                                        { value: 'asc', label: 'Terlama' },
                                    ]" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-2 d-flex justify-content-between flex-wrap gap-2 align-items-center">
                        <button-delete-all text="Hapus" :isVisible="isVisible" :deleted="deleteSelected" />
                        <div class="d-inline-flex ms-auto gap-1">
                            <drop-down @download="handleDownload" />
                            <div class="position-relative">
                                <Link :href="route('daily_report.create')" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Buat Laporan
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4 overflow-hidden rounded-4 shadow-sm" :class="{ 'h-100': isLoading }">
                        <div v-if="isLoading">
                            <loader-horizontal message="Sedang mempersiapkan data....." />
                        </div>
                        <div class="table-responsive" v-else>
                            <base-table @update:selected="selectedRow = $event"
                                :attributes="{ id: 'daily_report_id', name: '' }" :data="props.dailyReport"
                                :headers="header">
                                <template #cell="{ row, keyName }">
                                    <template v-if="keyName === 'date'">
                                        {{ daysOnlyConvert(row.date) }}
                                    </template>

                                    <template v-if="keyName === 'leads'">
                                        <div class="d-flex flex-column text-start">
                                            <span>Jumlah Leads: <b>{{ row.leads ?? 0 }}</b> </span>
                                            <span class="line-table"></span>
                                            <span>Closing Leads: <b>{{ row.closing ?? 0 }}</b></span>
                                        </div>
                                    </template>


                                    <template v-if="keyName === 'fu_yesterday'">
                                        <div class="d-flex flex-column text-start">
                                            <span>Konsumen Kemarin: <b>{{ row.fu_yesterday ?? 0 }}</b>
                                            </span>
                                            <span class="line-table"></span>
                                            <span>Closing: <b>{{ row.fu_yesterday_closing ?? 0 }}</b></span>
                                        </div>
                                    </template>
                                    <template v-if="keyName === 'fu_before_yesterday'">
                                        <div class="d-flex flex-column text-start">
                                            <span>Konsumen Kemarinnya: <b>{{ row.fu_before_yesterday ?? 0 }}</b>
                                            </span>
                                            <span class="line-table"></span>
                                            <span>Closing: <b>{{ row.fu_before_yesterday_closing ?? 0 }}</b></span>
                                        </div>
                                    </template>
                                    <template v-if="keyName === 'fu_last_week'">
                                        <div class="d-flex flex-column text-start">
                                            <span>Konsumen Minggu Kemarinnya: <b>{{ row.fu_last_week ?? 0 }}</b>
                                            </span>
                                            <span class="line-table"></span>
                                            <span>Closing: <b>{{ row.fu_last_week_closing ?? 0 }}</b></span>
                                        </div>
                                    </template>
                                    <template v-if="keyName === 'engage_old_customer'">
                                        <div class="d-flex flex-column text-start">
                                            <span>Engage Konsumen Lama: <b>{{ row.engage_old_customer ?? 0 }}</b>
                                            </span>
                                            <span class="line-table"></span>
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
                                                    <button
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
                        <div v-if="!isLoading"
                            class="d-flex flex-wrap justify-content-lg-between align-items-center flex-column flex-lg-row p-3">
                            <div class="mb-2 order-1 order-xl-0">
                                Menampilkan <strong>{{ props.dailyReport?.from ?? 0 }}</strong> sampai
                                <strong>{{ props.dailyReport?.to ?? 0 }}</strong> dari total
                                <strong>{{ props.dailyReport?.total ?? 0 }}</strong> data
                            </div>
                            <pagination :links="props.dailyReport?.links" routeName="daily_report" :additionalQuery="{
                                order_by: filters.order_by,
                                limit: filters.limit,
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
.line-table {
    height: 1px;
    width: 100%;
    background: rgba(0, 0, 0, 0.205);
    margin: 10px 0;
}

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
