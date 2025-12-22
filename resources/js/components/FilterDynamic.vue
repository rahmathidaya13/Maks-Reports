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

const emit = defineEmits(['update:modelValue', 'action'])

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
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white border-bottom py-3 px-4">
            <h5 class="card-title fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <span
                    class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 d-flex align-items-center justify-content-center"
                    style="width: 32px; height: 32px;">
                    <i class="fas fa-filter fa-sm"></i>
                </span>
                {{ title }}
            </h5>
        </div>

        <div class="card-body px-4">
            <div class="row g-2 row-cols-1">
                <div v-for="field in inputField" :key="field.key" :class="field.col">

                    <input-label v-if="field.type !== 'slot'" :for="field.key" :value="field.label"
                        class="fw-semibold mb-1 text-muted text-uppercase"
                        style="letter-spacing: 0.5px; font-size: 0.8rem;" />

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
