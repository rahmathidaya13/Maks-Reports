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

const inputField = computed(() => {
    return props.fields.filter(field => field.type !== 'button')
})
const buttonField = computed(() => {
    return props.fields.filter(field => field.type === 'button')
})
// Fungsi helper untuk handle klik button
const handleButtonClick = (field) => {
    if (field.handler && typeof field.handler === 'function') {
        // Jika ada handler khusus di objek field, jalankan
        field.handler()
    } else {
        // Jika tidak, emit event generic dengan key field tersebut
        emit('action', field.key)
    }

}
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
                <div v-for="field in inputField" :key="field.key" :class="field.col">
                    <input-label v-if="field.type !== 'slot'" :for="field.key" :value="field.label"
                        class="fw-semibold" />

                    <div v-if="field.type === 'text'" class="input-group">
                        <text-input :type="field.type" v-model="filters[field.key]" v-bind="field.props"
                            :name="field.key" />
                    </div>
                    <div v-else-if="field.type === 'date'" class="input-group">
                        <text-input :type="field.type" v-model="filters[field.key]" v-bind="field.props"
                            :name="field.key" />
                    </div>
                    <div v-else-if="field.type === 'datetime'" class="input-group">
                        <text-input :type="field.type" v-model="filters[field.key]" v-bind="field.props"
                            :name="field.key" />
                    </div>
                    <div v-else-if="field.type === 'file'" class="input-group">
                        <text-input :type="field.type" v-model="filters[field.key]" v-bind="field.props"
                            :name="field.key" />
                    </div>

                    <div v-else-if="field.type === 'select'" class="input-group">
                        <select-input v-model="filters[field.key]" v-bind="field.props" :name="field.key"
                            :options="field.options" />
                    </div>

                    <slot v-else :name="field.key" :model="filters" />
                </div>
                <div class="d-flex gap-1 mt-3">
                    <button :disabled="btn.disabled" v-for="btn in buttonField" :key="btn.key" :type="btn.type"
                        :name="btn.name" :id="btn.name" :class="['btn', btn.class || 'btn-primary']" v-bind="btn.props"
                        @click="handleButtonClick(btn)">
                        <i v-if="btn.icon" :class="[btn.icon, 'me-1']"></i>
                        {{ btn.label }}
                    </button>
                </div>

            </div>
        </div>
    </div>
</template>
