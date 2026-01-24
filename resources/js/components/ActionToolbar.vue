<script setup>
defineProps({
    actions: {
        type: Array,
        default: () => []
    }
});
</script>
<template>
    <div class="action-toolbar d-flex align-items-center bg-white px-3 p-2 rounded-4 shadow-sm border">

        <div class="btn-group gap-1">
            <template v-for="(btn, i) in actions" :key="i">
                <button v-if="btn.show !== false" type="button" :disabled="btn.disabled" @click.prevent="btn.click"
                    class="btn align-items-center transition-all border-0"
                    :class="btn.isPrimary ? 'btn-action-primary ' : 'btn-action-soft'" :title="btn.title">

                    <i v-if="btn.loading" class="fas fa-spinner fa-spin me-xl-2"></i>
                    <i v-else :class="[btn.icon, btn.isPrimary ? '' : btn.iconColor]" class="me-xl-2"></i>

                    <span :class="`d-none d-xl-inline fw-semibold ${btn.labelColor}`">
                        {{ btn.loading ? (btn.loadingText || 'Memuat..') : btn.label }}
                    </span>
                </button>
            </template>
        </div>
    </div>
</template>
<style scoped>
/* Toolbar Container */
.action-toolbar {
    width: fit-content;
}

/* Base Button Style */
.btn-action-soft,
.btn-action-primary {
    border: none;
    padding: 6px 10px;
    font-size: 0.875rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    transition: all 0.2s ease;
}

/* Soft Button (Info, Unduh, Segarkan) */
.btn-action-soft {
    background-color: transparent;
    color: #475569;
}

.btn-action-soft:hover {
    background-color: #f1f5f9;
    color: #1e293b;
}

.btn-action-soft i {
    font-size: 1rem;
}

.btn-action-danger {
    background-color: #f87171;
    color: white;
    border-radius: 10px !important;
    /* Membuatnya sedikit menonjol */
    box-shadow: 0 4px 6px -1px rgba(13, 110, 253, 0.2);
}

/* Primary Button (Buat Laporan) */
.btn-action-primary {
    background-color: #0d6efd;
    color: white;
    border-radius: 10px !important;
    /* Membuatnya sedikit menonjol */
    box-shadow: 0 4px 6px -1px rgba(13, 110, 253, 0.2);
}

.btn-action-primary:hover {
    background-color: #0b5ed7;
    transform: translateY(-1px);
    box-shadow: 0 6px 10px -1px rgba(13, 110, 253, 0.3);
}

/* Styling Khusus untuk btn-group agar tidak kaku */
.btn-group .btn:not(:last-child) {
    border-right: 1px solid #f1f5f9;
}

.btn-group {
    background: #f8fafc;
    border-radius: 10px;
    padding: 2px;
}

/* Animasi Spinner */
.fa-spin {
    animation: fa-spin 1s infinite linear;
}

.transition-all {
    transition: all 0.2s ease-in-out;
}
</style>