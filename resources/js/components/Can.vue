<script setup>
import useCan from '../composables/UseCan';
const props = defineProps({
    actions: {
        type: [String, Array],
        required: true,
    },
    all: {
        type: Boolean,
        default: false,
    },
});

const can = useCan(props.actions);
</script>
<template>
    <slot v-if="can(actions, all)" />
</template>
