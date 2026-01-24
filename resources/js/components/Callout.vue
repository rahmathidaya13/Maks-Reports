<script setup>
import { usePage } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";
const props = defineProps({
    icon: {
        type: Object,
        default: {
            success: "fas fa-check-circle text-success",
            danger: "fas fa-times-circle text-danger",
            warning: "fas fa-exclamation-circle text-warning",
            info: "fas fa-info-circle text-info",
        },
    },
    duration: {
        type: Number,
        default: 10,
    },
})
const page = usePage();
//  Logika Deteksi Flash Message
const flashContent = computed(() => {
    const flash = page.props.flash || {};
    const errors = page.props.errors || {};

    if (flash.success) return { msg: flash.success, type: 'success' };
    if (flash.error) return { msg: flash.error, type: 'danger' };
    if (flash.warning) return { msg: flash.warning, type: 'warning' };
    if (flash.info) return { msg: flash.info, type: 'info' };
    if (flash.message) return { msg: flash.message, type: 'success' };

    if (errors.error) return { msg: errors.error, type: 'danger' };
    if (errors.message) return { msg: errors.message, type: 'danger' };
    if (errors.warning) return { msg: errors.warning, type: 'warning' };
    if (errors.info) return { msg: errors.info, type: 'info' };


    return null;
});

// Mengontrol visibilitas alert
const visible = ref(false);
const countdown = ref(props.duration);
let timer;
// Mengubah nilai visible jika message berubah
watch(
    () => flashContent.value,
    (newValue) => {
        if (timer) clearInterval(timer);
        if (newValue) {
            visible.value = true;
            countdown.value = props.duration;

            timer = setInterval(() => {
                countdown.value--
                if (countdown.value <= 0) {
                    visible.value = false
                    clearInterval(timer)
                    page.props.flash = {};
                }
            }, 1000);
        } else {
            visible.value = false
        }
    },
    { immediate: true }
);
</script>

<template>
    <transition name="fade-alert" appear>
        <div :class="['callout rounded-3 align-items-center d-flex justify-content-between', `callout-${flashContent.type}`]"
            v-if="flashContent && visible">
            <div class="d-flex align-items-center gap-2 fw-semibold">
                <i v-if="flashContent.type" :class="icon[flashContent.type]"></i>
                {{ flashContent.msg }}
            </div>
            <span class="small fw-bold">{{ countdown }}</span>
        </div>
    </transition>
</template>
<style scoped>
.fade-alert-enter-active,
.fade-alert-leave-active {
    transition: all 0.4s ease-in-out;
}

.fade-alert-enter-from,
.fade-alert-leave-to {
    opacity: 0;
    transform: translateY(-20px);
}

.fade-alert-enter-to,
.fade-alert-leave-from {
    opacity: 1;
    transform: translateY(0);
}

.callout {
    padding: 1rem;
    margin-bottom: 1rem;
    border: 1px solid transparent;
    border-left-width: 0.25rem;
    /* Pastikan z-index tinggi agar melayang di atas konten lain jika perlu */
    position: relative;
    z-index: 999;
}

.callout-success {
    background-color: #d1e7dd;
    border-color: #badbcc;
    border-left-color: #0f5132;
    color: #0f5132;
}

.callout-danger {
    background-color: #f8d7da;
    border-color: #f5c2c7;
    border-left-color: #842029;
    color: #842029;
}

.callout-warning {
    background-color: #fff3cd;
    border-color: #ffecb5;
    border-left-color: #664d03;
    color: #664d03;
}

.callout-info {
    background-color: #cff4fc;
    border-color: #b6effb;
    border-left-color: #055160;
    color: #055160;
}
</style>
