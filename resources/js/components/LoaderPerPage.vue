<script setup>
import { ref } from 'vue';

defineProps({
    show: Boolean,
    text: {
        type: String,
        default: 'Memuat halaman...'
    }
})

const visible = ref(false)
const text = ref("Loading")

const show = (message = 'memuat halaman...') => {
    visible.value = true
    text.value = message
}

const hide = () => {
    visible.value = false;
};
defineExpose({ show, hide })
</script>
<template>
    <div v-if="visible" class="loader-overlay d-flex flex-column align-items-center justify-content-center fade-in ">
        <span class="loader mb-2 "></span>
        <div class="text-white fw-semibold fs-5">
            {{ text }}
        </div>
    </div>
</template>
<style scoped>
.loader-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.788);
    z-index: 9999;
    opacity: 0;
    transition: opacity .25s ease-in-out;
}

.fade-in {
    opacity: 1 !important;
}

.fade-out {
    opacity: 0 !important;
}

.loader {
    width: 48px;
    height: 48px;
    border: 5px solid #FFF;
    border-radius: 50%;
    display: inline-block;
    box-sizing: border-box;
    position: relative;
    animation: pulse 1s linear infinite;
}

.loader:after {
    content: '';
    position: absolute;
    width: 48px;
    height: 48px;
    border: 5px solid #FFF;
    border-radius: 50%;
    display: inline-block;
    box-sizing: border-box;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    animation: scaleUp 1s linear infinite;
}

@keyframes scaleUp {
    0% {
        transform: translate(-50%, -50%) scale(0)
    }

    60%,
    100% {
        transform: translate(-50%, -50%) scale(1)
    }
}

@keyframes pulse {

    0%,
    60%,
    100% {
        transform: scale(1)
    }

    80% {
        transform: scale(1.2)
    }
}
</style>
