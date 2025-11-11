<script setup>
import { ref, watch } from "vue";
const props = defineProps({
    modalId: {
        type: String,
        default: 'exampleModal',
    },
    title: { type: String, default: "Modal title" },
    body: { type: String, default: "" },
    show: { type: Boolean, default: false },
    dialogClass: { type: String, default: "" },
    close: {
        type: String,
        default: 'Close'
    },
    save: {
        type: String,
        default: 'Save'
    }
});

const emit = defineEmits(["update:show", "save", "hidden", "shown"]);

const visible = ref(props.show);
watch(
    () => props.show,
    (val) => (visible.value = val)
);

function hide() {
    visible.value = false;
    emit("update:show", false);
    emit("hidden");
}

function onBeforeEnter() {
    emit("shown");
}

function onAfterLeave() {
    emit("hidden");
}
</script>
<template>
    <transition name="modal-fade" @before-enter="onBeforeEnter" @after-leave="onAfterLeave">
        <div v-if="visible">
            <!-- BACKDROP -->
            <div class="modal-backdrop fade show"></div>
            <div class="modal fade show" ref="modalRef" tabindex="-1" :id="modalId" role="dialog"
                :aria-labelledby="modalId + 'Label'" :aria-modal="visible" :aria-hidden="!visible"
                style="display: block;">
                <div class="modal-dialog" :class="dialogClass">
                    <div class="modal-content">

                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="modalTitle">
                                {{ title }}
                            </h1>
                            <button type="button" class="btn-close" aria-label="Close" @click="hide"></button>
                        </div>

                        <div class="modal-body">
                            <slot name="body">
                                {{ body }}
                            </slot>
                        </div>

                        <div class="modal-footer d-flex justify-content-between">
                            <slot name="footer">
                                <button type="button" class="btn btn-outline-secondary" @click="hide">
                                    {{ close }}
                                </button>
                                <button type="button" class="btn btn-primary" @click="$emit('save')">
                                    {{ save }}
                                </button>
                            </slot>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </transition>
</template>
<style scoped>
/* === Smooth Transition === */
.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity 0.25s ease, transform 0.25s ease;

}

.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}

/* === Backdrop Style === */
.modal-backdrop {
    position: fixed;
    inset: 0;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1040;
    opacity: 1;
    transition: opacity 0.25s ease;
}

/* === Modal Proper Positioning === */
.modal {
    position: fixed;
    inset: 0;
    z-index: 1050;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow-x: hidden;
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
    outline: 0;
    opacity: 1;
    transition: opacity 0.25s ease;
}
</style>
