<script setup>
const props = defineProps({
    headers: {
        type: Array,
        default: () => [],
    },
    data: {
        type: Object,
        default: () => [{}],
    },
});

</script>

<template>
    <table class="table table-borderless table-striped text-nowrap">
        <thead class="table-dark">
            <tr>
                <th v-for="(header, index) in headers" :key="index">
                    {{ header.label }}
                </th>
            </tr>
        </thead>
        <tbody>
            <slot />
        </tbody>
    </table>
</template>
