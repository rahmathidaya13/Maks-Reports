<script setup>
import moment from "moment";
import { computed } from "vue";

// Set locale moment ke Indonesia
moment.locale("id");

const props = defineProps({
    show: Boolean,
    product: {
        type: Object,
        default: () => [{}]
    },
});

const emit = defineEmits(["update:show"]);

// --- Table Headers Configuration ---
const header = [
    { label: "No", key: "__index", attrs: { class: "text-center", width: "50px" } },
    { label: "Cabang", key: "branch", attrs: { class: "text-start" } },
    { label: "Harga Produk", key: "base_price", attrs: { class: "text-end" } }, // Align right untuk angka
    { label: "Periode Berlaku", key: "valid_until", attrs: { class: "text-start" } }, // Digabung agar hemat space
    { label: "Status", key: "status", attrs: { class: "text-center" } },
];

const close = () => {
    emit("update:show", false);
}

// --- Helpers ---
const formatDate = (date) => {
    if (!date) return '-';
    // Format: 15 Feb 2024
    return moment(date).format('DD MMM YYYY');
}

const formatCurrency = (value) => {
    if (!value && value !== 0) return "-";
    return new Intl.NumberFormat('id-ID', {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value);
}

</script>

<template>
    <div v-if="props.show">
        <modal title="Detail Produk" icon="fas fa-tags" width="1000px" size="modal-xl" :footer="false" :show="props.show" @closed="close">

            <template #header>
                <div class="d-flex align-items-center justify-content-between w-100 pe-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="icon-shape bg-primary bg-opacity-10 text-primary rounded-3">
                            <i class="fas fa-tags fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold text-dark">Rincian Harga Produk</h5>
                            <p class="mb-0 text-muted small">Kelola harga dan ketersediaan per cabang</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" @click="close"></button>
                </div>
            </template>

            <template #body>
                <div class="alert modern-alert mb-4" role="alert">
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <div class="d-flex align-items-center gap-3">
                            <div
                                class="product-avatar bg-white border rounded-circle d-flex align-items-center justify-content-center">
                                <i class="fas fa-box text-secondary"></i>
                            </div>
                            <div>
                                <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">Nama
                                    Produk</small>
                                <h6 class="fw-bold mb-0 text-dark">{{ props.product.name }}</h6>
                            </div>
                        </div>
                        <div class="text-end">
                            <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">Total
                                Cabang</small>
                            <h6 class="fw-bold mb-0 text-primary">{{ props.product.prices.length }} Lokasi</h6>
                        </div>
                    </div>
                </div>

                <base-table :markAll="false" :loader="false" loaderText="Sedang memuat data..." :headers="header"
                    :items="product.prices" row-key="product_price_id">
                    <template #empty>
                        <div class="text-center py-5">
                            <div class="bg-light rounded-circle d-inline-flex p-4 mb-3">
                                <i class="fas fa-store-slash text-muted opacity-50 fs-1"></i>
                            </div>
                            <h6 class="fw-bold text-dark">Belum ada harga cabang</h6>
                            <p class="text-muted small">Produk ini belum dikonfigurasi untuk cabang manapun.</p>
                        </div>
                    </template>

                    <template #row="{ item, index }">

                        <td class="text-center text-muted fw-medium">{{ index + 1 }}</td>

                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas fa-map-marker-alt text-danger opacity-75"></i>
                                <span class="fw-semibold text-dark">{{ item.branch?.name || '-' }}</span>
                            </div>
                        </td>

                        <td class="text-end">
                            <div v-if="item.discount_price && item.discount_price > 0">
                                <div class="d-flex flex-column align-items-end">
                                    <span class="text-danger fw-bold">{{ formatCurrency(item.discount_price) }}</span>
                                    <small class="text-muted text-decoration-line-through" style="font-size: 0.75rem;">
                                        {{ formatCurrency(item.base_price) }}
                                    </small>
                                </div>
                            </div>
                            <div v-else>
                                <span class="fw-bold text-dark">{{ formatCurrency(item.base_price) }}</span>
                            </div>
                        </td>

                        <td>
                            <div class="d-flex flex-column small">
                                <div class="mb-1">
                                    <i class="fas fa-calendar-alt text-muted me-1 width-15"></i>
                                    <span class="text-dark">{{ formatDate(item.valid_from) }}</span>
                                    <span class="mx-1 text-muted">-</span>
                                    <span :class="!item.valid_until ? 'text-success fw-bold' : 'text-dark'">
                                        {{ item.valid_until ? formatDate(item.valid_until) : 'Seterusnya' }}
                                    </span>
                                </div>
                            </div>
                        </td>

                        <td class="text-center">
                            <div class="d-flex flex-column align-items-center gap-1">
                                <span class="badge rounded-pill border px-3" :class="{
                                    'badge-published': item.status === 'published',
                                    'badge-draft': item.status === 'draft'
                                }">
                                    <i class="fas fa-circle me-1 small-dot"></i>
                                    {{ item.status === 'published' ? 'Publish' : 'Draft' }}
                                </span>

                                <span v-if="item.price_type == 'discount'"
                                    class="badge bg-danger bg-opacity-10 text-danger border-danger border border-opacity-10 rounded-1"
                                    style="font-size: 0.65rem;">
                                    PROMO
                                </span>
                            </div>
                        </td>

                    </template>
                </base-table>
            </template>
        </modal>
    </div>
</template>

<style scoped>
/* Icon Shape di Header */
.icon-shape {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Modern Alert Info Bar */
.modern-alert {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-left: 4px solid #0d6efd;
    border-radius: 8px;
    padding: 1rem 1.5rem;
}

.product-avatar {
    width: 40px;
    height: 40px;
}

/* Typography Helpers */
.fs-7 {
    font-size: 0.85rem;
}

.width-15 {
    width: 15px;
    display: inline-block;
    text-align: center;
}

/* Custom Badges */
.badge-published {
    background-color: #ecfdf5;
    /* Soft Green */
    color: #047857;
    border-color: #a7f3d0 !important;
}

.badge-draft {
    background-color: #f3f4f6;
    /* Soft Gray */
    color: #4b5563;
    border-color: #e5e7eb !important;
}

.small-dot {
    font-size: 0.5rem;
    vertical-align: middle;
}

/* Table Styling Override */
:deep(.table-hover-custom tbody tr:hover) {
    background-color: #f9fafb;
}

:deep(th) {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6c757d;
    background-color: #fff;
    border-bottom: 2px solid #f3f4f6;
    padding-top: 1rem;
    padding-bottom: 1rem;
}

:deep(td) {
    vertical-align: middle;
    padding-top: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #f3f4f6;
}
</style>
