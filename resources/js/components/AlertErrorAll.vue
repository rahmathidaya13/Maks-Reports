<script setup>
import { computed } from 'vue';

const props = defineProps({
    errors: {
        type: Object,
        default: () => ({})
    },
    title: {
        type: String,
        default: 'Waduh! Ada Kesalahan:'
    },
    dismissible: {
        type: Boolean,
        default: true
    }
});

// Cek apakah ada error
const hasErrors = computed(() => {
    return props.errors && Object.keys(props.errors).length > 0;
});
</script>

<template>
    <transition name="slide-fade">
        <div v-if="hasErrors"
            class="alert alert-danger d-flex align-items-start shadow-sm border-danger border-opacity-25 rounded-3 mb-4"
            :class="{ 'alert-dismissible fade show': dismissible }" role="alert">

            <div class="alert-icon me-3 mt-1">
                <i class="fas fa-exclamation-triangle fs-4 text-danger"></i>
            </div>

            <div class="flex-grow-1">
                <h6 class="alert-heading fw-bold mb-1 text-danger">{{ title }}</h6>
                <ul class="mb-0 ps-3 small text-danger text-opacity-75">
                    <li v-for="(error, key) in errors" :key="key" class="mb-1">
                        {{ error }}
                    </li>
                </ul>
            </div>

            <button v-if="dismissible" type="button" class="btn-close" data-bs-dismiss="alert"
                aria-label="Close"></button>
        </div>
    </transition>
</template>

<style scoped>
/* Animasi Masuk yang Halus */
.slide-fade-enter-active {
    transition: all 0.3s ease-out;
}

.slide-fade-leave-active {
    transition: all 0.3s cubic-bezier(1.0, 0.5, 0.8, 1.0);
}

.slide-fade-enter-from,
.slide-fade-leave-to {
    transform: translateY(-10px);
    opacity: 0;
}
</style>
