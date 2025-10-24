<script setup>
const props = defineProps({
    // Properti untuk nilai angka (diikat dengan v-model:modelValue)
    modelValue: {
        type: [Number, String],
        default: 0
    },
    unitValue: {
        type: String,
        default: ''
    },
    placeholder: {
        type: String,
        default: ''
    },
    // Array unit yang diterima
    units: {
        type: Array,
        required: true,
        default: () => []
    },
    inputClass: {
        type: [String, Array, Object],
        default: ''
    },
    selectClass: {
        type: [String, Array, Object],
        default: ''
    },
    size: {
        type: String,
        default: ''
    },
    isValid: {
        type: Boolean,
        default: true,
    },
    isInvalid: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['update:modelValue', 'update:unitValue']);

const updateQuantity = (event) => {
    let rawValue = event.target.value;
    // 1. Hapus semua karakter yang bukan angka, koma, atau titik
    let filteredValue = rawValue.replace(/,/g, '.');
    filteredValue = filteredValue.replace(/[^0-9.]/g, '');
    // 2. Tentukan pemisah desimal yang dominan dan bersihkan pemisah ganda
    let decimalSeparator = (filteredValue.match(/,/g) || []).length >= (filteredValue.match(/\./g) || []).length ? ',' : '.';

    // 3. Pastikan HANYA ada satu titik desimal
    const parts = filteredValue.split('.');
    if (parts.length > 2) {
        // Jika ada lebih dari satu titik, pertahankan yang pertama dan hapus sisanya
        filteredValue = parts[0] + '.' + parts.slice(1).join('');
    }

    // 4. Update nilai input DOM agar pengguna melihat format yang sudah bersih
    event.target.value = filteredValue;

    // 5. Emit nilai yang difilter (sebagai string)
    emit('update:modelValue', filteredValue);
};

const updateUnit = (event) => {
    emit('update:unitValue', event.target.value);
};
</script>

<template>
    <div class="input-group">
        <input :class="[size ? 'form-control-' + size : '', inputClass, {
            'is-invalid': isInvalid && $page.props.errors[props.name],
            'is-valid': isValid && modelValue && !$page.props.errors[props.name]
        }]" type="text" class="form-control" :value="modelValue" @input="updateQuantity" :placeholder="placeholder" />
        <select :class="[selectClass, {
            'is-invalid': $page.props.errors[props.name],
            'is-valid': unitValue && !$page.props.errors[props.name]
        }]" class="form-select w-auto" :value="unitValue" @change="updateUnit">
            <option v-for="unit in units" :key="unit.value" :value="unit.value">
                {{ unit.text }}
            </option>
        </select>
    </div>
</template>


<style scoped>
/* Opsional: Sesuaikan style Bootstrap agar terlihat lebih compact */
.input-group>.form-select {
    flex-grow: 0;
}
</style>
