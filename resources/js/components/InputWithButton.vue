<script setup>
import { ref, onMounted } from 'vue'
const props = defineProps({
    name: {
        type: String,
        default: ''
    },
    placeholder: {
        type: String,
        default: ''
    },
    buttonText: {
        type: String,
        default: 'Submit'
    },
    disabled: {
        type: Boolean,
        default: false
    },
    inputClass: {
        type: [String, Array, Object],
        default: ''
    },
    type: {
        type: [String, Number, Boolean, Array, Object],
        default: 'text'
    }
});
const emit = defineEmits(['submit'])

const model = defineModel({
    type: String,
    required: true
})

const inputRef = ref(null)

onMounted(() => {
    if (inputRef.value?.hasAttribute('autofocus')) {
        inputRef.value.focus()
    }
})
function onSubmit() {
    if (!props.disabled && model.value) {
        emit('submit', model.value)
    }
}

defineExpose({
    focus: () => inputRef.value.focus()
})
</script>
<template>
    <div class="input-button-wrapper border rounded-3 d-flex align-items-center px-2" :class="{
        'border-danger': $page.props.errors?.[props.name],
        'border-primary': !props.disabled
    }">
        <input ref="inputRef" v-model="model" :name="props.name" :id="props.name" :type="props.type"
            :placeholder="props.placeholder || 'Ketik sesuatu...'"
            class="form-control border-0 shadow-none bg-transparent flex-grow-1" :class="inputClass"
            :disabled="props.disabled" @keyup.enter="onSubmit" />

        <button class="btn btn-primary btn-sm ms-2" type="button" :disabled="props.disabled || !model"
            @click="onSubmit">
            {{ props.buttonText }}
        </button>
    </div>
</template>
<style scoped>
.input-button-wrapper {
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.input-button-wrapper:focus-within {
    border-color: var(--bs-primary);
    box-shadow: 0 0 0 0.1rem rgba(13, 110, 253, 0.15);
}
</style>