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
        <div :class="['modal-dialog ', position, size]">
            <div class="modal-content smooth-content">

                <div class="modal-header text-bg-grey">
                    <h5 class="modal-title fw-bold"> <i v-if="icon" :class="icon"></i> {{ title }}</h5>
                    <button @click="closeModal" type="button" class="close">
                        <span class="fs-1">&times;</span>
                    </button>
                </div>

                <div class="modal-body p-0 px-3 pb-3">
                    <slot name="body" />
                </div>

                <div v-if="footer" class="modal-footer d-flex justify-content-between">
                    <slot name="footer">
                        <button class="btn btn-primary" @click="emit('save')">Save</button>
                        <button class="btn btn-secondary" @click="closeModal">Close</button>
                    </slot>
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
    transform: translateY(65px);
    opacity: 1;
}
</style>
