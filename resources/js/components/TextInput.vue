<script setup>
import { onMounted, ref, watch } from "vue";
const props = defineProps({
    name: {
        type: [String, Number, Array],
        default: "",
    },
    isValid: {
        type: Boolean,
        default: true,
    },
    isInvalid: {
        type: Boolean,
        default: true,
    },
    inputClass: {
        type: [String, Array, Object],
        default: "",
    },
    maxChar: {
        type: Number,
        default: 150,
    },
});
const modelValue = defineModel(); // default name: modelValue

/* Batasi maksimal karakter */
watch(modelValue, (newVal) => {
    if (!newVal) return;
    if (newVal.length > props.maxChar) {
        modelValue.value = newVal.slice(0, props.maxChar);
    }
});
const inputRef = ref(null);
onMounted(() => {
    if (inputRef.value && inputRef.value.hasAttribute("autofocus")) {
        inputRef.value.focus();
    }
});
defineExpose({ focus: () => inputRef.value?.focus() });
</script>

<template>
    <input v-bind="$attrs" type="text" :class="['form-control', inputClass, {
        'is-invalid': isInvalid && $page.props.errors[props.name],
        'is-valid': isValid && modelValue && !$page.props.errors[props.name]
    }]" v-model="modelValue" @input="$emit('update:modelValue', $event.target.value)" :name="props.name"
        :id="props.name" ref="inputRef" :value="modelValue" />
</template>
