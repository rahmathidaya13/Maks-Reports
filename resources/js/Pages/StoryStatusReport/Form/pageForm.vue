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

                    <button title="Hapus Semua Form" v-if="forms.length > 1 && !form.processing"
                        class="btn btn-outline-danger position-relative align-items-center"
                        @click="forms = [{ report_time: '', count_status: '' }]"><i class="fas fa-recycle"></i> Hapus
                        <span class="badge text-bg-danger rounded-pill">{{ forms.length }}</span>
                    </button>


                    <button title="Tambah Form" v-if="!form.processing" @click="addForm"
                        class="btn btn-primary bg-gradient"><i class="fas fa-plus"></i>
                        Tambah</button>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-12 col-sm-12 pb-3">
                    <div class="card overflow-hidden rounded-3 shadow-sm">
                        <h5 class="card-header fw-bold text-uppercase p-3 text-bg-dark">
                            <i class="fas fa-clipboard me-1 text-light"></i>
                            Laporan Update Status:
                            <span class="text-info">{{ daysOnlyConvert(form.report_date) }}
                            </span>
                        </h5>
                        <div v-if="form.processing">
                            <loader-horizontal
                                :message="props.storyReport?.story_status_id ? 'Sedang memperbarui data' : 'Sedang menyimpan data'" />
                        </div>

                        <div class="card-body" :class="['blur-area', form.processing ? 'is-blurred' : '']">

                            <div class="form-overlay">
                                <form-wrapper @submit="isSubmit">

                                    <div :ref="el => formsRefs[index] = el" v-for="(fieldItems, index) in forms"
                                        :key="index"
                                        class="mb-3 text-bg-grey rounded-2 border overflow-hidden border border-dark shadow-sm">

                                        <div class="py-2 px-3 d-flex justify-content-between border-bottom">
                                            <h4 class="fw-bold">Form Laporan {{ index + 1 }}</h4>
                                            <button title="Hapus Form" class="btn btn-sm btn-danger"
                                                @click.prevent="removeForm(index)" v-if="forms.length > 1">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>

                                        <div class="px-3 py-2">
                                            <div class="mb-2">
                                                <input-label class="fw-bold" :for="index" value="Jam" />
                                                <text-input autofocus class="form-control-lg"
                                                    :tabindex="index * 2 + 1" placeholder="00:00"
                                                    :name="`report.${index}.report_time`" type="time"
                                                    v-model="fieldItems.report_time" />
                                                <input-error :message="form.errors[`report.${index}.report_time`]" />
                                            </div>
                                            <div class="mb-2">
                                                <input-label class="fw-bold" for="count_status" value="Jumlah" />
                                                <input-number class="form-control-lg" :tabindex="index * 2 + 2"
                                                    placeholder="0" :name="`report.${index}.count_status`" type="text"
                                                    v-model="fieldItems.count_status" />
                                                <input-error :message="form.errors[`report.${index}.count_status`]" />
                                            </div>
                                        </div>
                                    </div>
                                </form-wrapper>
                            </div>
                            <div class="d-grid d-xl-flex" :class="{ 'sticky-submit': forms.length > 1 }">
                                <base-button @click="isSubmit" waiting="Memproses..." :loading="form.processing"
                                    class="rounded-3 bg-gradient px-5 btn-height-2"
                                    :icon="props.storyReport && props.storyReport?.story_status_id ? 'fas fa-edit' : 'fas fa-save'"
                                    :variant="props.storyReport && props.storyReport?.story_status_id ? 'success' : 'primary'"
                                    type="submit"
                                    :name="props.storyReport && props.storyReport?.story_status_id ? 'ubah' : 'simpan'"
                                    :label="props.storyReport && props.storyReport?.story_status_id ? 'Ubah' : 'Simpan'" />
                            </div>
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
