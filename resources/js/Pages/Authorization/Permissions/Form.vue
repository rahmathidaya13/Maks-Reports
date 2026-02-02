<script setup>
import { computed, nextTick, onMounted, ref, watch } from "vue";
import { Head, Link, router, useForm, usePage } from "@inertiajs/vue3";
import { cleanTextFormat } from "@/helpers/cleanTextFormat";

const props = defineProps({
    permissions: [Array, Object],
    default: () => []
})

// 1. Normalisasi Data ke Array
// Apapun yang dikirim controller, kita paksa jadi array di frontend
const iniatialData = Array.isArray(props.permissions)
    ? props.permissions
    : (props.permissions ? [props.permissions] : [])

// 2. Setup Form
const forms = ref(
    iniatialData.length > 0
        ? iniatialData.map(newValue => ({
            id: newValue.id ?? null,
            name: cleanTextFormat(newValue.name ?? "")
        }))
        : [{ id: null, name: "" }]
)
const form = useForm({
    permissions: forms.value,
});

// Sinkronisasi ref -> useForm
watch(forms, () => {
    form.permissions = forms.value
}, {
    deep: true
})

//  cek apakah item pertama punya ID
const isEditMode = computed(() => {
    // Mode edit aktif jika setidaknya item pertama memiliki ID
    return forms.value.length > 0 && forms.value[0].id !== null;
});
const isSubmit = () => {
    form.post(route("permissions.store.multiple"), {
        preserveScroll: true,
        onSuccess: () => {
            if (!isEditMode.value) {
                forms.value = [{ id: null, name: "" }]
            }
            form.reset();
        }
    })
};
const title = computed(() => {
    if (isEditMode.value) {
        return forms.value.length > 1
            ? `Ubah ${forms.value.length} Izin Akses`
            : "Ubah Izin Akses";
    }
    return "Buat Izin Akses Baru";
});
const icon = computed(() => isEditMode.value ? "fas fa-edit" : "fas fa-plus-square");

const breadcrumbItems = computed(() => {
    const items = [
        { text: "Daftar Izin Akses", url: route("permissions") },
    ];

    // Jika edit, tambahkan path create agar breadcrumb rapi (opsional)
    if (isEditMode.value) {
        items.push({ text: "Buat Izin Akses Baru", url: route("permissions.create") });
    }

    items.push({ text: title.value });
    return items;
});


// ─────────────────────────────────────────────
// 4. Dynamic Form Logic
// ─────────────────────────────────────────────
const formRefs = ref([])

