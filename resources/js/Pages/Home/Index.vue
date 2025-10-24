<script setup>
import { computed } from "vue";
import { Head, usePage } from '@inertiajs/vue3';
const page = usePage();
const message = computed(() => {
    return page.props.flash.message || page.props.flash.error
});
</script>
<template>

    <Head title="Home" />
    <app-layout>
        <template #content>
            <bread-crumbs :home="false" icon="fas fa-tachometer-alt" title="Dashboard"
                :items="[{ text: 'Dashboard', url: route('home') }]" />
            <alert :variant="page.props.flash.message ? 'success' : 'danger'" :duration="10" :message="message" />
        </template>
    </app-layout>
</template>
