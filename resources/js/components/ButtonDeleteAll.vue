<script setup>
const props = defineProps({
    isVisible: {
        type: Boolean,
        default: false,
    },
    deleted: {
        type: Function,
        required: true,
    },
    text: {
        type: String,
        default: ''
    }
})
</script>
<template>
    <Transition name="fade">
        <button v-if="isVisible" class="btn btn-danger rounded-2" @click="deleted">
            <i class="fas fa-trash me-1"></i> {{ text }}
        </button>
    </Transition>
</template>
<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s ease, transform 0.5s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(-5px);
}

.fade-enter-to,
.fade-leave-from {
    opacity: 1;
    transform: translateY(0);
}
</style>
