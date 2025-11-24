<script setup>
import { defineEmits } from "vue";

const emit = defineEmits(["action"]);

const props = defineProps({
    variant: {
        type: String,
        default: "secondary",
    },
    title: {
        type: String,
        default: "Unduh",
    },
    showIcon: {
        type: Boolean,
        default: true,
    },
    items: {
        type: Array,
        required: true, // Wajib diisi
        // Contoh default item untuk ilustrasi
        default: () => [
            { id: 'pdf', text: 'PDF', icon: 'fas fa-file-pdf text-danger' },
            { id: 'excel', text: 'Excel', icon: 'fas fa-file-excel text-success' },
        ],
    },
    mainIcon: {
        type: String,
        default: 'fas fa-download',
    }
});
</script>

<template>
    <div class="dropdown">
        <button :class="`btn btn-${variant}`" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i v-if="showIcon" :class="[mainIcon, 'me-1']"></i> {{ title }}
        </button>

        <ul class="dropdown-menu shadow-sm border-0">
            <li v-for="item in items" :key="item.id">
                <a href="#" class="dropdown-item fw-semibold d-flex justify-content-between align-items-center"
                    @click="emit('action', item.id)">

                    {{ item.text }}

                    <i :class="item.icon"></i>
                </a>
            </li>
        </ul>
    </div>
</template>
