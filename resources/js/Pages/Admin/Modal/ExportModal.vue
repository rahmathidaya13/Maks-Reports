<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { formatDate } from "@/helpers/formatDate";
import moment from "moment";
moment.locale('id');
const props = defineProps({
    show: Boolean,
});
const emit = defineEmits(["update:show"]);

const useDateFilter = ref(false);
const filters = reactive({
    start_date: null,
    end_date: null,
});
const handleToggle = () => {
    if (!useDateFilter.value) {
        filters.start_date = null;
        filters.end_date = null;
    }
}
const download = async (format) => {
    const payload = useDateFilter.value ? filters : { start_date: null, end_date: null };
    // Siapkan URL
    const url = route('admin.dashboard.export', {
        format: format,
        filters: payload
    });

    // Buka di tab baru
    window.open(url, '_blank');
}
const reset = () => {
    useDateFilter.value = false;
    filters.start_date = null;
    filters.end_date = null;
}
const close = () => {
    reset();
    emit("update:show", false);
}
</script>
<template>
    <div class="row" v-if="props.show">
        <div class="col-xl-12 col-sm-12">
            <modal size="modal-lg" icon="fas fa-download" :show="props.show" title="Unduh Laporan" @closed="close">
                <template #body>
                    <div
                        class="modern-alert bg-info bg-opacity-10 border border-info border-opacity-25 rounded-3 p-3 mb-4 d-flex align-items-start">
                        <div class="icon-box me-3 mt-1">
                            <i class="fas fa-lightbulb text-info fs-4"></i>
                        </div>
                        <div class="alert-content">
                            <h6 class="fw-bold text-dark mb-1">Tips Unduh Data</h6>
                            <p class="text-muted small mb-0">
                                Secara default sistem akan mengunduh data <strong>Bulan Ini</strong>. Aktifkan toggle di
                                bawah jika Anda ingin mengunduh laporan pada rentang tanggal tertentu.
                            </p>
                        </div>
                    </div>
                    <div class="p-3">
                        <div class="d-flex align-items-center mb-3 justify-content-between">
                            <label class="form-check-label fw-bold text-dark" for="dateFilterSwitch"
                                style="cursor: pointer;">
                                Tentukan Tanggal
                            </label>
                            <div class="form-check form-switch form-switch-lg mb-0">
                                <input class="form-check-input" type="checkbox" role="switch" id="dateFilterSwitch"
                                    v-model="useDateFilter" @change="handleToggle"
                                    style="cursor: pointer; transform: scale(1.2); margin-top: 0.15rem;">
                            </div>
                        </div>

                        <div class="row g-1 p-3 rounded-4 mb-3" style="transition: all 0.3s ease;"
                            :class="useDateFilter ? 'bg-white border shadow-sm' : 'bg-light border opacity-50 pe-none'">

                            <div class="col-6">
                                <input-label class="fw-bold small text-muted mb-2" for="start_date"
                                    value="Mulai Tanggal" />
                                <text-input type="date" v-model="filters.start_date" name="start_date"
                                    :disabled="!useDateFilter" />
                            </div>
                            <div class="col-6">
                                <input-label class="fw-bold small text-muted mb-2" for="end_date"
                                    value="Sampai Tanggal" />
                                <text-input type="date" v-model="filters.end_date" name="end_date"
                                    :disabled="!useDateFilter" />
                            </div>
                        </div>

                        <hr class="border-dashed opacity-50 mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <button @click.prevent="reset" type="button"
                                class="btn btn-light border text-muted fw-bold px-4">
                                Batal
                            </button>
                            <div class="d-flex gap-2">
                                <button @click.prevent="download('pdf')" type="button"
                                    class="btn btn-danger fw-bold px-4 shadow-sm">
                                    <i class="fas fa-file-pdf me-2"></i> Export PDF
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </modal>
        </div>
    </div>
</template>
<style scoped>
.modern-alert {
    display: flex;
    align-items: flex-start;
    padding: 1rem;
    background: #f8fbff;
    border: 1px solid #e1effe;
    border-radius: 12px;
    border-left: 4px solid #3b82f6;
}

.modern-alert .icon-box {
    width: 36px;
    height: 36px;
    background: #ffffff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
}
</style>
