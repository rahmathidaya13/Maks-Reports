<script setup>
import { computed, ref, reactive, watch } from "vue";
const props = defineProps({
    show: Boolean,
});
const emit = defineEmits(["update:show"]);

const form = reactive({
    start_date_dw: '',
    end_date_dw: '',
})
const isDisableBtnDownload = computed(() => {
    return !(form.start_date_dw && form.end_date_dw);
})

function downloadPdf() {
    window.open(
        route('story_report.print_to_pdf', {
            start_date: form.start_date_dw,
            end_date: form.end_date_dw
        }),
        '_blank'
    )
}
function downloadExcel() {
    window.open(
        route('story_report.print_to_excel', {
            start_date: form.start_date_dw,
            end_date: form.end_date_dw
        }),
        '_self'
    )
}
const resetField = () => {
    form.start_date_dw = '';
    form.end_date_dw = '';
}
const close = () => {
    resetField();
    emit("update:show", false);
}


const information = ref(null);
watch(() => [form.start_date_dw, form.end_date_dw],
    async ([start_date, end_date]) => {
        if (!start_date || !end_date) {
            information.value = null;
            return;
        }
        const { data } = await axios.get(route('story_report.information'), {
            params: {
                start_date: start_date,
                end_date: end_date
            }
        })
        if (data.status) {
            information.value = data;
        }
    })
</script>
<template>
    <div class="row" v-if="props.show">
        <div class="col-xl-12 col-sm-12">
            <modal size="modal-lg" :footer="false" icon="fas fa-download" :show="props.show" title="Unduh Laporan"
                @closed="close">
                <template #body>

                    <div class="alert alert-info border-0 shadow-sm rounded-4 d-flex align-items-start p-3 mb-4">
                        <i class="fas fa-info-circle fa-lg me-3 mt-1"></i>
                        <div>
                            <h6 class="fw-bold mb-1">Informasi Pengunduhan</h6>
                            <p class="small mb-0 opacity-75">
                                Data akan difilter berdasarkan rentang tanggal. Laporan tersedia dalam format <b>PDF</b>
                                dan <b>Excel</b>.
                            </p>
                        </div>
                    </div>

                    <div class="card border-0 bg-light rounded-4 mb-4">
                        <div class="card-body p-4">
                            <div class="row g-3 align-items-end">

                                <div class="col-md-5 input-focus-wrapper p-3 rounded-4 transition-all">
                                    <input-label class="small fw-bold text-uppercase text-muted mb-2"
                                        value="Tanggal Awal" for="start_date_dw" />
                                    <div class="input-group custom-group">
                                        <span class="input-group-text bg-transparent border-0 ps-0">
                                            <i class="fas fa-calendar-alt text-muted icon-calendar"></i>
                                        </span>
                                        <text-input name="start_date_dw" :isValid="false" type="date"
                                            input-class="border-0 bg-transparent p-0" v-model="form.start_date_dw" />
                                    </div>
                                </div>

                                <div class="col-md-2 text-center d-none d-md-block pb-4">
                                    <div class="bg-white shadow-sm rounded-circle d-inline-flex align-items-center justify-content-center"
                                        style="width: 40px; height: 40px;">
                                        <i class="fas fa-chevron-right text-primary"></i>
                                    </div>
                                </div>

                                <div class="col-md-5 input-focus-wrapper p-3 rounded-4 transition-all">
                                    <input-label class="small fw-bold text-uppercase text-muted mb-2"
                                        value="Tanggal Akhir" for="end_date_dw" />
                                    <div class="input-group custom-group">
                                        <span class="input-group-text bg-transparent border-0 ps-0">
                                            <i class="fas fa-calendar-alt text-muted icon-calendar"></i>
                                        </span>
                                        <text-input name="end_date_dw" :isValid="false" type="date"
                                            input-class="border-0 bg-transparent p-0" v-model="form.end_date_dw" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="information" class="animate__fadeIn">
                        <div class="d-flex align-items-center mb-3">
                            <span class="small fw-bold text-uppercase text-muted">Preview Data Laporan</span>
                            <div class="flex-grow-1 ms-3 border-bottom opacity-25"></div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <div
                                    class="p-3 rounded-4 border bg-white d-flex justify-content-between align-items-center">
                                    <span class="small text-muted"><i class="fas fa-clock me-2"></i>Periode
                                        Terpilih</span>
                                    <span class="fw-bold">{{ information.first_date }} — {{
                                        information.last_date
                                    }}</span>
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="p-3 rounded-4 border bg-white text-center shadow-sm">
                                    <div class="small text-muted mb-1">Baris Data</div>
                                    <div class="h5 fw-bold mb-0">{{ information.total_rows }}</div>
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="p-3 rounded-4 border bg-white text-center shadow-sm">
                                    <div class="small text-muted mb-1">Total Status</div>
                                    <div class="h5 fw-bold mb-0">{{ information.total_status }}</div>
                                </div>
                            </div>
                            <div class="col-md-4 col-12">
                                <div class="p-3 rounded-4 border bg-white text-center shadow-sm">
                                    <div class="small text-muted mb-1">Minggu Ke</div>
                                    <div class="h5 fw-bold mb-0">
                                        {{ information.week_start }}
                                        <span v-if="information.week_start !== information.week_end"
                                            class="mx-1 text-muted small">s/d</span>
                                        {{ information.week_start !== information.week_end ?
                                            information.week_end : ''
                                        }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 mt-5 pt-3 border-top">
                        <base-button @click="resetField" type="button" label="Batalkan" variant="light"
                            class="rounded-pill px-4" />

                        <div class="d-flex gap-2">
                            <button :disabled="isDisableBtnDownload" @click="downloadPdf"
                                class="btn btn-danger btn-download shadow-sm rounded-pill px-4 py-2">
                                <i class="fas fa-file-pdf me-2"></i>PDF
                            </button>
                            <button :disabled="isDisableBtnDownload" @click="downloadExcel"
                                class="btn btn-success btn-download shadow-sm rounded-pill px-4 py-2">
                                <i class="fas fa-file-excel me-2"></i>Excel
                            </button>
                        </div>
                    </div>

                </template>
            </modal>
        </div>
    </div>
</template>
<style scoped>
/* Membuat tampilan input tanggal lebih modern */
.input-group-text {
    border: 1px solid #dee2e6;
}

/* Shadow yang sangat halus */
.shadow-xs {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
}

/* Tombol Download Style */
.btn-download {
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
}

.btn-download:not(:disabled):hover {
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.btn-download:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Animasi sederhana saat modal muncul */
.alert-info {
    background-color: #e0f2fe;
    color: #0369a1;
}

/* State Default */
.input-focus-wrapper {
    background-color: #f8fafc;
    /* Abu-abu sangat muda */
    border: 2px solid transparent;
    cursor: pointer;
}

/* State ketika Input di dalamnya aktif (:focus-within) */
.input-focus-wrapper:focus-within {
    background-color: #ffffff;
    border-color: #3b82f6;
    /* Warna biru primary */
    box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.1);
}

/* Perubahan warna ikon saat focus */
.input-focus-wrapper:focus-within .icon-calendar {
    color: #3b82f6 !important;
}

/* Menghilangkan ring biru default browser pada input asli */
.form-control:focus {
    box-shadow: none !important;
    outline: none !important;
}

.transition-all {
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Custom group agar input date terlihat menyatu */
.custom-group {
    border-bottom: 1px solid #e2e8f0;
}

.input-focus-wrapper:focus-within .custom-group {
    border-bottom-color: #3b82f6;
}

/* Animasi untuk info dashboard */
.animate__fadeIn {
    animation: fadeIn 0.4s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>