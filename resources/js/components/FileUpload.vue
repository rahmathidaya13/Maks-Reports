<script setup>
import { usePage } from '@inertiajs/vue3';
import { ref, defineProps, defineEmits, computed } from 'vue';

const page = usePage();
const props = defineProps({
    modelValue: File,
    defaultImageUrl: {
        type: String,
        default: null
    },
    inputId: {
        type: String,
        default: 'image-upload-input'
    },
    altText: {
        type: String,
        default: 'Image Preview'
    },
    accept: {
        type: String,
        default: "image/png,image/jpeg,image/jpg" // hanya gambar
    },
});

const emit = defineEmits(['update:modelValue']);

const fileInputRef = ref(null);
const previewUrl = ref(null);
const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        previewUrl.value = URL.createObjectURL(file);
        emit('update:modelValue', file);
    } else {
        previewUrl.value = null;
        emit('update:modelValue', null);
    }
}

const imageSource = computed(() => {
    if (previewUrl.value) {
        return previewUrl.value
    }
    if (props.defaultImageUrl) {
        return props.defaultImageUrl
    }
    // return '/storage/img/noimage.png';
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(page.props.auth.user.name)}`

})
</script>
<template>
    <div class="image-upload-container position-relative mx-auto">

        <img :src="imageSource" class="img-fluid image-previews" :alt="altText">

        <div class="image-upload-overlay text-white" @click="triggerFileInput">
            <label :for="inputId" class="file-upload-label">
                {{ previewUrl || defaultImageUrl ? 'Ganti Gambar' : 'Pilih Gambar' }}
            </label>
        </div>

        <input type="file" :id="inputId" name="file" ref="fileInputRef" @change="handleFileChange" hidden
            :accept="accept">
    </div>
</template>


<style scoped>
.image-upload-container {
    width: 400px;
    height: 400px;
    overflow: hidden;
    background-color: #e6e6e6;
    border: 1px solid #999999;
    border-radius: 15px;
    /* border-style: dotted; */
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 5;
}

.image-upload-container .image-previews {
    width: 100%;
    height: auto;
    display: block;
    object-fit: cover;
    object-position: center;
    border: 1px solid #c5c5c5;
    border-radius: 15px;
}

.image-upload-overlay {
    transition: background-color 0.3s;
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background-color: rgba(105, 105, 105, 0.308);
    height: 50px;
    display: flex;
    justify-content: center;
    align-items: center;
    text-align: center;
}

.image-upload-overlay:hover {
    background-color: rgba(0, 0, 0, 0.75);
    cursor: pointer;
}

.image-upload-overlay .file-upload-label {
    cursor: pointer;
    margin: 0;
    padding: 10px 15px;
    font-weight: bold;
    user-select: none;
}
</style>
