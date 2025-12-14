<script setup>
import { Link, router } from "@inertiajs/vue3";
import { onMounted, onBeforeUnmount, computed, ref, watch } from "vue";
import moment from "moment";
import Swal from "sweetalert2";

const props = defineProps({
    headers: {
        type: Array,
        default: () => [],
    },
    data: {
        type: Object,
        default: () => [{}],
    },
    routes: {
        type: Object,
        default: () => ({
            edit: "",
            delete: "",
        }),
    },
    attributes: {
        type: [Array, Object, String],
        default: "", // untuk ambil ID unik dari row
    },

    tableClass: {
        type: [String, Array, Object],
        default: "",
    },
    variant: {
        type: String,
        default: "dark",
    },
});
const edited = (nameRoute, data) => {
    router.get(
        route(nameRoute, data[props.attributes.id]),
        {},
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

const deleted = (nameRoute, data) => {
    Swal.fire({
        title: "Hapus Data",
        text: `Yakin ingin menghapus data ini ${data[props.attributes.name] ?? ""}?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Ya, Hapus!",
        cancelButtonText: "Batal",
        customClass: {
            popup: "swal2-popup-minimal",
            title: "swal2-title-minimal",
            confirmButton: "swal2-confirm-minimal",
            content: "swal2-content-minimal",
            cancelButton: "swal2-cancel-minimal",
        },
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route(nameRoute, data[props.attributes.id]), {
                preserveScroll: true,
                preserveState: true,
            });
        }
    });
};

const emit = defineEmits(["update:selected"]);
const selected = ref([]);
const isAllSelected = computed(() => {
    const rows = props.data?.data ?? [];
    return rows.length > 0 && selected.value.length === rows.length;
});

// --- EVENT ---
function toggleSelectAll(event) {
    if (event.target.checked) {
        selected.value = props.data.data.map((row) => row[props.attributes.id]);
    } else {
        selected.value = [];
    }
}
// Tambahkan emit event agar parent bisa tahu baris mana yang terpilih.
watch(selected, (val) => {
    emit("update:selected", val);
});
</script>

<template>
    <table class="table table-bordered table-striped text-nowrap " :class="tableClass">
        <thead :class="`table-${variant}`">
            <tr>
                <th>
                    <div class="form-check d-flex justify-content-center gap-2">
                        <input type="checkbox" class="form-check-input" @change="toggleSelectAll($event)"
                            :checked="isAllSelected" />
                    </div>
                </th>
                <th class="text-center" v-for="(header, index) in headers" :key="index">
                    {{ header.label }}
                </th>
                <th class="text-center" v-if="routes.edit && routes.delete"></th>
            </tr>
        </thead>
        <tbody>
            <tr v-if="!data.data.length">
                <td :colspan="headers.length + 2" class="text-center text-muted">
                    Tidak ada data ditemukan
                </td>
            </tr>
            <tr :id="row[props.attributes.id]" v-for="(row, rowIndex) in data.data" :key="rowIndex">
                <td class="text-center align-middle">
                    <div class="form-check d-flex justify-content-center gap-2">
                        <input type="checkbox" class="form-check-input" :name="row[props.attributes.id]"
                            :id="row[props.attributes.id]" :value="row[props.attributes.id]" v-model="selected" />
                    </div>
                </td>
                <td class="text-center align-middle" v-for="(header, colIndex) in headers" :key="colIndex">
                    <template v-if="header.key === '__index'">
                        {{
                            rowIndex +
                            1 +
                            (data.current_page - 1) * data.per_page
                        }}
                    </template>
                    <template v-else>
                        <slot name="cell" :row="row" :keyName="header.key">
                            {{ row[header.key] ?? "-" }}
                        </slot>
                    </template>
                </td>
                <td class="text-center align-middle" v-if="routes.edit && routes.delete">
                    <div class="dropdown dropstart">
                        <button class="btn btn-outline-secondary" type="button" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="fas fa-cog"></i>
                        </button>
                        <ul class="dropdown-menu overflow-hidden">
                            <div class="dropdown-header">Aksi</div>
                            <li>
                                <button class="dropdown-item fw-bold" @click.prevent="edited(routes.edit, row)">
                                    <i class="fas fa-edit"></i> Ubah
                                </button>
                            </li>
                            <li>
                                <button class="dropdown-item text-danger fw-bold"
                                    @click.prevent="deleted(routes.delete, row)">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </li>
                        </ul>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</template>
