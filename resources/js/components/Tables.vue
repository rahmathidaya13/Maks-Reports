<script setup>
import { computed } from 'vue';

const props = defineProps({
    headers: {
        type: [Array, Object],
        required: true,
    },
    data: {
        type: [Object, Array],
        required: true,
    },
    // Fitur Checkbox
    selectable: {
        type: Boolean,
        default: false
    },
    selection: {
        type: Array,
        default: () => []
    },
    rowKey: {
        type: String,
        default: 'id'
    },
    theadClass: {
        type: String,
        default: 'dark'
    }
});

const emit = defineEmits(['update:selection']);

// --- LOGIC CHECKBOX ---

// Cek apakah semua data sudah terpilih
const isAllSelected = computed(rows => {
    return props.data.length > 0 && props.selection.length === props.data.length
})
// Cek apakah data terpilih sebagian (untuk tampilan strip/- pada checkbox)
const isIndeterminate = computed(() => {
    return props.selection.length > 0 && props.selection.length < props.data.length;
});
// Handle klik "Select All" di header
const toggleSelectAll = (e) => {
    if (e.target.checked) {
        // Pilih semua (clone semua item ke array selection)
        emit('update:selection', [...props.data]);
    } else {
        // Kosongkan selection
        emit('update:selection', []);
    }
};
// Handle klik checkbox per baris
const toggleSelection = (item) => {
    const selectedIndex = props.selection.findIndex(s => s[props.rowKey] === item[props.rowKey]);
    let newSelection = [...props.selection];

    if (selectedIndex === -1) {
        newSelection.push(item); // Tambah jika belum ada
    } else {
        newSelection.splice(selectedIndex, 1); // Hapus jika sudah ada
    }

    emit('update:selection', newSelection);
};

// Cek apakah item tertentu sedang dicentang
const isSelected = (item) => {
    return props.selection.some(s => s[props.rowKey] === item[props.rowKey]);
};

// Helper untuk menghitung colspan footer/empty row
const totalColumns = computed(() => {
    return props.selectable ? props.headers.length + 1 : props.headers.length;
});
</script>

<template>
    <table class="table table-bordered table-striped text-nowrap">
        <thead :class="`table-${theadClass}`">
            <tr>
                <th v-if="selectable" style="width: 40px;" class="text-center">
                    <input type="checkbox" class="form-check-input" :checked="isAllSelected"
                        :indeterminate="isIndeterminate" @change="toggleSelectAll">
                </th>

                <th v-for="col in headers" :key="col.key" class="text-nowrap text-center">
                    {{ col.label }}
                </th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="(item, index) in data" :key="index" :class="{ 'table-primary': isSelected(item) }">

                <td v-if="selectable" class="text-center">
                    <input type="checkbox" class="form-check-input" :checked="isSelected(item)"
                        @change="toggleSelection(item)">
                </td>

                <td v-for="col in headers" :key="col.key">
                    <template v-if="col.key === '__index'">
                        {{
                            data.current_page ? index + 1 + (data.current_page - 1) * data.per_page : index + 1
                        }}
                    </template>
                    <template v-else>
                        <slot :name="col.key" :item="item">
                            {{ item[col.key] }}
                        </slot>
                    </template>
                </td>
            </tr>

            <tr v-if="data.length === 0">
                <td :colspan="totalColumns" class="text-center py-4 text-muted">
                    Tidak ada data tersedia.
                </td>
            </tr>
        </tbody>
        <tfoot v-if="$slots['table-footer']" class="table-light fw-bold border-top">
            <tr>
                <td :colspan="totalColumns">
                    <slot name="table-footer"></slot>
                </td>
            </tr>
        </tfoot>
    </table>
</template>
