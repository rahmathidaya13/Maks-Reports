<script setup>
import { computed, onMounted, ref, nextTick } from 'vue'
import { hasRole, hasPermission } from "@/composables/useAuth";

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
    },
    permissions: {
        type: [String, Array, Object],
        default: () => []
    },
    roles: {
        type: [String, Array, Object, Boolean],
        default: () => []
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

const visibleFields = computed(() => {
    return props.fields.filter(field => {

        // 1. Cek Role (Jika field mendefinisikan roles)
        // Helper hasRole sudah support array (misal: ['admin', 'dev']), jadi langsung pass saja.
        if (field.roles && !hasRole(field.roles)) {
            return false; // Sembunyikan jika role user tidak cocok
        }

        // 2. Cek Permission (Jika field mendefinisikan permission)
        if (field.permission && !hasPermission(field.permission)) {
            return false; // Sembunyikan jika permission user tidak punya
        }

        // 3. Cek Handler (Opsional, sesuai kode aslimu)
        // if (field.handler && ...) { ... }

        // Jika lolos semua pengecekan, tampilkan field ini
        return true;
    });
})
const inputRefs = ref({})
onMounted(async () => {
    await nextTick()
    const field = visibleFields.value.find(f => f.autofocus)
    if (field && inputRefs.value[field.key]) {
        inputRefs.value[field.key].focus()
    }
})
</script>
<template>
    <div class="card border-0 shadow-sm rounded-4 mb-4 filter-card">
        <div class="card-body p-4">
            <div class="d-flex align-items-center mb-3">
                <div style="width: 40px; height: 40px"
                    class="text-center bg-primary bg-opacity-10 text-primary p-2 rounded-circle me-2">
                    <i class="fas fa-sliders-h fs-6"></i>
                </div>
                <h5 class="fw-bold text-dark mb-0 text-uppercase ls-1">{{ title }}</h5>
            </div>
            <div class="row g-2 row-cols-1">
                <div v-for="field in visibleFields" :key="field.key" :class="field.col">
                    <input-label v-if="field.type !== 'slot' && field.type !== 'button'" :for="field.key"
                        :value="field.label" class="form-label-custom mb-1" />

                    <div v-if="field.type === 'text'" class="input-group">
                        <span class="input-group-text border-end-0 text-muted ps-3">
                            <i :class="field.icon"></i>
                        </span>
                        <text-input :ref="el => inputRefs[field.key] = el" :type="field.type"
                            v-model="filters[field.key]" v-bind="field.props" :name="field.key" />
                    </div>
                    <div v-else-if="field.type === 'search'" class="input-group">
                        <span class="input-group-text border-end-0 text-muted ps-3">
                            <i :class="field.icon"></i>
                        </span>
                        <text-input :ref="el => inputRefs[field.key] = el" :type="field.type"
                            v-model="filters[field.key]" v-bind="field.props" :name="field.key" />
                    </div>
                    <div v-else-if="field.type === 'date'" class="input-group">
                        <span class="input-group-text border-end-0 text-muted ps-3">
                            <i :class="field.icon"></i>
                        </span>
                        <text-input :ref="el => inputRefs[field.key] = el" :type="field.type"
                            v-model="filters[field.key]" v-bind="field.props" :name="field.key" />
                    </div>
                    <div v-else-if="field.type === 'datetime'" class="input-group">
                        <span class="input-group-text border-end-0 text-muted ps-3">
                            <i :class="field.icon"></i>
                        </span>
                        <text-input :ref="el => inputRefs[field.key] = el" :type="field.type"
                            v-model="filters[field.key]" v-bind="field.props" :name="field.key" />
                    </div>
                    <div v-else-if="field.type === 'select'" class="input-group">
                        <span class="input-group-text border-end-0 text-muted ps-3">
                            <i :class="field.icon"></i>
                        </span>
                        <select-input :ref="el => inputRefs[field.key] = el" v-model="filters[field.key]"
                            v-bind="field.props" :name="field.key" :options="field.options" />
                    </div>
                    <slot v-else :name="field.key" :model="filters" />
                </div>
                <div class="d-flex gap-1 mt-0" v-if="buttonField.length > 0">
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
/* Card Filter */
.filter-card {
    background: #ffffff;
    transition: box-shadow 0.3s ease;
}



/* Target input/select komponen Vue kamu */
.filter-card input,
.filter-card select {
    border-left: none;
    /* Hilangkan border kiri input */
    color: #495057;
    font-weight: 400;
}

/*  INVALID STATE */
.filter-card .input-group:has(.is-invalid) {
    box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
    border-radius: 0.375rem;
}

.filter-card .input-group:has(.is-invalid) .input-group-text,
.filter-card .input-group:has(.is-invalid) input,
.filter-card .input-group:has(.is-invalid) select {
    border-color: #dc3545;
}

/* Valid State */
.filter-card .input-group:has(.is-valid) {
    box-shadow: 0 0 0 0 rgba(25, 135, 84, 0.25);
    border-radius: 0.375rem;
}

.filter-card .input-group:has(.is-valid) .input-group-text,
.filter-card .input-group:has(.is-valid) input,
.filter-card .input-group:has(.is-valid) select {
    border-color: #198754;
}

/* PRIORITY HANDLING invalid > valid > focus */
.filter-card .input-group:has(.is-invalid):focus-within {
    box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.35);
}

/* Efek Focus: Border input group menyala */
.filter-card .input-group:focus-within .input-group-text,
.filter-card .input-group:focus-within input,
.filter-card .input-group:focus-within select {
    /* Warna focus bootstrap */
    border-color: #86b7fe;
    /* Hilangkan shadow default input, kita pakai border saja */
    box-shadow: none;
}

/* Label Styling (Konsisten dengan halaman lain) */
.form-label-custom {
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    color: #8898aa;
    text-transform: uppercase;
}

.filter-card .input-group:focus-within {
    box-shadow: 0 0 0 0.20rem rgba(13, 110, 253, 0.25);
    border-radius: 0.375rem;
}
</style>
