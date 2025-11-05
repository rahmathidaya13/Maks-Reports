<script setup>
defineProps({
    variant: {
        type: String,
        default: "primary",
    },
    label: {
        type: String,
        default: "",
    },
    icon: {
        type: String,
        default: null,
    },
    name: {
        type: String,
        default: "",
    },
    loading: {
        type: Boolean,
        default: false,
    },
    waiting: {
        type: String,
        default: "Please wait...",
    },
});
</script>
<template>
    <button
        :class="['btn', `btn-${variant}`]"
        :name="name.toLowerCase()"
        :id="name.toLowerCase()"
        :disabled="loading"
    >
        <i v-if="icon" :class="[icon, 'me-2']"></i>
        <span v-if="loading">{{ waiting }}</span>
        <span v-else>
            <slot>{{ label }}</slot>
        </span>
    </button>
</template>
