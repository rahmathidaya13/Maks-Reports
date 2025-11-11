<script setup>
import { router } from "@inertiajs/vue3";
// Menerima properti `links` dari induk
const props = defineProps({
    links: Array,
    keyword: {
        type: String,
        default: '',
    },
    routeName: {
        type: String,
        required: true,
    },
    additionalQuery: {
        type: Object,
        default: () => ({}),
    },
})

const goToPage = (url) => {
    if (!url) return

    const queryString = new URL(url).search
    const params = new URLSearchParams(queryString)
    const page = params.get('page')

    router.get(route(props.routeName), {
        page,
        keyword: props.keyword,
        ...props.additionalQuery,
    }, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    })
}
</script>
<template>
    <nav aria-label="Page navigation example">
        <ul class="pagination justify-content-end pagination-sm" >

            <li :class="{ 'page-item': true, disabled: !links[0].url }">
                <button class="page-link" @click.prevent="goToPage(links[0].url)" :disabled="!links[0].url">
                    &laquo;
                </button>
            </li>

            <li v-for="(link, index) in links.slice(1, links.length - 1)" :key="index"
                :class="['page-item', { active: link.active }]">
                <button v-if="link.url" class="page-link" @click.prevent="goToPage(link.url)" v-html="link.label" />
                <span v-else class="page-link" v-html="link.label" />
            </li>

            <li :class="{ 'page-item': true, disabled: !links[links.length - 1].url }">
                <button class="page-link" @click.prevent="goToPage(links[links.length - 1].url)"
                    :disabled="!links[links.length - 1].url">
                    &raquo;
                </button>
            </li>
        </ul>
    </nav>
</template>
