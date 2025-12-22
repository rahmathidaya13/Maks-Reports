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

// 3. Deteksi Mode (Edit vs Create)
//  cek apakah item pertama punya ID
const isEditMode = computed(() => !!forms.value[0].id);
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
    forms.value.push({
        id: null,
        name: "",
    })
    nextTick(() => {
        const lastIndex = forms.value.length - 1;
        const el = formRefs.value[lastIndex];
        // Focus otomatis ke input Jam di form baru
        const input = el?.querySelector("input");
        input?.focus();
    })
}
const removeForm = (index) => {
    if (forms.value.length === 1) return;
    forms.value.splice(index, 1)
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

            <div class=" justify-content-between align-content-center d-flex mb-2">
                <button type="button" @click.prevent="goBack" class="btn btn-danger">
                    <i class="fas fa-arrow-left"></i>
                    Kembali
                </button>
                <div class="gap-1 d-flex">
                    <button title="Hapus Semua Form" v-if="forms.length > 1 && !form.processing"
                        class="btn btn-outline-danger position-relative align-items-center"
                        @click="forms = [{ name: forms[0].name ?? '', id: forms[0].id ?? '' }]"><i
                            class="fas fa-recycle"></i> Hapus
                        <span class="badge text-bg-primary">{{ forms.length }}</span>
                    </button>
                    <button @click="addForm" class="btn btn-primary btn-gradient"><i class="fas fa-plus"></i>
                        Tambah</button>
                </div>
            </div>
            <div class="row g-0 pb-3">
                <div class="col-xl-12">
                    <div class="card overflow-hidden rounded-2 bg-light">
                        <h5 class="card-header fw-bold text-uppercase p-3 text-bg-grey">
                            <i class="fas fa-info-circle me-1"></i>
                            Form Izin Akses
                        </h5>
                        <div v-if="form.processing">
                            <loader-horizontal
                                :message="props.permissions?.id ? 'Sedang memperbarui data' : 'Sedang menyimpan data'" />
                        </div>
                        <div class="card-body " :class="['blur-area', form.processing ? 'is-blurred' : '']">
                            <div :class="['form-overlay', forms.length <= 5 ? 'vh-30' : '']">
                                <form-wrapper @submit="isSubmit">
                                    <div
                                        :class="`row row-cols-xl-${forms.length > 1 ? '2' : '1'} row-cols-md-${forms.length > 1 ? '2' : '1'} row-cols-1 row-cols-sm-1 g-2`">
                                        <div class="col-auto" :ref="el => formRefs[index] = el"
                                            v-for="(field, index) in forms" :key="index">
                                            <div class="d-flex justify-content-between mt-2">
                                                <input-label class="fw-bold" :for="`permissions.${index}.name`"
                                                    :value="`Izin Akses ${index + 1}`" />
                                                <button title="Hapus Form" class="btn  text-danger"
                                                    @click.prevent="removeForm(index)" v-if="forms.length > 1">
                                                    <i class="fas fa-times fs-5"></i>
                                                </button>
                                            </div>

                                            <text-input :placeholder="`Buat izin Akses ${index + 1}`"
                                                :tabindex="index * 1 + 1" class="input-height-1" type="text"
                                                v-model="field.name" :name="`permissions.${index}.name`" />
                                            <input-error :message="form.errors[`permissions.${index}.name`]" />
                                        </div>
                                    </div>
                                </form-wrapper>
                            </div>
                        </div>
                        <div class="card-footer" :class="['blur-area', form.processing ? 'is-blurred' : '']">
                            <div class="d-grid d-xl-flex justify-content-xl-start">
                                <base-button @click="isSubmit" :loading="form.processing" class="bg-gradient px-5"
                                    :icon="isEditMode ? 'fas fa-edit' : 'fas fa-save'"
                                    :variant="isEditMode ? 'success' : 'primary'" type="button"
                                    :label="isEditMode ? 'Ubah' : 'Simpan'" />
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
</style>
