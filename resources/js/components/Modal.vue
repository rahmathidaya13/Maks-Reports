<script setup>
import { nextTick, onMounted, onUnmounted, ref, watch } from 'vue'
import { Modal } from 'bootstrap'
const props = defineProps({
    show: Boolean,
    title: String,
    size: {
        type: String,
        default: '',
    },
    position: {
        type: String,
        default: '',
    },
    icon: {
        type: String,
        default: null,
    },
    footer: {
        type: Boolean,
        default: true
    },
    classModalTitle: {
        type: String,
        default: ''
    }
})

const emit = defineEmits(["update:show", "opened", "closed", "save"])

const modalEl = ref(null)
let modalInstance = null
onMounted(async () => {
    modalInstance = new Modal(modalEl.value, {
        backdrop: true,
        keyboard: false,
        backdrop: 'static',
        focus: true
    })
    if (props.show) {
        await nextTick();
        modalInstance.show()
    }

    modalEl.value.addEventListener('shown.bs.modal', () => {
        emit("opened")
    })

    modalEl.value.addEventListener('hidden.bs.modal', () => {
        emit("update:show", false)
        emit("closed")
    })
})

// CLOSE HANDLER YANG BENAR
const closeModal = () => {
    document.activeElement?.blur()  // Pindahkan fokus sebelum modal menyembunyikan dirinya
    if (modalInstance) modalInstance.hide()
}
onUnmounted(() => {
    if (modalInstance) modalInstance.dispose()
})

watch(() => props.show, async (v) => {
    if (!modalInstance) return
    if (v) {
        await nextTick();
        modalInstance.show()
    } else {
        document.activeElement?.blur()  // Pindahkan fokus sebelum modal menyembunyikan dirinya
        modalInstance.hide()
    }
})
</script>

<template>
    <div class="modal custom-modal fade" tabindex="-1" ref="modalEl">
        <div :class="['modal-dialog modal-dialog-scrollable ', position, size]">
            <div class="modal-content smooth-content border-0 shadow-lg rounded-4 overflow-hidden">

                <div class="modal-header bg-transparent border-0">
                    <h5 :class="`modal-title fw-bold ${classModalTitle}`"> <i v-if="icon" :class="icon"></i> {{ title }}
                    </h5>
                    <button @click="closeModal" type="button" class="close">
                        <span class="fs-1">&times;</span>
                    </button>
                </div>

                <div class="modal-body p-0 px-3 pb-3 bg-transparent">
                    <slot name="body" />
                </div>

                <div v-if="footer" class="modal-footer text-bg-grey">
                    <slot name="footer" />
                </div>

            </div>
        </div>
    </div>
</template>

<style scoped>
/* =============== ANIMASI BACKDROP =============== */
.custom-modal.fade .modal-backdrop {
    transition: opacity .35s ease !important;
}

/* =============== ANIMASI MODAL CONTENT =============== */
.custom-modal .smooth-content {
    transform: translateY(-50px);
    opacity: 0;
    transition:
        transform .35s ease,
        opacity .35s ease !important;
}

/* ANIMASI MASUK (Bootstrap menambahkan .show) */
.custom-modal.show .smooth-content {
    transform: translateY(0);
    opacity: 1;
}

.modal-body::-webkit-scrollbar {
    width: 6px;
}

.modal-body::-webkit-scrollbar-thumb {
    background-color: rgba(0, 0, 0, 0.2);
    border-radius: 10px;
}
</style>
