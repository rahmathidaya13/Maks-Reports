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
})

const emit = defineEmits(["update:show", "opened", "closed"])

const modalEl = ref(null)
let modalInstance = null


onMounted(() => {
    modalInstance = new Modal(modalEl.value, {
        backdrop: true,
        keyboard: true
    })

    if (props.show) {
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

onUnmounted(() => {
    if (modalInstance) modalInstance.dispose()
})

watch(() => props.show, (v) => {
    if (!modalInstance) return
    if (v) {
        modalInstance.show()
    } else {
        modalInstance.hide()
    }
})

</script>

<template>
    <div class="modal custom-modal fade" tabindex="-1" ref="modalEl">
        <div class="modal-dialog" :class="size">
            <div class="modal-content smooth-content">

                <div class="modal-header">
                    <h5 class="modal-title">{{ title }}</h5>
                    <button type="button" class="btn-close" @click="emit('update:show', false)"></button>
                </div>

                <div class="modal-body">
                    <slot />
                </div>

                <div class="modal-footer">
                    <slot name="footer">
                        <button class="btn btn-secondary" @click="emit('update:show', false)">Close</button>
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
    transform: translateY(-20px);
    opacity: 0;
    transition:
        transform .35s ease,
        opacity .35s ease !important;
}

.custom-modal.show .smooth-content {
    transform: translateY(0);
    opacity: 1;
}
</style>
