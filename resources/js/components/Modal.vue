<script setup>
import { ref, watch, onMounted, onUnmounted } from "vue";

const props = defineProps({
    modalId: { type: String, default: "exampleModal" },
    title: { type: String, default: "Modal title" },
    body: { type: String, default: "" },
    show: { type: Boolean, default: false },
    dialogClass: { type: String, default: "" },
    close: { type: String, default: "Close" },
    save: { type: String, default: "Save" },
});

const emit = defineEmits(["update:show", "save", "hidden", "shown"]);

const visible = ref(props.show);
const modalRef = ref(null);

// Sync prop -> internal
watch(
    () => props.show,
    (value) => {
        visible.value = value;
        toggleScroll(value);
    }
);

// Disable body scroll while modal open
function toggleScroll(state) {
    document.body.style.overflow = state ? "hidden" : "";
}

function hide() {
    visible.value = false;
    emit("update:show", false);
}

function onBeforeEnter() {
    emit("shown");
}

function onAfterLeave() {
    emit("hidden");
    toggleScroll(false);
}

// Cleanup jika component di-destroy
onUnmounted(() => {
    toggleScroll(false);
});
</script>

<template>
    <transition name="modal-fade" @before-enter="onBeforeEnter" @after-leave="onAfterLeave">
        <div v-if="visible">
            <!-- BACKDROP -->
            <transition name="backdrop-fade">
                <div class="modal-backdrop" v-if="visible"></div>
            </transition>

            <div class="modal" ref="modalRef" :id="modalId" role="dialog" :aria-labelledby="modalId + 'Label'"
                aria-modal="true" style="display: block;">

                <div class="modal-dialog" :class="dialogClass">
                    <div class="modal-content">

                        <div class="modal-header">
                            <h1 class="modal-title fs-5" :id="modalId + 'Label'">
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
/* === Smooth fade + slide === */
.modal-fade-enter-active,
.modal-fade-leave-active {
    /* transition: all 0.25s ease; */
}

.modal-fade-enter-from {
    opacity: 0;
    /* transform: translateY(-12px); */
}

.modal-fade-leave-to {
    opacity: 0;
    transform: translateY(-12px);
}

/* === Backdrop Animation === */
.backdrop-fade-enter-active,
.backdrop-fade-leave-active {
    transition: opacity 0.25s ease;
}

.backdrop-fade-enter-from,
.backdrop-fade-leave-to {
    opacity: 0;
}

/* === Backdrop Style === */
.modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.747);
    z-index: 1040;
}

/* === Modal Positioning === */
.modal {
    position: fixed;
    inset: 0;
    z-index: 1050;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}
</style>
