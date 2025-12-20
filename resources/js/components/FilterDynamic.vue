<script setup>
import { computed } from 'vue'

const props = defineProps({
    title: {
        type: String,
        default: 'Filter'
    },
    modelValue: {
        type: Object,
        required: true
    },
    fields: {
        /**
         * fields: [
         *  {
         *    key: 'keyword',
         *    label: 'Pencarian',
         *    type: 'text' | 'select' | 'slot',
         *    col: 'col-xl-6 col-md-12',
         *    props: {},
         *    options: [] // khusus select
         *  }
         * ]
         */
        type: Array,
        required: true
    }
})

const emit = defineEmits(['update:modelValue'])

const filters = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val)
})
</script>

<template>
    <div class="card overflow-hidden pb-2">
        <div class="card-header p-2 text-bg-grey bg-gradient">
            <h5 class="card-title fw-bold mb-0 text-uppercase px-2">
                <i class="fas fa-filter"></i> {{ title }}
            </h5>
        </div>

        <div class="card-body">
            <div class="row g-2 row-cols-1 justify-content-end">
                <div v-for="field in fields" :key="field.key" :class="field.col">
                    <input-label v-if="field.type !== 'slot'" :for="field.key" :value="field.label"
                        class="fw-semibold" />

                    <!-- TEXT INPUT -->
                    <div v-if="field.type === 'text'" class="input-group">
                        <text-input v-model="filters[field.key]" v-bind="field.props" :name="field.key" />
                    </div>

                    <!-- SELECT INPUT -->
                    <div v-else-if="field.type === 'select'" class="input-group">
                        <select-input v-model="filters[field.key]" v-bind="field.props" :name="field.key"
                            :options="field.options" />
                    </div>

                    <slot v-else :name="field.key" :model="filters" />
                </div>
            </div>
        </div>
    </div>
</template>
