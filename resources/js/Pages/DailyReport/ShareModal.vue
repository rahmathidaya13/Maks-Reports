<script setup>
import { computed, ref } from "vue";
const props = defineProps({
    show: Boolean,
    previewText: String,
    report: Object
});
const emit = defineEmits(["update:show"]);
const close = () => {
    emit("update:show", false);
}
const copied = ref(false);

async function copyToClipboard() {
    try {
        await navigator.clipboard.writeText(props.previewText)
        copied.value = true
        setTimeout(() => copied.value = false, 2000)
    } catch {
        alert('Gagal menyalin teks')
    }
}
const sendToWhatsApp = () => {
    if (!props.previewText) return
    const encodedText = encodeURIComponent(props.previewText);
    const url = `https://wa.me/?text=${encodedText}`
    window.open(url, '_blank')
}
const formattedPreview = computed(() => {
    if (!props.report) return '';
    let text = props.previewText;
    // Convert symbol WA ke HTML
    text = text.replace(/─{3,}/g, '<hr class="my-2 border-secondary opacity-25 border-dashed">'); // Garis
    text = text.replace(/\*(.*?)\*/g, '<span class="fw-bold text-dark">$1</span>'); // Bold
    text = text.replace(/\n/g, '<br>'); // Newline
    text = text.replace(/•/g, '<span class="text-success me-1">•</span>'); // Bullet

    return text;
});
</script>
<template>
    <div class="row" v-if="props.show">
        <div class="col-xl-12 col-sm-12">
            <modal :footer="false" icon="fas fa-share-alt me-2" :show="props.show" title="Bagikan Laporan Harian"
                @closed="close">
                <template #body>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn rounded-pill btn-sm px-4 fw-bold transition-all"
                            :class="copied ? 'btn-success text-white' : 'btn-outline-secondary'"
                            @click="copyToClipboard">
                            <i class="fas" :class="copied ? 'fa-check text-white me-2' : 'fa-copy me-2'"></i>
                            {{ copied ? 'Tersalin' : 'Salin Teks' }}
                        </button>

                        <button type="button" @click.prevent="sendToWhatsApp"
                            class="btn btn-success btn-sm px-4 rounded-pill hover-scale fw-bold shadow-sm">
                            <i class="fab fa-whatsapp me-2"></i> Kirim
                        </button>
                    </div>
                    <div class="bg-white rounded-3 p-3 shadow-sm position-relative message-bubble mx-auto">
                        <div class="text-sm text-secondary font-monospace-custom" v-html="formattedPreview">
                        </div>

                        <div class="d-flex justify-content-end align-items-center mt-2 pt-1 gap-1">
                            <span class="text-muted" style="font-size: 10px;">{{ new
                                Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
                                }}</span>
                            <i class="fas fa-check-double text-primary" style="font-size: 10px;"></i>
                        </div>

                        <div class="bubble-arrow"></div>
                    </div>
                </template>

            </modal>
        </div>
    </div>
</template>

<style scoped>
/* Style Bubble Chat & Font */
.font-monospace-custom {
    font-family: 'Consolas', 'Menlo', 'Courier New', monospace;
    font-size: 1rem;
    color: #374151;
}

.message-bubble {
    border-top-left-radius: 0 !important;
}

.bubble-arrow {
    position: absolute;
    top: 0;
    left: -10px;
    width: 0;
    height: 0;
    border-top: 10px solid white;
    border-left: 10px solid transparent;
}

/* Efek " Bouncing" kecil saat sukses copy */
.btn-dark i.fa-check {
    animation: bounceIn 0.4s cubic-bezier(0.175,
            0.885, 0.32, 1.275);
}

.transition-all {
    transition: all 0.3s ease;
}

@keyframes bounceIn {
    0% {
        transform: scale(0);
        opacity: 0;
    }

    60% {
        transform:
            scale(1.2);
        opacity: 1;
    }

    100% {
        transform: scale(1);
    }
}
</style>