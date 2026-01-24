<script setup>
import { Link } from '@inertiajs/vue3';
defineProps({
    items: {
        type: Array,
        default: () => []
    },
    icon: {
        type: String,
        default: ""
    },
    title: {
        type: String,
        default: "Dashboard"
    },
    home: {
        type: Boolean,
        default: true
    },
    modeButton: {
        type: Boolean,
        default: false
    }
});
</script>
<template>
    <div class="pagetitle mb-4 py-2">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">

            <div class="d-flex align-items-center">
                <div v-if="icon"
                    class="bg-white text-primary shadow-sm rounded-3 d-flex align-items-center justify-content-center me-2"
                    style="width: 50px; height: 50px; font-size: 1.5rem;">
                    <i :class="icon"></i>
                </div>
                <div class="w-90">
                    <h4 class="fw-bold text-dark mb-0 ls-tight text-capitalize">{{ title }}</h4>
                </div>
            </div>

            <nav aria-label="breadcrumb">

                <div v-if="modeButton">
                    <slot name="modeButton" />
                </div>

                <ol v-else
                    class="breadcrumb bg-white shadow-sm rounded-pill px-4 py-2 mb-0 align-items-center m-0 border border-light">

                    <li v-if="home" class="breadcrumb-item">
                        <Link :href="route('home')"
                            class="text-decoration-none text-muted hover-primary d-flex align-items-center">
                            <i class="fas fa-home"></i>
                        </Link>
                    </li>

                    <li v-for="(item, index) in items" :key="index" class="breadcrumb-item text-capitalize"
                        :class="{ 'active': index === items.length - 1 }">

                        <span v-if="index === items.length - 1" class="fw-bold text-primary">
                            {{ item.text }}
                        </span>

                        <Link v-else :href="item.url" class="text-decoration-none text-muted hover-primary fw-medium">
                            {{ item.text }}
                        </Link>
                    </li>
                </ol>
            </nav>
        </div>
    </div>
</template>
<style scoped>
/* Transisi halus saat hover link breadcrumb */
.hover-primary {
    transition: color 0.2s ease;
}

.hover-primary:hover {
    color: var(--bs-primary) !important;
}

/* Custom separator breadcrumb menjadi panah kecil (chevron) agar lebih modern */
.breadcrumb-item+.breadcrumb-item::before {
    font-family: "Font Awesome 5 Free";
    font-weight: 900;
    content: "\f054";
    /* Code untuk fa-chevron-right */
    font-size: 0.65rem;
    color: #b0b0b0;
    padding-top: 4px;
}
</style>
