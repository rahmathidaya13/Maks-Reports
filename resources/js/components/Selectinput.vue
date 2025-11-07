<script setup>
import { computed, onMounted } from 'vue'
const props = defineProps({
    modelValue: {
        type: [String, Number,Array, null],
        default: null
    },
    text: {
        type: String,
        default: ""
    },
    name: {
        type: String,
        default: ""
    },
    options: {
        type: Array,
        default: () => []
    },
    disabled: {
        type: Boolean,
        default: false
    },
    selectClass: {
        type: [String, Array, Object],
        default: ""
    },
    isValid: {
        type: Boolean,
        default: true,
    },
    isInvalid: {
        type: Boolean,
        default: true,
    },
})
const emit = defineEmits(['update:modelValue'])
const selectedValue = computed({
    get() {
        return props.modelValue
    },
    set(val) {
        emit('update:modelValue', val)
    }
})
function formatLabel(label) {
    return String(label).replace(/\b\w/g, l => l.toUpperCase())
}
</script>
<template>
    <select v-model="selectedValue" :disabled="disabled" :class="['form-select', selectClass, {
        'is-invalid': isInvalid && $page.props.errors[name],
        'is-valid': isValid && modelValue && !$page.props.errors[name]
    }]" :name="name" :id="name">
        <option v-if="text" value="" disabled>{{ text }}</option>
        <option v-for="opt in options" :key="opt.value" :value="opt.value">
            {{ formatLabel(opt.label) }}
        </option>
    </select>
</template>
