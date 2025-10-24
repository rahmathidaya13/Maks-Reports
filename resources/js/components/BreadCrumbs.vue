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
    }
});
</script>
<template>
    <div class="pagetitle">
        <nav class="d-lg-flex flex flex-wrap flex-row flex-col justify-content-between align-items-center py-lg-3 pt-3">
            <h3 class="fw-bold">
                <i v-if="icon" :class="icon"></i>
                {{ title }}
            </h3>
            <ol class="breadcrumb align-items-center small">
                <li v-if="home" class="breadcrumb-item">
                    <Link class="text-decoration-none" :href="route('home')">Home</Link>
                </li>
                <li v-for="(item, index) in items" :key="index"
                    :class="['breadcrumb-item', { 'active': index === items.length - 1 }]">
                    <template v-if="index === items.length - 1">
                        {{ item.text }}
                    </template>
                    <template v-else>
                        <Link class="text-decoration-none" :href="item.url">{{ item.text }}</Link>
                    </template>
                </li>
            </ol>
        </nav>
    </div>
</template>
