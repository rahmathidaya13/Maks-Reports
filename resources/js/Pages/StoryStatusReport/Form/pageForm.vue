<script setup>
import { computed, nextTick, onMounted, ref, watch } from "vue";
import { Head, Link, router, useForm, usePage } from "@inertiajs/vue3";
import moment from "moment";
const props = defineProps({
    storyReport: Object,
    date: String
})
// array form dinamis
const forms = ref([
    {
        report_time: props.storyReport?.report_time.slice(0, 5) ?? '',
        count_status: props.storyReport?.count_status ?? '',
    }
])


const formsRefs = ref([]) // Array untuk menyimpan referensi ke setiap form
const addForm = () => {
    forms.value.push({
        report_time: '',
        count_status: '',
    })
    nextTick(() => {
        const lastIndex = forms.value.length - 1;
        // Focus otomatis ke input Jam di form baru
        const input = formsRefs.value[lastIndex]?.querySelector("input[type='time']");
        input?.focus();
    })
}
const removeForm = (index) => {
    if (forms.value.length === 1) return;
    forms.value.splice(index, 1)
}

const form = useForm({
    report_date: props.storyReport?.report_date ?? props.date,
    report: forms.value,
});

watch(forms, () => {
    form.report = forms.value
}, {
    deep: true
})


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
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
            }
        });
    }
};

const title = ref("");
const icon = ref("");
const url = ref("");
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

