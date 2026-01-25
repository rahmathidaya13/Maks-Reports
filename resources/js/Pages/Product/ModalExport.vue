<script setup>
import { computed, reactive } from "vue";
const props = defineProps({
    show: Boolean,
    branches: Object,
    categories: Object,
});
const emit = defineEmits(["update:show"]);
const filter = reactive({
    branch: null,
    item_condition: null,
    category: null,
})
const conditions = [
    { value: null, label: 'Semua Kondisi' },
    { value: 'new', label: 'Baru (New)' },
    { value: 'used', label: 'Bekas (Used)' },
    { value: 'refurbished', label: 'Rekondisi (Refurbished)' },
    { value: 'damaged', label: 'Rusak (Damaged)' },
    { value: 'discontinued', label: 'Tidak Produksi (Discontinued)' },
];
const close = () => {
    filter.branch = null;
    filter.item_condition = null;
    filter.category = null;
    emit("update:show", false);
}

const categories = computed(() => [
    { label: "Semua Kategori", value: null },
    ...props.categories.map(cat => ({
        label: cat.category,
        value: cat.category,
    }))

]);
const branch = computed(() => [
    { label: "Semua Cabang", value: null },
    ...props.branches.map(br => ({
        label: br.name,
        value: br.branches_id,
    }))
]);

const download = (format, ignoreFilters = false) => {
    // Siapkan parameter
    const params = {};
    if (!ignoreFilters) {
        if (filter.branch) params.branches_id = filter.branch;
        if (filter.item_condition) params.item_condition = filter.item_condition;
        if (filter.category) params.category = filter.category;
    }
    // Generate URL (gunakan helper route() Ziggy)
    const url = route('product.export', {
        format: format,
        ...params
    });

    // Buka di tab baru
    window.open(url, '_blank');

    // Opsional: Tutup modal setelah download
    close();
};
</script>
<template>
    <div class="row" v-if="props.show">
        <div class="col-xl-12 col-sm-12">
            <modal size="modal-lg" :footer="false" icon="fas fa-download" :show="props.show" title="Unduh Data Produk"
                @closed="close">
                <template #body>
                    <div class="p-2">

                        <div class="modern-alert mb-4">
                            <div class="icon-box">
                                <i class="fas fa-lightbulb text-info"></i>
                            </div>
                            <div class="alert-content">
                                <h6 class="fw-bold text-dark mb-1">Tips Unduh Data</h6>
                                <p class="text-muted small mb-0">
                                    Gunakan filter di bawah untuk laporan spesifik, atau tombol "Unduh Semua" untuk
                                    backup data lengkap.
                                </p>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Cabang</label>
                                <select-input :options="branch" v-model="filter.branch" name="branch" />
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Kondisi Produk</label>
                                <select class="form-select" v-model="filter.item_condition">
                                    <option v-for="cond in conditions" :key="cond.value" :value="cond.value">
                                        {{ cond.label }}
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Kategori</label>
                                <select-input :options="categories" v-model="filter.category" name="category" />
                            </div>
                        </div>

                        <hr class="border-secondary border-opacity-25 my-4">

                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm"
                                    @click="download('pdf', true)">
                                    <i class="fas fa-file-pdf me-1"></i> PDF (Semua)
                                </button>
                                <button type="button" class="btn btn-outline-success btn-sm"
                                    @click="download('excel', true)">
                                    <i class="fas fa-file-excel me-1"></i> Excel (Semua)
                                </button>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-danger shadow-sm" @click="download('pdf')">
                                    <i class="fas fa-file-pdf me-2"></i> Export PDF
                                </button>
                                <button type="button" class="btn btn-success shadow-sm" @click="download('excel')">
                                    <i class="fas fa-file-excel me-2"></i> Export Excel
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
