<script setup>
const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    text: {
        type: String,
        default: 'Sedang memuat data...'
    }
})
</script>
<template>
    <transition name="fade">
        <div v-if="show" class="loading-container">
            <div class="loading-content">
                <div class="spinner-modern"></div>
                <span class="loading-text">{{ text }}</span>
            </div>
        </div>
    </transition>
</template>
<style scoped>
/* Desain Overlay */
.loading-container {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    /* background: rgba(255, 255, 255, 0.7); */
    /* Transparan Putih */
    backdrop-filter: blur(5px);
    /* Efek Blur Kaca */
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 100;
    transition: all 0.3s ease;
}

.loading-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
}

/* Spinner Custom yang lebih elegan dari default Bootstrap */
.spinner-modern {
    width: 40px;
    height: 40px;
    border: 3px solid rgba(13, 110, 253, 0.1);
    border-top: 3px solid #0d6efd;
    /* Warna Primary */
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

.loading-text {
    font-size: 0.85rem;
    font-weight: 700;
    color: #0d6efd;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }

    100% {
        transform: rotate(360deg);
    }
}

/* transition */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
