<script setup>
import { computed, nextTick, onMounted, ref, watch } from "vue";
import { Head, Link, router, useForm, usePage } from "@inertiajs/vue3";
import moment from "moment";
const props = defineProps({
    dailyReport: Object,
    date: String
})
const form = useForm({
    date: props.dailyReport?.date ?? props.date,
    leads: props.dailyReport?.leads ?? '',
    closing: props.dailyReport?.closing ?? '',
    fu_yesterday: props.dailyReport?.fu_yesterday ?? '',
    fu_yesterday_closing: props.dailyReport?.fu_yesterday_closing ?? '',
    fu_before_yesterday: props.dailyReport?.fu_before_yesterday ?? '',
    fu_before_yesterday_closing: props.dailyReport?.fu_before_yesterday_closing ?? '',
    fu_last_week: props.dailyReport?.fu_last_week ?? '',
    fu_last_week_closing: props.dailyReport?.fu_last_week_closing ?? '',
    engage_old_customer: props.dailyReport?.engage_old_customer ?? '',
    engage_closing: props.dailyReport?.engage_closing ?? '',
});
const isSubmit = () => {
    if (props.dailyReport?.daily_report_id) {
        form.put(route('daily_report.update', props.dailyReport.daily_report_id), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
            },
        })
    } else {
        // Create
        form.post(route('daily_report.store'), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
            }
        });
    }
};
const title = ref("");
const icon = ref("");
const url = ref("")
const inputLeadsRef = ref(null);
onMounted(() => {
    if (props.dailyReport && props.dailyReport?.daily_report_id) {
        title.value = "Ubah Data Laporan Harian"
        icon.value = "fas fa-edit"
        url.value = route('daily_report')
    } else {
        title.value = "Buat Laporan Harian"
        icon.value = "fas fa-plus-square"
        url.value = route('daily_report')
    }
    nextTick(() => {
        if (inputLeadsRef.value?.$el) {
            inputLeadsRef.value.$el.focus()
        } else if (inputLeadsRef.value?.focus) {
            inputLeadsRef.value.focus()
        }
    })
})
const breadcrumbItems = computed(() => {
    if (props.dailyReport && props.dailyReport?.daily_report_id) {
        return [
            { text: "Daftar Laporan Harian", url: route("daily_report") },
            { text: "Buat Laporan Harian", url: route("daily_report.create") },
            { text: title.value }
        ]
    }
    return [
        { text: "Daftar Laporan Harian", url: route("daily_report") },
        { text: title.value }
    ]
})
// override button kembali
const loaderActive = ref(null);
const goBack = () => {
    loaderActive.value?.show("Memproses...");
    router.get(url.value, {}, {
        onFinish: () => loaderActive.value?.hide()
    });
}
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
            <loader-page ref="loaderActive" />

            <bread-crumbs :home="false" :icon="icon" :title="title" :items="breadcrumbItems" />
            <div class="callout callout-success">
                <h5 class="fw-bold"><i class="fas fa-bullhorn me-2"></i>Panduan Pengisian Laporan Leads Harian</h5>
                <ul class="mb-0 ps-3">
                    <li>Isi setiap kolom sesuai aktivitas yang dilakukan pada hari ini.</li>
                    <li>Angka <strong>Leads</strong> dan setiap <strong>Follow Up (FU)</strong> diisi berdasarkan jumlah
                        konsumen yang benar-benar dihubungi.</li>
                    <li>Kolom <strong>Closing</strong> diisi sesuai jumlah konsumen yang berhasil closing dari
                        masing-masing kategori.</li>
                    <li>Pastikan tidak ada kolom yang dibiarkan kosong — isi dengan angka <strong>0</strong> jika tidak
                        ada aktivitas.</li>
                    <li>Periksa kembali semua data sebelum menekan tombol <strong>Simpan</strong>.</li>
                </ul>
            </div>

            <div class="d-flex justify-content-between">
                <Link @click.prevent="goBack" :href="url" class="btn btn-danger mb-3">
                <i class="fas fa-arrow-left"></i>
                Kembali
                </Link>
            </div>
            <div class="row">
                <div class="col-xl-12 col-sm-12 pb-3">
                    <div class="card overflow-hidden rounded-4 shadow-sm">
                        <h5 class="card-header fw-bold text-uppercase p-3 text-bg-dark">
                            <i class="fas fa-clipboard me-1 text-light"></i>
                            Laporan Leads Harian:
                            <span class="text-info">{{ daysOnlyConvert(form.date) }}
                            </span>
                        </h5>
                        <div v-if="form.processing">
                            <loader-horizontal
                                :message="props.dailyReport?.daily_report_id ? 'Sedang memperbarui data' : 'Sedang menyimpan data'" />
                        </div>
                        <div class="card-body" :class="['blur-area', form.processing ? 'is-blurred' : '']">
                            <form-wrapper @submit="isSubmit">
                                <div class="mb-3 p-3 rounded-3 border border-dark text-bg-grey">
                                    <div class="mb-2">
                                        <input-label class="fw-bold" for="leads" value="Leads" />
                                        <input-number ref="inputLeadsRef" placeholder="0" name="leads"
                                            v-model="form.leads" />
                                        <input-error :message="form.errors.leads" />
                                    </div>
                                    <div class="mb-2">
                                        <input-label class="fw-bold" for="closing" value="Closing" />
                                        <input-number placeholder="0" name="closing" v-model="form.closing" />
                                        <input-error :message="form.errors.closing" />
                                    </div>
                                </div>
                                <div class="mb-3 p-3 rounded-3 border border-dark text-bg-grey">
                                    <div class="row row-cols-1 row-cols-xl-3 g-3">

                                        <div class="col-xl-3 col-sm-6 col-md-6 col-12">
                                            <div class="mb-2">
                                                <input-label class="fw-bold" for="fu_yesterday"
                                                    value="FU Konsumen Kemarin" />
                                                <input-number placeholder="0" name="fu_yesterday"
                                                    v-model="form.fu_yesterday" />
                                                <input-error :message="form.errors.fu_yesterday" />
                                            </div>
                                            <div class="mb-2">
                                                <input-label class="fw-bold" for="fu_yesterday_closing"
                                                    value="Closing" />
                                                <input-number placeholder="0" name="fu_yesterday_closing"
                                                    v-model="form.fu_yesterday_closing" />
                                                <input-error :message="form.errors.fu_yesterday_closing" />
                                            </div>
                                        </div>

                                        <div class="col-xl-3 col-sm-6 col-md-6 col-12">
                                            <div class="mb-2">
                                                <input-label class="fw-bold" for="fu_before_yesterday"
                                                    value="FU Konsumen Kemarinnya" />
                                                <input-number placeholder="0" name="fu_before_yesterday"
                                                    v-model="form.fu_before_yesterday" />
                                                <input-error :message="form.errors.fu_before_yesterday" />
                                            </div>
                                            <div class="mb-2">
                                                <input-label class="fw-bold" for="fu_before_yesterday_closing"
                                                    value="Closing" />
                                                <input-number placeholder="0" name="fu_before_yesterday_closing"
                                                    v-model="form.fu_before_yesterday_closing" />
                                                <input-error :message="form.errors.fu_before_yesterday_closing" />
                                            </div>
                                        </div>

                                        <div class="col-xl-3 col-sm-6 col-md-6 col-12">
                                            <div class="mb-2">
                                                <input-label class="fw-bold" for="fu_last_week"
                                                    value="FU Minggu Kemarinnya" />
                                                <input-number placeholder="0" name="fu_last_week"
                                                    v-model="form.fu_last_week" />
                                                <input-error :message="form.errors.fu_last_week" />
                                            </div>
                                            <div class="mb-2">
                                                <input-label class="fw-bold" for="fu_last_week_closing"
                                                    value="Closing" />
                                                <input-number placeholder="0" name="fu_last_week_closing"
                                                    v-model="form.fu_last_week_closing" />
                                                <input-error :message="form.errors.fu_last_week_closing" />
                                            </div>
                                        </div>

                                        <div class="col-xl-3 col-sm-6 col-md-6 col-12">
                                            <div class="mb-2">
                                                <input-label class="fw-bold" for="engage_old_customer"
                                                    value="Engage Pelanggan Lama" />
                                                <input-number placeholder="0" name="engage_old_customer"
                                                    v-model="form.engage_old_customer" />
                                                <input-error :message="form.errors.engage_old_customer" />
                                            </div>
                                            <div class="mb-2">
                                                <input-label class="fw-bold" for="engage_closing" value="Closing" />
                                                <input-number placeholder="0" name="engage_closing"
                                                    v-model="form.engage_closing" />
                                                <input-error :message="form.errors.engage_closing" />
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <div class="d-grid d-xl-flex justify-content-end">
                                    <base-button waiting="Memproses..." :loading="form.processing"
                                        class="rounded-3 bg-gradient px-5 btn-height-2"
                                        :icon="props.dailyReport && props.dailyReport?.daily_report_id ? 'fas fa-edit' : 'fas fa-save'"
                                        :variant="props.dailyReport && props.dailyReport?.daily_report_id ? 'success' : 'primary'"
                                        type="submit"
                                        :name="props.dailyReport && props.dailyReport?.daily_report_id ? 'ubah' : 'simpan'"
                                        :label="props.dailyReport && props.dailyReport?.daily_report_id ? 'Ubah' : 'Simpan'" />
                                </div>
                            </form-wrapper>
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
