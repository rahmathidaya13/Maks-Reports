<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { formatDate } from "@/helpers/formatDate";
const props = defineProps({
    transaction: Array,
    show: Boolean,
});
const emit = defineEmits(["update:show"]);
const close = () => {
    emit("update:show", false);
}
const filters = reactive({
    start_date: null,
    end_date: null,
});
</script>
<template>
    <div class="row" v-if="props.show">
        <div class="col-xl-12 col-sm-12">
            <modal size="modal-lg" icon="fas fa-download" :show="props.show" title="Unduh" @closed="close">
                <template #body>
                    <div class="modern-alert mb-4">
                        <div class="icon-box">
                            <i class="fas fa-lightbulb text-info"></i>
                        </div>
                        <div class="alert-content">
                            <h6 class="fw-bold text-dark mb-1">Tips Unduh Data</h6>
                            <p class="text-muted small mb-0">
                                Gunakan filter di bawah untuk unduh laporan spesifik, atau tombol "Unduh Semua".
                                Untuk data yang jumlah (>1000) tidak dapat diunduh semua dengan format PDF.
                            </p>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <input-label class="fw-bold small text-muted" for="start_date" value="Tanggal Awal" />
                            <text-input type="date" v-model="filters.start_date" name="start_date" />
                        </div>
                        <div class="col-6">
                            <input-label class="fw-bold small text-muted" for="end_date" value="Tanggal Akhir" />
                            <text-input type="date" v-model="filters.end_date" name="end_date" />
                        </div>
                    </div>
                </template>
                <template #footer>
                    <div class="d-flex gap-1">
                        <button type="button" class="btn btn-danger rounded-pill px-3">
                            <i class="fas fa-file-pdf me-1"></i> PDF
                        </button>
                        <button type="button" class="btn btn-success rounded-pill px-3">
                            <i class="fas fa-file-excel me-1"></i> Excel
                        </button>
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
