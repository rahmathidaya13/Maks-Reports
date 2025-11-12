<script setup>
import { ref, onMounted, defineExpose } from "vue";
import * as bootstrap from "bootstrap";

// Props untuk konfigurasi
const props = defineProps({
  message: {
    type: String,
    default: "Hello, world! This is a toast message.",
  },
  variant: {
    type: String,
    default: "primary", // warna default bootstrap: primary, success, danger, warning, dll
  },
  delay: {
    type: Number,
    default: 5000, // durasi tampil 5 detik
  },
});

const toastEl = ref(null);
let toastInstance = null;

// expose fungsi ke parent agar bisa dipanggil dari luar
const show = () => {
  if (toastInstance) toastInstance.show();
};

onMounted(() => {
  toastInstance = new bootstrap.Toast(toastEl.value, {
    delay: props.delay,
  });
});

defineExpose({ show });
</script>
<template>
    <div class="toast align-items-center bg-{{ variant }} border-0 fade" role="alert" aria-live="assertive"
        aria-atomic="true" ref="toastEl">
        <div class="d-flex">
            <div class="toast-body">
                <slot>{{ message }}</slot>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                aria-label="Close"></button>
        </div>
    </div>
</template>
