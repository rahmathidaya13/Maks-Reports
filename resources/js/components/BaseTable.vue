<script setup>
import { computed, ref, watch } from 'vue'
const props = defineProps({
    headers: Array,
    items: Array,
    rowKey: {
        type: String,
        default: 'id',
    },
    rowClass: {
        type: Function,
        default: () => '',
    },
    markAll: {
        type: Boolean,
        default: false,
    },
    loader: {
        type: Boolean,
        default: false,
    },
    loaderText: {
        type: String,
        default: '',
    }
})

const emit = defineEmits(['update:selected'])

const selectedRow = ref([]);
const rows = computed(() => props.items ?? []);

// Fungsi pembantu untuk mendapatkan ID baris
const getRowId = (row) => {
    return row[props.rowKey] ?? row.id;
}

// Fungsi untuk memeriksa apakah semua baris terpilih
const isAllSelected = computed(() => {
    if (rows.value.length === 0) return false;
    return rows.value.every(r => selectedRow.value.includes(getRowId(r)));
})

// Fungsi untuk memilih semua baris
const toggleAll = (evt) => {
    selectedRow.value = evt.target.checked
        ? rows.value.map(r => getRowId(r))
        : [];
}

// Reset pilihan jika data item berubah (misal pindah halaman/filter)
watch(() => props.items, () => {
    selectedRow.value = [];
}, { deep: false });


// Emit perubahan ke parent
watch(selectedRow, (val) => {
    emit('update:selected', val);
});

// Fungsi untuk menggabungkan class internal (selected) dengan class dari luar
const computeRowClass = (item) => {
    const externalClass = props.rowClass(item); // Panggil fungsi dari luar
    const isSelected = selectedRow.value.includes(getRowId(item));

    return [
        externalClass,
        isSelected ? 'row-selected' : '',
        'align-middle transition-all'
    ];
}
const visibleHeaders = computed(() => props.headers.filter(col => col.visible !== false));
</script>
<template>
    <loading-overlay :show="loader" :text="loaderText" />
    <div class="table-responsive custom-scroll">
        <table class="table table-hover align-middle text-nowrap custom-table">
            <thead class="bg-light text-uppercase text-secondary fw-bold">
                <tr>
                    <th v-if="markAll" class="text-center" style="width: 50px;">
                        <div class="form-check d-flex justify-content-center">
                            <input :disabled="!rows.length" type="checkbox"
                                class="form-check-input custom-checkbox cursor-pointer" :checked="isAllSelected"
                                @change="toggleAll($event)" />
                        </div>
                    </th>
                    <th v-for="col in visibleHeaders" :key="col.key" v-bind="col.attrs" style="font-size: 0.95rem;">
                        <slot name="header" :col="col">
                            {{ col.label }}
                        </slot>
                    </th>
                </tr>
            </thead>
            <tbody>

                <tr v-if="!rows.length">
                    <td :colspan="visibleHeaders.length + (markAll ? 1 : 0)" class="text-center py-5">
                        <slot name="empty">
                            <p class="text-muted fw-semibold">Tidak ada data</p>
                        </slot>
                    </td>
                </tr>

                <tr v-for="(item, index) in rows" :key="getRowId(item)" :class="computeRowClass(item)">

                    <td v-if="markAll" class="text-center">
                        <div class="form-check d-flex justify-content-center">
                            <input type="checkbox" class="form-check-input custom-checkbox" :value="getRowId(item)"
                                v-model="selectedRow" />
                        </div>
                    </td>

                    <slot name="row" :item="item" :index="index" :selected="selectedRow" />
                </tr>
            </tbody>
            <tfoot v-if="$slots.footer" class="bg-light border-top">
                <slot name="footer" :headers="visibleHeaders.length + (markAll ? 1 : 0)" :items="rows" />
            </tfoot>
        </table>
    </div>
</template>

<style scoped>
.transition-all {
    transition: all 0.2s ease;
}

/* Custom Checkbox Size */
.custom-checkbox {
    width: 1.1em;
    height: 1.1em;
    cursor: pointer;
}

/* Custom Scrollbar */
.custom-scroll::-webkit-scrollbar {
    height: 8px;
}

.custom-scroll::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}

/* Row Selected State */
.row-selected {
    /* Biru muda jika checkbox dicentang */
    background-color: #d7e0ec !important;
    transition: all 0.2s ease;
}

/* Custom Table Styling */
.custom-table thead th {
    letter-spacing: 0.5px;
    background-color: #f7f7f8;
    /* border-bottom: 1px solid #e9ecef; */
}

.custom-table tbody td {
    padding-top: 1rem;
    padding-bottom: 1rem;
    /* border-bottom: 1px solid #e9ecef; */
}

/* Row Hover Effect */
.custom-table tbody tr {
    transition: background-color 0.2s ease;
}

.custom-table tbody tr:hover {
    background-color: #f8faff;
    /* Biru sangat pudar saat hover */
}
</style>