const addForm = () => {
    if (isEditMode.value) return;
    forms.value.push({
        id: null,
        name: "",
    })
    // nextTick(() => {
    //     const lastIndex = forms.value.length - 1;
    //     const el = formRefs.value[lastIndex];
    //     // Focus otomatis ke input Jam di form baru
    //     const input = el?.querySelector("input");
    //     input?.focus();
    // })
}
const removeForm = (index) => {
    if (isEditMode.value || forms.value.length === 1) return;
    forms.value.splice(index, 1)
}
const resetBulkForm = () => {
    if (isEditMode.value) return;
    forms.value = [{ id: null, name: "" }];
}
const loaderActive = ref(null);
const goBack = () => {
    loaderActive.value?.show("Memproses...");
    router.get(route("permissions"), {}, {
        onFinish: () => loaderActive.value?.hide()
    });
}
</script>
<template>

    <Head :title="title" />
    <app-layout>
        <template #content>
            <loader-page ref="loaderActive" />

            <bread-crumbs :icon="icon" :title="title" :items="breadcrumbItems" />
            <div class="row pb-3">
                <div class="col-12">
                    <div class="card border-0 shadow rounded-4 overflow-hidden">

                        <div
                            class="card-header bg-white p-3 border-bottom d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="icon-wrapper-lg bg-info bg-opacity-10 text-info rounded-3 me-3">
                                    <i class="fas fa-user-lock fs-4"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold text-dark mb-1">Input Izin Akses</h5>
                                    <p class="text-muted small mb-0">Buat atau ubah izin Akses saat ini</p>
                                </div>
                            </div>
                            <button @click.prevent="goBack" type="button" class="btn btn-danger mb-3">
                                <i class="fas fa-arrow-left"></i>
                                Kembali
                            </button>
                        </div>


                        <div class="card-body position-relative">
                            <transition name="fade">
                                <div v-if="form.processing" class="loading-container">
                                    <div class="loading-content">
                                        <div class="spinner-modern"></div>
                                        <span class="loading-text">{{ props.role?.id ? 'Menyimpan Perubahan...' :
                                            'Menyimpan Data Baru...' }}</span>
                                    </div>
                                </div>
                            </transition>

                            <div class="gap-2 d-flex justify-content-end mb-3" v-if="!isEditMode">
                                <button v-if="forms.length > 1 && !form.processing"
                                    class="btn btn-outline-danger btn-sm d-flex align-items-center gap-2"
                                    @click="resetBulkForm">
                                    <i class="fas fa-trash-restore"></i>
                                    Hapus
                                    <span class="badge bg-danger text-white">{{ forms.length }}</span>
                                </button>

                                <button @click="addForm" class="btn btn-sm btn-primary btn-gradient shadow-sm px-4">
                                    <i class="fas fa-plus me-2"></i> Tambah
                                </button>
                            </div>

                            <div :class="['blur-area', form.processing ? 'is-blurred' : '']">
                                <form-wrapper @submit="isSubmit">
                                    <div class="row g-3">
                                        <div class="col-12" v-for="(field, index) in forms" :key="index"
                                            :ref="el => formRefs[index] = el">
                                            <div class="p-3 border rounded-3 bg-white shadow-sm position-relative">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <label
                                                        class="fw-bold text-dark small text-uppercase tracking-wider">
                                                        <i class="fas fa-key me-2 text-primary"></i>
                                                        Izin Akses {{ index + 1 }}
                                                    </label>

                                                    <button v-if="forms.length > 1 && !isEditMode" type="button"
                                                        @click.prevent="removeForm(index)"
                                                        class="btn-close small shadow-none" style="font-size: 0.7rem;">
                                                    </button>
                                                </div>

                                                <text-input input-class="input-height-2"
                                                    :placeholder="`Masukkan nama izin akses...`" type="text"
                                                    v-model="field.name" :name="`permissions.${index}.name`" />
                                                <input-error :message="form.errors[`permissions.${index}.name`]" />

                                                <input type="hidden" v-model="field.id">
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        class="d-flex justify-content-end align-items-center gap-2 mt-4 mb-3">
                                        <button @click.prevent="goBack" type="button"
                                            class="btn btn-light border bg-light px-4 rounded-3 text-muted">
                                            Batal & Kembali</button>
                                        <base-button :loading="form.processing" waiting="Memproses"
                                            class="rounded-3 shadow px-5 fw-bold"
                                            :variant="isEditMode ? 'success' : 'primary'" type="submit"
                                            :icon="isEditMode ? 'fas fa-sync' : 'fas fa-save'"
                                            :label="isEditMode ? 'Simpan Perubahan' : 'Simpan Izin Baru'" />
                                    </div>
                                </form-wrapper>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </template>
    </app-layout>

</template>
<style scoped>
.blur-area {
    transition: all 0.3s ease;
}

.blur-area.is-blurred {
    filter: blur(3px);
    pointer-events: none;
    user-select: none;
    opacity: 0.6;
}

.form-overlay {
    max-height: 60vh;
    overflow-y: auto;
    padding-right: 6px;
    position: relative;

}

.vh-30 {
    height: 30vh;
}


.fw-extrabold {
    font-weight: 800;
}

/* Chip Checkbox Custom */
.chip-checkbox {
    transition: all 0.2s ease;
    user-select: none;
}

.chip-content {
    padding: 10px 20px;
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #f0f0f0;
    color: #6c757d;
    font-size: 0.85rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.chip-checkbox:hover .chip-content {
    background: #f8f9fa;
    transform: translateY(-2px);
    border-color: #dee2e6;
}

/* State Aktif (Terpilih) */
.chip-checkbox.active .chip-content {
    background: #4f46e5;
    /* Warna Indigo Premium */
    color: #ffffff;
    border-color: #4f46e5;
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
}

.permission-grid {
    max-height: 400px;
    overflow-y: auto;
    background: #fcfcfd;
    border: 1px inset #f1f1f1;
}


/* Animasi sederhana */
.hover-scale {
    transition: transform 0.2s;
}

.hover-scale:hover {
    transform: scale(1.02);
}

/* Scrollbar halus untuk permission grid */
.permission-grid::-webkit-scrollbar {
    width: 6px;
}

.permission-grid::-webkit-scrollbar-thumb {
    background-color: #e2e8f0;
    border-radius: 10px;
}

/* Desain Overlay */
.loading-container {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.7);
    /* Transparan Putih */
    backdrop-filter: blur(2px);
    /* Efek Blur Kaca */
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 100;
    transition: all 0.3s ease;
}

.loading-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
}

/* Spinner Custom yang lebih elegan dari default Bootstrap */
.spinner-modern {
    width: 40px;
    height: 40px;
    border: 3px solid rgba(13, 110, 253, 0.1);
    border-top: 3px solid #0d6efd;
    /* Warna Primary */
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

.loading-text {
    font-size: 0.85rem;
    font-weight: 700;
    color: #0d6efd;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

/* Animasi saat loading muncul/hilang */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }

    100% {
        transform: rotate(360deg);
    }
}

.icon-wrapper-lg {
    width: 50px;
    height: 50px;
    background: #4f46e5;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 16px;
    font-size: 1.5rem;
}
</style>
