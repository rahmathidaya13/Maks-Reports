<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { cleanTextCapitalize } from "@/helpers/cleanTextCapitalize";
const props = defineProps({
    permissions: Object,
    roles: String,
    show: Boolean,
});
const emit = defineEmits(["update:show"]);
const close = () => {
    emit("update:show", false);
}
// atur warna badge sesuai jenis permission
const getBadgeClass = (permName) => {
    switch (true) {
        case /create|export|import/i.test(permName):
            return 'bg-success'
        case /edit|update/i.test(permName):
            return 'bg-warning text-dark'
        case /delete|remove/i.test(permName):
            return 'bg-danger'
        case /view|show/i.test(permName):
            return 'bg-info text-dark'
        case /manage|access|assign|share/i.test(permName):
            return 'bg-primary'
        default:
            return 'bg-secondary'
    }
}
</script>
<template>
    <div class="row" v-if="show">
        <div class="col-xl-12 col-sm-12">
            <modal size="modal-lg" :footer="false" icon="fas fa-info-circle" :show="show"
                title="Detail izin Akses" @closed="close">
                <template #body>
                    <div class="role-detail-header d-flex align-items-center p-4 rounded-4 mb-4 border-0">
                        <div class="icon-wrapper-lg shadow-sm me-3">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div>
                            <small class="text-uppercase tracking-wider fw-bold text-primary-emphasis opacity-75">Detail
                                Peran</small>
                            <h3 class="fw-extrabold mb-0 text-dark">{{ cleanTextCapitalize(roles) }}</h3>
                        </div>
                    </div>

                    <div class="px-2">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="fw-bold mb-0">
                                <i class="fas fa-key me-2 text-warning"></i>Izin Akses Terdaftar
                            </h6>
                            <span class="badge rounded-pill bg-light text-dark border px-3">
                                {{ permissions.length }} Total Izin
                            </span>
                        </div>

                        <div class="permission-viewer-grid">
                            <div v-for="perm in permissions" :key="perm.id" class="permission-item shadow-sm">
                                <div class="perm-dot" :class="getBadgeClass(perm.name)"></div>
                                <div class="perm-text text-capitalize">
                                    {{ cleanTextCapitalize(perm.name) }}
                                </div>
                            </div>

                            <div v-if="!permissions.length" class="text-center py-5 w-100">
                                <i class="fas fa-folder-open fa-3x text-light mb-3"></i>
                                <p class="text-muted">Tidak ada izin akses yang tersemat.</p>
                            </div>
                        </div>
                    </div>
                </template>
            </modal>
        </div>
    </div>
</template>
<style scoped>
/* for Modal only */
/* Header Peranan */
.role-detail-header {
    background: linear-gradient(to right, #f8f9ff, #ffffff);
    border: 1px solid #edf2f9;
}

.icon-wrapper-lg {
    width: 60px;
    height: 60px;
    background: #4f46e5;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 16px;
    font-size: 1.5rem;
}

.tracking-wider {
    letter-spacing: 0.1em;
    font-size: 0.7rem;
}

/* Grid System */
.permission-viewer-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 12px;
    max-height: 350px;
    overflow-y: auto;
    padding: 4px;
}

/* Item Permission */
.permission-item {
    background: #ffffff;
    border: 1px solid #f1f1f1;
    border-radius: 12px;
    padding: 12px 16px;
    display: flex;
    align-items: center;
    transition: all 0.2s ease;
}

.permission-item:hover {
    border-color: #4f46e5;
    transform: translateY(-2px);
    background: #fbfbff;
}

.perm-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    margin-right: 12px;
    flex-shrink: 0;
}

.perm-text {
    font-size: 0.85rem;
    font-weight: 600;
    color: #4b5563;
}

/* Scrollbar Style */
.permission-viewer-grid::-webkit-scrollbar {
    width: 5px;
}

.permission-viewer-grid::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}

/* Helpers dari getBadgeClass yang disesuaikan */
.bg-success {
    background-color: #10b981 !important;
}

.bg-danger {
    background-color: #ef4444 !important;
}

.bg-info {
    background-color: #3b82f6 !important;
}

.bg-warning {
    background-color: #f59e0b !important;
}
</style>
