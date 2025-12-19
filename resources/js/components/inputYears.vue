<script setup>
const props = defineProps({
    modelValue: {
        type: [String, Number],
        required: true
    },
    name: {
        type: String,
        required: ''
    },
    inputClass: {
        type: String,
        default: ''
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
const emit = defineEmits(['update:modelValue']);

// FILTER AFTER INPUT (antisipasi paste)
const handleInput = (event) => {
    const inputValue = event.target.value;
    let cleanedValue = inputValue.replace(/[^0-9]/g, '');
    if (cleanedValue.length > 4) {
        cleanedValue = cleanedValue.slice(0, 4);
    }
    emit('update:modelValue', cleanedValue);
}
// BLOCK NON-NUMERIC BEFORE IT APPEARS
const handleKeyDown = (event) => {
    const allowKeys = [
        'Backspace', 'Tab', 'ArrowLeft', 'ArrowRight', 'Delete'
    ]
    if (allowKeys.includes(event.key)) return;
    if (!/^[0-9]$/.test(event.key)) {
        event.preventDefault();
    }
}
</script>
<template>
    <input :name="name" :id="name" type="text" :value="modelValue" @keydown="handleKeyDown" @input="handleInput"
        maxlength="4" :class="['form-control text-bg-grey', {
            'is-invalid': isInvalid && $page.props.errors[name],
            'is-valid': isValid && modelValue && !$page.props.errors[name]
        }, inputClass]" />
</template>
