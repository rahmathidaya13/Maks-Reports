<script setup>
import { onMounted, ref } from "vue";
defineProps({
    name: {
        type: String,
        default: ""
    },
    rows: {
        type: Number,
        default: 0
    },
    cols: {
        type: Number,
        default: 0
    }
})
const model = defineModel({
    type: String,
    required: true,
});
const textarea = ref(null);
onMounted(() => {
    // Fokuskan ke textarea jika ada atribut autofocus
    if (textarea.value && textarea.value.hasAttribute("autofocus")) {
        textarea.value.focus();
    }
});

// Mengekspos fungsi fokus
defineExpose({ focus: () => textarea.value.focus() });
</script>

<template>
    <textarea v-model="model" :class="['form-control text-bg-grey', {
        'is-invalid': $page.props.errors[name],
        'is-valid': model && !$page.props.errors[name]
    }]" ref="textarea" :rows="rows" :cols="cols" :name="name" :id="name"></textarea>
</template>
