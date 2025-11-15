<script setup>
import { onMounted, ref } from "vue";
const props = defineProps({
    name: {
        type: String,
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
});
const modelValue = defineModel(); // default name: modelValue
const input = ref(null);
onMounted(() => {
    if (input.value && input.value.hasAttribute("autofocus")) {
        input.value.focus();
    }
});
defineExpose({ focus: () => input.value?.focus() });
</script>

<template>
    <input type="text" :class="['form-control', {
        'is-invalid': isInvalid && $page.props.errors[props.name],
        'is-valid': isValid && modelValue && !$page.props.errors[props.name]
    }]" v-model="modelValue" @input="$emit('update:modelValue', $event.target.value)" :name="props.name"
        :id="props.name" ref="input" :value="modelValue" />
</template>
