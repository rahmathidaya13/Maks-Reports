<script setup>
import { computed, onMounted, ref } from 'vue'

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
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 custom-filter-card">
        <div class="card-body p-4">
            <div class="d-flex align-items-center mb-3">
                <div style="width: 40px; height: 40px"
                    class="text-center bg-primary bg-opacity-10 text-primary p-2 rounded-circle me-2">
                    <i class="fas fa-sliders-h fs-6"></i>
                </div>
                <h5 class="fw-bold text-dark mb-0 text-uppercase ls-1"> {{ title }}</h5>
            </div>
            <div class="row g-2 row-cols-1">
                <div v-for="field in inputField" :key="field.key" :class="field.col">

                    <input-label v-if="field.type !== 'slot'" :for="field.key" :value="field.label"
                        class="fw-semibold mb-1 text-muted text-uppercase"
                        style="letter-spacing: 0.5px; font-size: 0.8rem;" />

                    <div v-if="field.type === 'text'" class="input-group">
                        <text-input :type="field.type" v-model="filters[field.key]" v-bind="field.props"
                            :name="field.key" />
                    </div>
                    <div v-else-if="field.type === 'search'" class="input-group">
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
<style scoped>
.custom-filter-card {
    background: #ffffff;
    border-radius: 12px;
    /* Sudut lebih bulat */
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.04);
    /* Shadow sangat halus */
    transition: all 0.3s ease;
}

.custom-filter-card:hover {
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
    /* Efek naik saat hover */
}

/* Styling Input & Select agar seragam */
/* Note: CSS ini menargetkan elemen input yang dirender oleh komponen Vue kamu */
.custom-filter-card input,
.custom-filter-card select,
.custom-filter-card .input-group-text {
    border-color: #e9ecef;
    padding-top: 0.6rem;
    padding-bottom: 0.6rem;
    font-size: 0.9rem;
    border: none;
}

/* Efek saat input diklik (Fokus) */
.custom-filter-card input:focus,
.custom-filter-card select:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 4px rgba(13, 109, 253, 0.185);
    /* Ring fokus yang lembut */
    background-color: #fff;
}
</style>
