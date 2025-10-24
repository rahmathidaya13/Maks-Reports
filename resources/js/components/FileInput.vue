<script setup>
import { computed, ref } from "vue"
const props = defineProps({
    modelValue: {
        type: [File, String, null],
        default: null
    },
    accept: {
        type: String,
        default: "image/png,image/jpeg,image/jpg" // hanya gambar
    },
    inputClass: {
        type: [Array, Object, String],
        default: ""
    },
    name: {
        type: String,
        default: "file"
    },
    multiple: {
        type: Boolean,
        default: false
    },
    pathUrls: {
        type: String,
        default: null
    }
})

const emit = defineEmits(["update:modelValue", "preview"])

// file preview
const previewUrl = ref(null)
const nameFile = ref("")
function onFileChange(e) {
    const file = e.target.files[0] || null
    emit("update:modelValue", file)
    if (file && file.type.startsWith("image/")) {
        nameFile.value = file.name
        previewUrl.value = URL.createObjectURL(file)
        emit("preview", previewUrl.value)
    } else {
        previewUrl.value = null;
        nameFile.value = null;

    }
}
const nameFromPath = computed(() => {
    if (!props.pathUrls) return null;
    const parts = props.pathUrls.split('/');
    return parts[parts.length - 1];
});
const imageSource = computed(() => {
    if (previewUrl.value) {
        return previewUrl.value
    }
    if (props.pathUrls) {
        return props.pathUrls
    }

    return '/storage/img/noimage.png';
})
</script>

<template>
    <div class="mb-2">
        <img :src="imageSource" alt="Preview" class="img-thumbnail shadow-sm img-preview" />
    </div>
    <div>{{ nameFile || nameFromPath || 'Belum ada file dipilih' }}</div>
    <label style="width: 165px" :for="name" class="btn btn-secondary"><i class="fas fa-upload"></i> Pilih File
        <input type="file" :multiple="multiple" class="d-none" :id="name" :accept="accept" :name="name"
            @change="onFileChange" />
    </label>
</template>
