<script setup>
import { onMounted, ref, watch } from 'vue';
import Quill from 'quill';
import "quill/dist/quill.core.css"; // Pastikan Anda mengimpor CSS tema yang Anda gunakan, misal snow.css jika menggunakan tema 'snow'
import "quill/dist/quill.snow.css"; // Tambahkan impor untuk tema 'snow' agar toolbar muncul

const props = defineProps({
    modelValue: {
        type: String,
        default: ''
    },
    placeholder: {
        type: String,
        default: ''
    },
    theme: {
        type: String,
        default: 'snow'
    },
    readOnly: {
        type: Boolean,
        default: false
    },
    height: {
        type: String,
        default: '200px'
    },
    maxChar: {
        type: Number,
        default: null
    }
})
const emit = defineEmits(['update:modelValue'])
const editorContainer = ref(null)
const charCount = ref(0) // ✅ indikator karakter
// Menggunakan `let` untuk `quill` agar bisa diakses di luar `onMounted` dan di `watch`
let quill = null;

onMounted(() => {
    // 1. Inisialisasi Quill
    quill = new Quill(editorContainer.value, {
        theme: props.theme,
        readOnly: props.readOnly,
        placeholder: props.placeholder,
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                [{ 'script': 'sub' }, { 'script': 'super' }],
                [{ 'indent': '-1' }, { 'indent': '+1' }],
                [{ 'direction': 'rtl' }],
                [{ 'size': ['small', 'normal', 'large', 'huge'] }],
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
            ],
        },
    });

    // 2. Set nilai awal dari modelValue
    // Pastikan `quill` ada sebelum mengakses `root`
    if (quill && props.modelValue) {
        quill.root.innerHTML = props.modelValue
    }

    // 3. Pasang event listener untuk perubahan teks
    quill.on("text-change", () => {
        const length = quill.getLength() - 1

        // 🔒 Enforce max length (frontend guard)
        if (props.maxChar && length > props.maxChar) {
            quill.deleteText(props.maxChar, length)
            length = props.maxChar
        }

        charCount.value = length
        emit('update:modelValue', quill.root.innerHTML)

    });

    // Pindahkan logic watch ke dalam onMounted atau pastikan `quill` diperiksa
    // Namun, kita biarkan `watch` di luar `onMounted` tapi pastikan memeriksa `quill`
})

// Gunakan `if (quill)` di `watch` untuk memastikan inisialisasi sudah selesai
watch(() => props.modelValue, (value) => {
    // Memastikan `quill` sudah diinisialisasi
    if (quill && value !== quill.root.innerHTML) {
        quill.root.innerHTML = value || ''
        charCount.value = quill.getLength() - 1
    }
}, { immediate: true }) // immediate: true untuk set nilai awal jika diperlukan

</script>
<template>
    <div class="quill-wrapper" :style="{ height: props.height }">
        <div ref="editorContainer" class="h-100" />
    </div>
    <small v-if="maxChar" class="text-muted d-flex justify-content-start mt-1">
        {{ charCount }}/{{ maxChar }}
    </small>
</template>
<style scoped>
/* Pastikan container editor memiliki tinggi penuh di dalam quill-wrapper */
.quill-wrapper {
    /* Atur tinggi sesuai prop, misal '200px' */
    height: v-bind(height);
    display: flex;
    /* Untuk memastikan editor mengisi container */
    flex-direction: column;
}

/* Pastikan editor dan toolbar mengisi tinggi container */
.quill-wrapper .ql-editor {
    min-height: 100%;
    /* Penting agar editor mengisi sisa tinggi */
    height: 100%;
    /* Gunakan flex: 1; pada container editor jika toolbar dipisahkan */
}

/* Tambahkan style untuk tema snow */
.quill-wrapper .ql-container {
    flex-grow: 1;
    /* Kontainer Quill akan mengisi ruang sisa setelah toolbar */
    min-height: 0;
    /* Untuk mencegah overscroll */
}

.quill-wrapper :deep(.ql-editor) {
    font-size: 18px;
    min-height: 100%;
    height: 100%;
}


</style>
