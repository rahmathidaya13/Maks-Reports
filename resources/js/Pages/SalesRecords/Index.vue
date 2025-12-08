<script setup>
import { computed, ref } from "vue";
import { Head, usePage } from '@inertiajs/vue3';
import axios from "axios";
const page = usePage();
const message = computed(() => {
    return page.props.flash.message || page.props.flash.error
});

// const props = defineProps({
//     summaryStatusReport: Object
// })
// console.log(props.summaryStatusReport);

const loading = ref(false);
const items = ref([]);

const runScraping = async () => {
    loading.value = true;
    const res = await axios.get("/scraped/products");
    items.value = res.data.data;
    loading.value = false;
    console.log(res.data);
};
</script>
<template>

    <Head title="Halaman Catatan Penjualan" />
    <app-layout>
        <template #content>
            <bread-crumbs :home="false" icon="fas fa-sticky-note" title="Catatan Penjualan"
                :items="[{ text: 'Catatan Penjualan' }]" />
            <alert :variant="page.props.flash.message ? 'success' : 'danger'" :duration="10" :message="message" />

            <button @click="runScraping" class="btn btn-primary mb-4">
                Mulai Scraping
            </button>

        </template>
    </app-layout>
</template>