// override button kembali
const loaderActive = ref(null);
const goBack = () => {
    loaderActive.value?.show("Memproses...");
    router.get(url.value, {}, {
        onFinish: () => loaderActive.value?.hide()
    });
}

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
            <loader-page ref="loaderActive" />
            <bread-crumbs :icon="icon" :title="title" :items="breadcrumbItems" />

            <div class="callout callout-success">
                <h5 class="fw-bold"><i class="fas fa-bullhorn me-2"></i>Panduan Pengisian Laporan Update Status Harian
                </h5>
                <ul class="mb-0 ps-3">
                    <li>Setiap form mewakili satu aktivitas posting status pada hari ini.</li>
                    <li>Isi <strong>Jam</strong> sesuai waktu status diposting (contoh: 08:30, 13:45, dll).</li>
                    <li>Jika memposting status beberapa kali dalam satu hari, klik tombol
                        <strong>Tambah</strong> untuk menambah form baru.
                    </li>
                    <li>Kolom <strong>Jumlah</strong> berisi banyaknya status yang diposting pada jam tersebut
                        (isi angka <strong>1</strong> jika hanya upload satu status).</li>
                    <li>Pastikan tidak ada kolom yang kosong — jika tidak ada status pada jam itu, hapus form terkait.
                    </li>
                    <li>Klik tombol <strong>X</strong> di setiap form untuk menghapus form yang tidak
                        diperlukan.</li>
                    <li>Periksa kembali seluruh data sebelum menekan tombol <strong>Simpan</strong> atau
                        <strong>Ubah</strong>.
                    </li>
                </ul>
            </div>

            <div class="d-flex justify-content-between">
                <Link @click.prevent="goBack" :href="url" class="btn btn-danger mb-3">
                    <i class="fas fa-arrow-left"></i>
                    Kembali
                </Link>
                <div class="mb-3 gap-1 d-flex" v-if="!props.storyReport?.story_status_id">
                    <transition name="fade">
                        <button title="Hapus Semua Form" v-if="forms.length > 1 && !form.processing"
                            @click="forms = [{ report_time: '', count_status: '' }]" type="button"
                            class="btn btn-danger position-relative align-items-center">
                            <i class="fas fa-trash-alt me-2"></i>
                            Hapus ({{ forms.length }})
                        </button>
                    </transition>
                    <button title="Tambah Form" v-if="!form.processing" @click="addForm"
                        class="btn btn-primary bg-gradient"><i class="fas fa-plus"></i>
                        Tambah</button>
                </div>
            </div>

            <div class="row pb-3">
                <div class="col-xl-12 col-12">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div
                            class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="bg-info bg-opacity-10 text-info p-2 rounded-3 me-3">
                                    <i class="fas fa-chart-line fs-4"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0 text-dark">Laporan Update Status</h5>
                                    <p class="text-muted small mb-0">Input laporan leads harian.</p>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-bold fs-6">
                                    <i class="far fa-calendar-alt me-2 text-muted"></i>
                                    {{ daysOnlyConvert(form.report_date) }}
                                </span>
                            </div>
                        </div>

                        <div v-if="form.processing"
                            class="position-absolute w-100 h-100 bg-white opacity-75 d-flex align-items-center justify-content-center"
                            style="z-index: 10;">
                            <loader-horizontal
                                :message="props.storyReport?.story_status_id ? 'Memperbarui Laporan...' : 'Menyimpan Laporan...'" />
                        </div>

                        <div class="card-body p-4 bg-light bg-opacity-25"
                            :class="['blur-area', form.processing ? 'is-blurred' : '']">
                            <form-wrapper @submit="isSubmit">

                                <div class="position-relative ps-2 form-overlay">

                                    <div class="position-absolute start-0 top-0 bottom-0 border-start border-2 border-primary border-opacity-25 ms-2"
                                        style="z-index: 0;" v-if="forms.length > 0"></div>

                                    <div :ref="el => formsRefs[index] = el" v-for="(fieldItems, index) in forms"
                                        :key="index" class="mb-3 position-relative ps-4">

                                        <div class="position-absolute start-0 top-50 translate-middle-y bg-white border border-2 border-primary rounded-circle"
                                            style="width: 16px; height: 16px; left: 8px !important; z-index: 1;"></div>

                                        <div class="card border-0 shadow-sm rounded-3 overflow-hidden transition-hover">
                                            <div class="card-body p-3">
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <h6 class="fw-bold text-primary mb-0 small text-uppercase ls-1">
                                                        <i class="fas fa-clock me-1"></i> Sesi Laporan {{ index + 1 }}
                                                    </h6>

                                                    <button v-if="forms.length > 1" title="Hapus Baris Ini"
                                                        class="btn btn-link text-danger"
                                                        @click.prevent="removeForm(index)">
                                                        <i class="fas fa-times-circle fs-5"></i>
                                                    </button>
                                                </div>

                                                <div class="row g-3 align-items-start">

                                                    <div class="col-md-6">
                                                        <input-label class="fw-bold small text-muted"
                                                            :for="`time_${index}`" value="Waktu / Jam" />
                                                        <div class="position-relative">
                                                            <i class="far fa-clock text-muted input-icon-left"></i>
                                                            <text-input autofocus
                                                                input-class="fw-bold text-dark input-fixed-height input-height-2"
                                                                :id="`time_${index}`" :tabindex="index * 2 + 1"
                                                                placeholder="00:00"
                                                                :name="`report.${index}.report_time`" type="time"
                                                                v-model="fieldItems.report_time" />
                                                        </div>
                                                        <input-error
                                                            :message="form.errors[`report.${index}.report_time`]" />
                                                    </div>

                                                    <div class="col-md-6">
                                                        <input-label class="fw-bold small text-muted"
                                                            :for="`count_${index}`" value="Jumlah Status" />
                                                        <div class="position-relative">
                                                            <i class="fas fa-hashtag text-muted input-icon-left"></i>
                                                            <input-number
                                                                input-class="input-fixed-height input-height-2 fw-bold text-primary has-icon-right"
                                                                :id="`count_${index}`" :tabindex="index * 2 + 2"
                                                                placeholder="0" :name="`report.${index}.count_status`"
                                                                type="text" v-model="fieldItems.count_status" />
                                                            <span
                                                                class="input-icon-right bg-light text-muted small">Qty</span>
                                                        </div>
                                                        <input-error
                                                            :message="form.errors[`report.${index}.count_status`]" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                                    <div class="d-none d-md-block">
                                        <small class="text-muted text-uppercase fw-bold"
                                            style="font-size: 0.7rem;">Total Sesi</small>
                                        <div class="h5 mb-0 fw-bold text-dark">{{ forms.length }} <span
                                                class="small fw-normal text-muted">Entri</span></div>
                                    </div>

                                    <div
                                        :class="{ 'sticky-bottom bg-white py-3 border-top shadow-lg w-100 px-4 position-fixed start-0 bottom-0 d-flex justify-content-end z-3': forms.length > 5 }">
                                        <base-button @click="isSubmit" waiting="Menyimpan..." :loading="form.processing"
                                            class="rounded-3 shadow px-5 btn-height-2 fw-bold"
                                            :icon="props.storyReport && props.storyReport?.story_status_id ? 'fas fa-save' : 'fas fa-paper-plane'"
                                            :variant="props.storyReport && props.storyReport?.story_status_id ? 'success' : 'primary'"
                                            type="submit"
                                            :name="props.storyReport && props.storyReport?.story_status_id ? 'ubah' : 'simpan'"
                                            :label="props.storyReport && props.storyReport?.story_status_id ? 'Simpan Perubahan' : 'Kirim Laporan'" />
                                    </div>
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
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s ease, transform 0.5s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(-5px);
}

.fade-enter-to,
.fade-leave-from {
    opacity: 1;
    transform: translateY(0);
}

.blur-area {
    transition: all 0.3s ease;
}

.blur-area.is-blurred {
    filter: blur(3px);
    pointer-events: none;
    user-select: none;
    opacity: 0.6;
}


.form-overlay {
    max-height: 60vh;
    overflow-y: auto;
    padding-right: 6px;
    position: relative;

}

.sticky-submit {
    position: sticky;
    bottom: 0;
    background: rgba(255, 255, 255, .95);
    padding: 10px 0;
    border-top: 1px solid #ddd;
    backdrop-filter: blur(6px);
    z-index: 999;
    display: flex;
}
</style>
