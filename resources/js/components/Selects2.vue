<script setup>
import { ref, watch, onMounted, onUnmounted, nextTick } from 'vue';
import $ from 'jquery';
import 'select2';
import 'select2/dist/css/select2.css';
import 'select2-bootstrap-5-theme/dist/select2-bootstrap-5-theme.min.css'
// 1. Props dan Emit
const props = defineProps({
    modelValue: [String, Number, Array], // Untuk v-model
    options: {
        type: Array,
        default: () => [],
    },
    settings: {
        type: Object,
        default: () => ({}),
    },
    name: {
        type: String,
        default: ''
    }
});

const emit = defineEmits(['update:modelValue', 'change']);

// 2. Referensi DOM
const selectElement = ref(null);
let $select = null; // Variabel untuk menyimpan instance jQuery Select2
// 3. Inisialisasi Select2
onMounted(async () => {
    await nextTick()
    $select = $(selectElement.value);  // Inisialisasi jQuery dan Select2
    // Apply options/data
    if (props.options.length) {
        // Select2 memiliki cara sendiri untuk memuat data jika diperlukan
        $select.html(''); // Bersihkan slot jika ada
        props.options.forEach(option => {
            // Pastikan format opsi Select2 (id, text)
            const data = { id: option.id, text: option.text || option.name };
            const newOption = new Option(data.text, data.id, false, false);
            $select.append(newOption);
        });
    }

    // Inisialisasi Select2 dengan pengaturan (settings)
    $select.select2({
        theme: 'bootstrap-5',
        ...props.settings,
    });
    // Set nilai awal
    $select.val(props.modelValue).trigger('change');

    // 4. Sinkronisasi Nilai Select2 ke Vue (v-model)
    $select.on('change', function () {
        const value = $select.val();
        emit('update:modelValue', value);
        emit('change', value);
    });
});

// 5. Watcher untuk Sinkronisasi Nilai Vue ke Select2
watch(() => props.modelValue, (newValue) => {
    // Hanya update Select2 jika nilainya berbeda,
    // untuk mencegah loop tak terbatas (infinite loop)
    if (newValue !== $select.val()) {
        $select.val(newValue).trigger('change');
    }
});


// 6. Cleanup saat Komponen Dihancurkan
onUnmounted(() => {
    if ($select && $select.data('select2')) {
        $select.off('change'); // Hapus event listener
        $select.select2('destroy'); // Hancurkan instance Select2
    }
});
</script>

<template>
    <select :class="[{
        'is-invalid': $page.props.errors[props.name],
        'is-valid': modelValue && !$page.props.errors[props.name]
    }]" class="select2-hidden-accessible form-select" ref="selectElement" :id="name + '_ids'" :name="name">
        <slot></slot>
    </select>
</template>



<style>
.select2-container--bootstrap-5 .select2-selection--single {
    height: 40px !important;
    padding: 0.375rem 0.75rem;
}

.select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
    line-height: 38px !important;
}

.select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
    height: 38px !important;
}

/* Styling untuk ukuran kecil (form-select) */
.form-select+.select2-container--bootstrap-5 .select2-selection--single {
    height: 30px !important;
}

.form-select+.select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
    line-height: 28px !important;
}

.form-select+.select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
    height: 28px !important;
}

.select2-container--bootstrap-5.select2-container--focus .select2-selection {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.25);
}

.select2-container.is-invalid .select2-selection--single {
    border-color: #dc3545 !important;
}

.is-valid+.select2-container--bootstrap-5.select2-container--focus .select2-selection,
.is-valid+.select2-container--bootstrap-5.select2-container--open .select2-selection,
.was-validated select:valid+.select2-container--bootstrap-5.select2-container--focus .select2-selection,
.was-validated select:valid+.select2-container--bootstrap-5.select2-container--open .select2-selection {
    border-color: #198754;
    box-shadow: 0 0 0 0 rgba(25, 135, 84, .25);
}

.is-invalid+.select2-container--bootstrap-5.select2-container--focus .select2-selection,
.is-invalid+.select2-container--bootstrap-5.select2-container--open .select2-selection,
.was-validated select:invalid+.select2-container--bootstrap-5.select2-container--focus .select2-selection,
.was-validated select:invalid+.select2-container--bootstrap-5.select2-container--open .select2-selection {
    border-color: #dc3545;
    box-shadow: 0 0 0 0 rgba(220, 53, 69, .25);
}

.select2-container--bootstrap-5 .select2-dropdown .select2-search .select2-search__field:focus {
    border-color: #000000;
    box-shadow: 0 0 0 0 rgba(13, 110, 253, .25);
}

.select2-container--bootstrap-5.select2-container--focus .select2-selection,
.select2-container--bootstrap-5.select2-container--open .select2-selection {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0 rgba(13, 110, 253, .25);
}
</style>
