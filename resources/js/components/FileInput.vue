<script setup>
import { computed, ref } from "vue"
import { usePage } from '@inertiajs/vue3';
const page = usePage();

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
    },
    width: {
        type: Number,
        default: 250
    },
    height: {
        type: Number,
        default: 250
    },
    objectFit: {
        type: String,
        default: 'cover'
    },
    objectPosition: {
        type: String,
        default: 'center'
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

    return `https://ui-avatars.com/api/?name=${encodeURIComponent(page.props.auth.user.name)}`
})
</script>

<template>
    <div class="d-grid">
        <div class="mb-2">
            <img :style="{
                width: props.width + 'px',
                height: props.height + 'px',
                objectFit: props.objectFit,
                objectPosition: props.objectPosition,
            }" :src="imageSource" alt="Preview" class="img-thumbnail shadow-sm img-previews" />
        </div>
        <!-- <div>{{ nameFile || nameFromPath || 'Belum ada file dipilih' }}</div> -->
        <label :for="name" class="btn btn-success rounded-0 bg-gradient align-content-center"><i class="fas fa-images"></i>
            {{ previewUrl || pathUrls ? 'Ganti Gambar' : 'Pilih Gambar' }}
            <input type="file" :multiple="multiple" class="d-none" :id="name" :accept="accept" :name="name"
                @change="onFileChange" />
        </label>
    </div>
</template>
