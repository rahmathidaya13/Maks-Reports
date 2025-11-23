<script setup>
import { router } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';

const visible = ref(false);
const message = ref('')
let removeStartListener, removeFinishListener;

const setMessageByMEthod = (method) => {
    switch (method) {
        case "POST":
            message.value = "Menyimpan data...";
            break;
        case "PUT":
        case "PATCH":
            message.value = "Memperbarui data...";
            break;
        case "DELETE":
            message.value = "Mengahapus data...";
            break;
        default:
            message.value = "";
            break;
    }
}

onMounted(() => {
    // jalankan Listener ketika mulai
    removeStartListener = router.on('start', (event) => {
        // Cek method
        const method = event?.detail?.visit?.method?.toUpperCase() || 'GET';
        // Cek url
        const urlPath = event?.detail?.visit?.url?.pathname || "";
        // Cek url terakhir
        const lastSegment = urlPath.split("/").pop()?.toLowerCase();

        let finalMethod = method;

        // Deteksi method pada aksi hapus semua dan url terakhir
        if (method === "POST" && (lastSegment.includes("delete") || lastSegment.includes("destroy"))) {
            finalMethod = "DELETE";
        }
        setMessageByMEthod(finalMethod)
        visible.value = true;
    })
    // Hapus listener ketika selesai
    removeFinishListener = router.on('finish', () => {
        setTimeout(() => { visible.value = false; }, 200);
    })
})

onUnmounted(() => {
    if (removeStartListener) removeStartListener();
    if (removeFinishListener) removeFinishListener();
})
</script>
<template>
    <div v-if="visible"
        class="position-fixed top-0 start-0 w-100 h-100 d-flex flex-column justify-content-center align-items-center"
        style="background: rgba(0,0,0,.6); z-index: 9999;">
        <span class="loader mb-2"></span>
        <div class="text-white fw-semibold fs-5 d-block">
            {{ message }}
        </div>
    </div>
</template>
<style scoped>
.loader-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, .15);
    z-index: 9999;
    opacity: 0;
    transition: opacity .25s ease-in-out;
}

.fade-in {
    opacity: 1 !important;
}

.fade-out {
    opacity: 0 !important;
}

.loader {
    width: 48px;
    height: 48px;
    border: 5px solid #FFF;
    border-radius: 50%;
    display: inline-block;
    box-sizing: border-box;
    position: relative;
    animation: pulse 1s linear infinite;
}

.loader:after {
    content: '';
    position: absolute;
    width: 48px;
    height: 48px;
    border: 5px solid #FFF;
    border-radius: 50%;
    display: inline-block;
    box-sizing: border-box;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    animation: scaleUp 1s linear infinite;
}

@keyframes scaleUp {
    0% {
        transform: translate(-50%, -50%) scale(0)
    }

    60%,
    100% {
        transform: translate(-50%, -50%) scale(1)
    }
}

@keyframes pulse {

    0%,
    60%,
    100% {
        transform: scale(1)
    }

    80% {
        transform: scale(1.2)
    }
}
</style>
