<script setup>
import axios from "axios";
import { computed, reactive, watch, ref } from "vue";
const props = defineProps({
    show: Boolean,
    branches: Object,
    categories: Object,
    product: Object,
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
function formatCategory(cat) {
    return cat
        .split('/')                      // pecah sub kategori
        .map(part => part.replace(/-/g, ' '))  // ganti - dengan spasi
        .map(part => part.replace(/\b\w/g, char => char.toUpperCase())) // kapital
        .join(' - ');                    // gabungkan dengan pemisah cantik
}

const categories = computed(() => [
    { label: "Semua Kategori", value: null },
    ...props.categories.map(cat => ({
        label: formatCategory(cat.category),
        value: cat.category,
    }))

]);
const branch = computed(() => [
    { label: "Semua Cabang", value: null },
    ...props.branches.map(br => ({
        label: br.name,
        value: br.name,
    }))
]);

const download = (format, ignoreFilters = false) => {
    // Siapkan parameter
    const params = {};
    if (!ignoreFilters) {
        if (filter.branch) params.branch = filter.branch;
        if (filter.item_condition) params.item_condition = filter.item_condition;
        if (filter.category) params.category = filter.category;
    }
    const url = route('product.export', {
        format: format,
        ...params
    });

    // Buka di tab baru
    window.open(url, '_blank');

};
const totalProductFilter = ref(0)
const isLoadingCount = ref(false);
let isReseting = false
watch(filter, async () => {
    if (isReseting) return;
    isLoadingCount.value = true
    try {
        const response = await axios.get(route('product.information'), {
            params: {
                branch: filter.branch, // Kirim parameter
                item_condition: filter.item_condition,
                category: filter.category
            }
        });
        totalProductFilter.value = response.data.total

    } catch (error) {
        totalProductFilter.value = 0
    } finally {
        isLoadingCount.value = false
    }
}, { deep: true });

const close = () => {
    isReseting = true
    filter.branch = null;
    filter.item_condition = null;
    filter.category = null;
    totalProductFilter.value = 0
    isLoadingCount.value = false
    emit("update:show", false);

    setTimeout(() => {
        isReseting = false
    }, 300);
}
</script>
<template>
    <div class="row" v-if="props.show">
        <div class="col-xl-12 col-sm-12">
            <modal size="modal-lg" :footer="false" icon="fas fa-download text-success" :show="props.show"
                title="Unduh Data Produk" @closed="close">
                <template #body>
                    <div class="p-2">

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

                        <div class="row g-2 row-cols-2">
                            <div class="col-6">
                                <label class="form-label fw-bold small text-muted">Cabang</label>
                                <select-input :options="branch" v-model="filter.branch" name="branch" />
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-bold small text-muted">Kondisi Produk</label>
                                <select-input :options="conditions" v-model="filter.item_condition" name="condition" />
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold small text-muted">Kategori</label>
                                <select-input :options="categories" v-model="filter.category" name="category" />
                            </div>
                        </div>


                        <div class="alert alert-light border d-flex justify-content-between align-items-center mt-3">
                            <span class="text-muted small">Total Data Terpilih:</span>

                            <span v-if="isLoadingCount" class="spinner-border spinner-border-sm text-primary"></span>
                            <span v-else class="fw-bold text-primary fs-5">
                                {{ totalProductFilter }} Produk
                            </span>
                        </div>

                        <hr class="border-secondary border-opacity-50 my-2">

                        <div class="d-block justify-content-between gap-2 d-xl-flex">
                            <div class="d-flex gap-2">
                                <button :disabled="props.product.total > 1000" type="button"
                                    class="btn btn-outline-secondary rounded-pill" @click="download('pdf', true)">
                                    <i class="fas fa-file-pdf me-1"></i> PDF (Semua)
                                </button>
                                <button type="button" class="btn btn-outline-success rounded-pill"
                                    @click="download('excel', true)">
                                    <i class="fas fa-file-excel me-1"></i> Excel (Semua)
                                </button>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-danger shadow-sm rounded-pill"
                                    @click="download('pdf')">
                                    <i class="fas fa-file-pdf me-2"></i>PDF
                                </button>
                                <button type="button" class="btn btn-success shadow-sm rounded-pill"
                                    @click="download('excel')">
                                    <i class="fas fa-file-excel me-2"></i>Excel
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
