<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { Head, Link, router, useForm, usePage } from "@inertiajs/vue3";
import moment from "moment";
const props = defineProps({
    storyReport: Object,
    date: String
})
const form = useForm({
    report_date: props.storyReport?.report_date ?? props.date,
    report_time: props.storyReport?.report_time ?? '',
    count_status: props.storyReport?.count_status ?? '',
    description: props.storyReport?.description ?? '',
});
const isSubmit = () => {
    if (props.storyReport?.story_status_id) {
        form.put(route('story_report.update', props.storyReport.story_status_id), {
            onSuccess: () => {
                form.reset();
            },
        })
    } else {
        // Create
        form.post(route('story_report.store'), {
            onSuccess: () => {
                form.reset();
            }
        });
    }
};
const title = ref("");
const icon = ref("");
const url = ref("")
onMounted(() => {
    if (props.storyReport && props.storyReport?.story_status_id) {
        title.value = "Ubah Laporan Update Status"
        icon.value = "fas fa-edit"
        url.value = route('story_report')
    } else {
        title.value = "Buat Laporan Update Status"
        icon.value = "fas fa-plus-square"
        url.value = route('story_report')
    }
})
const breadcrumbItems = computed(() => {
    if (props.storyReport && props.storyReport?.story_status_id) {
        return [
            { text: "Daftar Laporan Update Status", url: route("story_report") },
            { text: "Buat Laporan Update Status", url: route("story_report.create") },
            { text: title.value }
        ]
    }
    return [
        { text: "Daftar Laporan Update Status", url: route("story_report") },
        { text: title.value }
    ]
})
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
    const dateFormat = moment(dayValue).format('DD-MM-YYYY');
    return dayConvert[dayName] + ", " + dateFormat ?? dayName;
}
</script>
<template>

    <Head :title="title" />
    <app-layout>
        <template #content>
            <bread-crumbs :icon="icon" :title="title" :items="breadcrumbItems" />

            <!-- <div class="callout callout-success">
                <h5 class="fw-bold"><i class="fas fa-bullhorn me-2"></i>Panduan Pengisian Laporan Leads Harian</h5>
                <ul class="mb-0 ps-3">
                    <li>Isi setiap kolom sesuai aktivitas yang dilakukan pada hari ini.</li>
                    <li>Angka <strong>Leads</strong> dan setiap <strong>Follow Up (FU)</strong> diisi berdasarkan jumlah
                        konsumen yang benar-benar dihubungi.</li>
                    <li>Kolom <strong>Closing</strong> diisi sesuai jumlah konsumen yang berhasil closing dari
                        masing-masing kategori.</li>
                    <li>Pastikan tidak ada kolom yang dibiarkan kosong — isi dengan angka <strong>0</strong> jika tidak
                        ada aktivitas.</li>
                    <li>Gunakan kolom <strong>Catatan</strong> untuk menuliskan kendala, progres penting, atau update
                        konsumen tertentu.</li>
                    <li>Periksa kembali semua data sebelum menekan tombol <strong>Simpan</strong>.</li>
                </ul>
            </div> -->


            <div class="d-flex justify-content-between">
                <Link :href="url" class="btn btn-danger mb-3">
                <i class="fas fa-arrow-left"></i>
                Kembali
                </Link>
            </div>
            <div class="row">
                <div class="col-xl-12 col-sm-12 pb-3">
                    <div class="card overflow-hidden rounded-4">
                        <h5 class="card-header fw-bold text-uppercase p-3 text-bg-dark">
                            <i class="fas fa-clipboard me-1 text-light"></i>
                            Form Laporan Update Status:
                            <span class="text-info">{{ daysOnlyConvert(form.report_date) }}
                            </span>
                        </h5>
                        <div :class="{ 'd-flex py-5 mt-5': form.processing }" v-if="form.processing">
                            <loader-horizontal
                                :message="props.storyReport?.story_status_id ? 'Sedang memperbarui data.....' : 'Sedang memproses data.....'" />
                        </div>
                        <div class="card-body" v-else>
                            <form-wrapper @submit="isSubmit">
                                <div class="mb-3 p-3 rounded-3 border" :class="{
                                    'border-danger': form.errors.report_time || form.errors.count_status,
                                    'border-dark': !form.errors.report_time && !form.errors.count_status
                                }">
                                    <div class="row">
                                        <div class="col-xl-6 col-sm-6 col-md-6 col-12">
                                            <div class="mb-2">
                                                <input-label class="fw-bold" for="report_time" value="Jam" />
                                                <text-input tabindex="1" name="report_time" type="time"
                                                    v-model="form.report_time" />
                                                <input-error :message="form.errors.report_time" />
                                            </div>
                                        </div>
                                        <div class="col-xl-6 col-sm-6 col-md-6 col-12">
                                            <div class="mb-2">
                                                <input-label class="fw-bold" for="count_status" value="Jumlah" />
                                                <input-number tabindex="2" placeholder="0" name="count_status"
                                                    type="text" v-model="form.count_status" />
                                                <input-error :message="form.errors.count_status" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 mb-3 p-3 rounded-3 border border-dark">
                                    <div class="mb-2">
                                        <quill-text placeholder="Opsional: Tulis catatan disini jika ada" height="300px"
                                            name="notes" v-model="form.description" />
                                        <input-error :message="form.errors.description" />
                                    </div>
                                </div>

                                <div class="d-grid d-xl-flex">
                                    <base-button waiting="Memproses..." :loading="form.processing"
                                        class="rounded-3 bg-gradient px-5 btn-height-2"
                                        :icon="props.storyReport && props.storyReport?.story_status_id ? 'fas fa-edit' : 'fas fa-save'"
                                        :variant="props.storyReport && props.storyReport?.story_status_id ? 'success' : 'primary'"
                                        type="submit"
                                        :name="props.storyReport && props.storyReport?.story_status_id ? 'ubah' : 'simpan'"
                                        :label="props.storyReport && props.storyReport?.story_status_id ? 'Ubah' : 'Simpan'" />
                                </div>
                            </form-wrapper>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </app-layout>

</template>
