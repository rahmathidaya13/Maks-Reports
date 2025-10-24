<script setup>
const props = defineProps({
    label: {
        type: String,
        required: false,
    },
    to: {
        type: String,
        required: false,
    },
    icon: {
        type: String,
        default: null,
    },
    inputClass: {
        type: String,
        default: '',
    },
    external: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: '',
    },
});

const navigate = () => {
    if (props.external) {
        window.open(props.to, '_blank');
    } else {
        window.location.href = props.to;
    }
}
</script>
<template>
    <button :title="title" type="button" :class="inputClass" @click="navigate">
        <i v-if="icon" :class="icon"></i>
        {{ label }}
    </button>
</template>
