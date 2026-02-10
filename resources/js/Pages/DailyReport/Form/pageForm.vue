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
const isEditMode = computed(() => !!props.dailyReport?.daily_report_id);
const isSubmit = () => {
    const method = isEditMode.value ? 'put' : 'post';
    const url = isEditMode.value
        ? route('daily_report.update', props.dailyReport.daily_report_id)
        : route('daily_report.store');

    form[method](url, {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    })
};
const pageMeta = computed(() => {
    if (isEditMode.value) {
        return {
            title: "Ubah Data Laporan Harian",
            url: route("daily_report"),
            icon: "fas fa-edit"
        }
    }
    return {
        title: "Buat Laporan Harian",
        url: route("daily_report"),
        icon: "fas fa-plus-square"
    }
})


const breadcrumbItems = computed(() => {
    const items = [{ text: "Daftar Laporan Harian", url: route("daily_report") }];
    items.push({
        text: pageMeta.value.title,
        url: null,
    })
    return items;
})
// override button kembali
const loaderActive = ref(null);
const goBack = () => {
    loaderActive.value?.show("Memproses...");
    router.get(pageMeta.value.url, {}, {
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

// autofocus input
const inputLeadsRef = ref(null);
onMounted(() => {
    inputLeadsRef.value.focus();
})
</script>
<template>

    <Head :title="pageMeta.title" />
    <app-layout>
        <template #content>
            <loader-page ref="loaderActive" />

            <bread-crumbs :home="false" :icon="pageMeta.icon" :title="pageMeta.title" :items="breadcrumbItems" />

            <div class="row pb-3">
                <div class="col-12">

                    <div class="d-block d-xl-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="fw-bold text-dark mb-1">Input Laporan</h4>
                            <p class="text-muted small mb-0">Isi data aktivitas harian Anda dengan lengkap.</p>
                        </div>
                        <Link @click.prevent="goBack" :href="pageMeta.url" class="btn btn-danger border shadow-sm">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </Link>
                    </div>

                    <div class="alert custom-alert-info border-0 rounded-3 shadow-sm mb-4 d-flex align-items-start"
                        role="alert">
                        <i class="fas fa-info-circle fs-4 me-3 mt-1 text-primary"></i>
                        <div>
                            <h6 class="fw-bold text-primary mb-1">Panduan Pengisian</h6>
                            <ul class="mb-0 ps-3 small text-muted">
                                <li>Isi setiap kolom sesuai aktivitas yang dilakukan pada hari ini.</li>
                                <li>Angka <strong>Leads</strong> dan setiap <strong>Follow Up (FU)</strong> diisi
                                    berdasarkan jumlah
                                    pelanggan yang benar-benar dihubungi.</li>
                                <li>Kolom <strong>Closing</strong> diisi sesuai jumlah pelanggan yang berhasil closing
                                    dari
                                    masing-masing kategori.</li>
                                <li>Pastikan tidak ada kolom yang dibiarkan kosong — isi dengan angka <strong>0</strong>
                                    jika tidak
                                    ada aktivitas.</li>
                                <li>Periksa kembali semua data sebelum menekan tombol <strong>Simpan</strong>.</li>
                            </ul>
                        </div>
                    </div>

                    <div class="card report-card border-0 shadow-sm overflow-hidden">
                        <div
                            class="card-header bg-white border-0 p-4 pb-0 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="icon-square bg-primary-subtle text-primary rounded-3 me-3">
                                    <i class="fas fa-calendar-check fs-5"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold text-uppercase text-muted small mb-0">Laporan Tanggal</h6>
                                    <h5 class="fw-bold text-dark mb-0">{{ daysOnlyConvert(form.date) }}</h5>
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-4 position-relative">
                            <loading-overlay :show="form.processing"
                                :text="props.dailyReport?.daily_report_id ? 'Memperbarui Laporan Leads Harian...' : 'Menyimpan Laporan Leads Harian...'" />
                            <form-wrapper @submit="isSubmit">

                                <div class="section-group mb-4">
                                    <h6 class="section-title text-primary"><i class="fas fa-star me-2"></i>Aktivitas
                                        Utama Hari Ini</h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="p-3 rounded-3 bg-primary-subtle border border-primary-subtle">

                                                <input-label class="fw-bold text-primary-dark mb-1" for="leads"
                                                    value="Total Leads Masuk" />

                                                <input-number ref="inputLeadsRef" placeholder="0" name="leads"
                                                    v-model="form.leads"
                                                    input-class="form-control-lg fw-bold text-primary" />

                                                <small class="text-muted fst-italic mt-1 d-block">Jumlah kontak baru
                                                    yang didapat.</small>
                                                <input-error :message="form.errors.leads" />

                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class=" p-3 rounded-3 bg-success-subtle border border-success-subtle">
                                                <input-label class="fw-bold text-success-dark mb-1" for="closing"
                                                    value="Total Closing Langsung" />

                                                <input-number placeholder="0" name="closing" v-model="form.closing"
                                                    input-class="form-control-lg fw-bold text-success" />

                                                <small class="text-muted fst-italic mt-1 d-block">Closing dari leads
                                                    hari ini.</small>
                                                <input-error :message="form.errors.closing" />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <hr class="border-light my-3">

                                <div class="section-group mb-4">
                                    <h6 class="section-title text-secondary mb-3"><i
                                            class="fas fa-history me-2"></i>Rincian Follow Up (FU)</h6>

                                    <div class="row g-3">

                                        <div class="col-6">
                                            <div class="stat-card border rounded-3 p-3 h-100">
                                                <div class="d-flex align-items-center mb-2 text-muted">
                                                    <i class="fas fa-clock me-2"></i> <span class="fw-bold small">FU
                                                        Pelanggan Kemarin</span>
                                                </div>
                                                <div class="mb-2">
                                                    <input-label class="small text-muted" value="Dihubungi" />
                                                    <input-number placeholder="0" name="fu_yesterday"
                                                        v-model="form.fu_yesterday" />
                                                    <input-error :message="form.errors.fu_yesterday" />
                                                </div>
                                                <div>
                                                    <input-label class="small text-success fw-bold" value="Closing" />
                                                    <input-number placeholder="0" name="fu_yesterday_closing"
                                                        v-model="form.fu_yesterday_closing"
                                                        input-class="border-success-subtle" />
                                                    <input-error :message="form.errors.fu_yesterday_closing" />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-6">
                                            <div class="stat-card border rounded-3 p-3 h-100">
                                                <div class="d-flex align-items-center mb-2 text-muted">
                                                    <i class="fas fa-calendar-minus me-2"></i> <span
                                                        class="fw-bold small">FU PELANGGAN 2 HARI LALU</span>
                                                </div>
                                                <div class="mb-2">
                                                    <input-label class="small text-muted" value="Dihubungi" />
                                                    <input-number placeholder="0" name="fu_before_yesterday"
                                                        v-model="form.fu_before_yesterday" />
                                                    <input-error :message="form.errors.fu_before_yesterday" />
                                                </div>
                                                <div>
                                                    <input-label class="small text-success fw-bold" value="Closing" />
                                                    <input-number placeholder="0" name="fu_before_yesterday_closing"
                                                        v-model="form.fu_before_yesterday_closing"
                                                        input-class="border-success-subtle" />
                                                    <input-error :message="form.errors.fu_before_yesterday_closing" />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-6">
                                            <div class="stat-card border rounded-3 p-3 h-100">
                                                <div class="d-flex align-items-center mb-2 text-muted">
                                                    <i class="fas fa-calendar-week me-2"></i> <span
                                                        class="fw-bold small">FU PELANGGAN MINGGU LALU</span>
                                                </div>
                                                <div class="mb-2">
                                                    <input-label class="small text-muted" value="Dihubungi" />
                                                    <input-number placeholder="0" name="fu_last_week"
                                                        v-model="form.fu_last_week" />
                                                    <input-error :message="form.errors.fu_last_week" />
                                                </div>
                                                <div>
                                                    <input-label class="small text-success fw-bold" value="Closing" />
                                                    <input-number placeholder="0" name="fu_last_week_closing"
                                                        v-model="form.fu_last_week_closing"
                                                        input-class="border-success-subtle" />
                                                    <input-error :message="form.errors.fu_last_week_closing" />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-6">
                                            <div class="stat-card border rounded-3 p-3 h-100">
                                                <div class="d-flex align-items-center mb-2 text-muted">
                                                    <i class="fas fa-users me-2"></i> <span
                                                        class="fw-bold small">PELANGGAN LAMA</span>
                                                </div>
                                                <div class="mb-2">
                                                    <input-label class="small text-muted" value="Engage" />
                                                    <input-number placeholder="0" name="engage_old_customer"
                                                        v-model="form.engage_old_customer" />
                                                    <input-error :message="form.errors.engage_old_customer" />
                                                </div>
                                                <div>
                                                    <input-label class="small text-success fw-bold" value="Closing" />
                                                    <input-number placeholder="0" name="engage_closing"
                                                        v-model="form.engage_closing"
                                                        input-class="border-success-subtle" />
                                                    <input-error :message="form.errors.engage_closing" />
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <div class="d-flex justify-content-end mt-4 gap-3">
                                    <button @click.prevent="goBack"
                                        class="btn btn-light text-muted border-0 px-4 rounded-3 shadow-sm">
                                        Batal & Kembali
                                    </button>

                                    <base-button waiting="Menyimpan..." :loading="form.processing"
                                        button-class="btn-height-1 px-4 rounded-3 shadow-sm"
                                        :icon="props.dailyReport?.daily_report_id ? 'fas fa-check-circle' : 'fas fa-save'"
                                        :variant="props.dailyReport?.daily_report_id ? 'success' : 'primary'"
                                        type="submit"
                                        :label="props.dailyReport?.daily_report_id ? 'Perbarui Laporan' : 'Simpan Laporan'" />
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
/* Styling Kartu Utama */
.report-card {
    background: #ffffff;
    border-radius: 16px;
}

/* Header Icon Box */
.icon-square {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Styling Judul Bagian */
.section-title {
    font-size: 0.9rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 1rem;
}

/* Alert Instruksi Custom */
.custom-alert-info {
    background-color: #f0f7ff;
    /* Biru sangat muda */
    border-left: 4px solid #0d6efd;
}

/* Styling Input di dalam Box Utama (Leads & Closing) */
.stat-box input {
    background-color: rgba(255, 255, 255, 0.7);
    border: 1px solid transparent;
    transition: all 0.3s;
}

.stat-box input:focus {
    background-color: #ffffff;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

/* Text Colors untuk Background Subtle */
.text-primary-dark {
    color: #084298;
}

.text-success-dark {
    color: #0f5132;
}

/* Styling Kartu Kecil Follow Up */
.stat-card {
    background-color: #fff;
    border-color: #e9ecef !important;
    transition: transform 0.2s, box-shadow 0.2s;
}

.hover-shadow:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
    border-color: #cfe2ff !important;
}

/* Tombol Save Besar */
.btn-save-custom {
    font-weight: 600;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
}

.btn-save-custom:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
}

/* Utility Class untuk Tombol Kembali */
.hover-scale {
    transition: transform 0.2s;
}

.hover-scale:hover {
    transform: scale(1.05);
}
</style>
