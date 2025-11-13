<script setup>
import { defineEmits } from "vue";

const emit = defineEmits(["download"]);

const props = defineProps({
  title: {
    type: String,
    default: "Unduh Laporan",
  },
  showIcon: {
    type: Boolean,
    default: true,
  },
});
</script>

<template>
  <div class="dropdown">
    <button
      class="btn btn-outline-secondary dropdown-toggle "
      type="button"
      data-bs-toggle="dropdown"
      aria-expanded="false"
    >
      <i v-if="showIcon" class="fas fa-download me-1"></i> {{ title }}
    </button>

    <ul class="dropdown-menu shadow-sm border-0">
      <li>
        <button
          class="dropdown-item fw-semibold d-flex justify-content-between align-items-center"
          @click="emit('download', 'pdf')"
        >
          PDF <i class="fas fa-file-pdf text-danger"></i>
        </button>
      </li>
      <li>
        <button
          class="dropdown-item fw-semibold d-flex justify-content-between align-items-center"
          @click="emit('download', 'excel')"
        >
          Excel <i class="fas fa-file-excel text-success"></i>
        </button>
      </li>
    </ul>
  </div>
</template>

