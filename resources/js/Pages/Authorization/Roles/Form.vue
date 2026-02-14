<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { Head, Link, router, useForm, usePage } from "@inertiajs/vue3";
import { formatText } from "@/helpers/formatText";
import { formatTextFromSlug } from "@/helpers/formatTextFromSlug";
const props = defineProps({
    permissions: Array,
    role: Object
})
const form = useForm({
    name: formatTextFromSlug(props.role?.name) || "",
    permissions: props.role?.permissions?.map(p => p.name) || [],
});
const isSubmit = () => {
    if (props.role?.id) {
        form.put(route('roles.update', props.role.id), {
            onSuccess: () => {
                form.reset();
            },
            preserveScroll: true,
        })
    } else {
        // Create
        form.post(route('roles.store'), {
            onSuccess: () => {
                form.reset();
            }
        });
    }
};
const title = ref("");
const icon = ref("");
const url = ref("")
onMounted(() => {
    if (props.role && props.role.id) {
        title.value = "Ubah Data Peran " + props.role.name
        icon.value = "fas fa-edit"
        url.value = route('roles')
    } else {
        title.value = "Buat Peran Baru"
        icon.value = "fas fa-plus-square"
        url.value = route('roles')
    }
})

const breadcrumbItems = computed(() => {
    if (props.role && props.role.id) {
        return [
            { text: "Daftar Peranan", url: route("roles") },
            { text: "Buat Peran Baru", url: route("roles.create") },
            { text: title.value }
        ]
    }
    return [
        { text: "Daftar Peranan", url: route("roles") },
        { text: title.value }
    ]
})
const isChecked = (id) => {
    return form.permissions.includes(id)
}
const loaderActive = ref(null);
const goBack = () => {
    loaderActive.value?.show("Memproses...");
    router.get(url.value, {}, {
        onFinish: () => loaderActive.value?.hide()
    });
}

const isAllSelected = computed(() => {
    return form.permissions.length === props.permissions.length && props.permissions.length > 0
})
const toggleSelectAll = () => {
console.log(form.permissions.length);
    if (isAllSelected.value) {
        form.permissions = []
    } else {
        form.permissions = props.permissions.map(p => p.name)
    }
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
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">

                        <div
                            class="card-header bg-white p-3 border-bottom d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="icon-wrapper-lg bg-info bg-opacity-10 text-info rounded-3 me-3">
                                    <i class="fas fa-user-shield fs-4"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold text-dark mb-1">Input Peran pengguna</h5>
                                    <p class="text-muted small mb-0">Buat atau ubah peran pengguna saat ini</p>
                                </div>
                            </div>
                            <Link @click.prevent="goBack" :href="url" class="btn btn-danger mb-3 hover-scale">
                                <i class="fas fa-arrow-left"></i>
                                Kembali
                            </Link>
                        </div>


                        <div class="card-body position-relative">
                            <transition name="fade">
                                <div v-if="form.processing" class="loading-container">
                                    <div class="loading-content">
                                        <div class="spinner-modern"></div>
                                        <span class="loading-text">{{ props.role?.id ? 'Menyimpan Perubahan...' :
                                            'Menyimpan Peran Baru...' }}</span>
                                    </div>
                                </div>
                            </transition>
                            <div>
                                <form-wrapper @submit="isSubmit">
                                    <div class="row mb-5">
                                        <div class="col-md-4">
                                            <h6 class="fw-bold text-dark mb-1">Informasi Dasar</h6>
                                            <p class="text-muted small">Tentukan nama unik untuk peran/role pengguna.
                                            </p>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="form-floating mb-1">
                                                <input autofocus v-model="form.name" type="text"
                                                    class="form-control border bg-light rounded-3" id="name"
                                                    placeholder="Nama Role" :class="{ 'is-invalid': form.errors.name }">
                                                <label for="name" class="text-muted">Nama Peran (Contoh: Admin,
                                                    Manager)</label>
                                            </div>
                                            <input-error :message="form.errors.name" />
                                        </div>
                                    </div>

                                    <hr class="opacity-10 mb-5">

                                    <div class="row mb-5">
                                        <div class="col-md-4">
                                            <h6 class="fw-bold text-dark mb-1">Hak Akses (Permissions)</h6>
                                            <p class="text-muted small">Pilih izin apa saja yang diberikan untuk
                                                pengguna
                                                ini.</p>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="d-flex justify-content-end mb-3">
                                                <button type="button" @click="toggleSelectAll"
                                                    class="btn btn-light border" style="font-size: 0.9rem;">
                                                    <i :class="isAllSelected ? 'fas fa-times-circle text-danger' : 'fas fa-check-double text-primary'"
                                                        class="me-1"></i>
                                                    {{ isAllSelected ? 'Batalkan Semua' : 'Pilih Semua' }}
                                                </button>
                                            </div>
                                            <div
                                                class="permission-grid shadow-inner p-4 rounded-4 bg-light-subtle border border-light-subtle">
                                                <div class="d-flex flex-wrap gap-2">
                                                    <label v-for="perm in permissions" :key="perm.id"
                                                        class="chip-checkbox cursor-pointer"
                                                        :class="{ 'active': isChecked(perm.name) }">
                                                        <input multiple class="d-none" type="checkbox"
                                                            :value="perm.name" v-model="form.permissions" />
                                                        <div class="chip-content shadow-sm">
                                                            <i :class="isChecked(perm.name) ? 'fas fa-check-circle' : 'far fa-circle'"
                                                                class="me-2"></i>
                                                            <span class="fw-semibold">{{ formatTextFromSlug(perm.name)
                                                            }}</span>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                            <input-error :message="form.errors.permissions" class="mt-2" />
                                        </div>
                                    </div>

                                    <div
                                        class="d-flex justify-content-end align-items-center gap-3 mt-4 pt-4 border-top">
                                        <Link :href="url"
                                            class="btn btn-light px-4 rounded-3 text-muted fw-bold border-0">
                                            Batal</Link>
                                        <base-button :loading="form.processing" waiting="Memproses"
                                            class="rounded-3 shadow px-5 py-2 fw-bold hover-scale"
                                            :variant="props.role?.id ? 'success' : 'primary'" type="submit"
                                            :icon="props.role?.id ? 'fas fa-sync' : 'fas fa-save'"
                                            :label="props.role?.id ? 'Simpan Perubahan' : 'Simpan Peran'" />
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

/* Input Form Floating */
.form-control:focus {
    background-color: #fff !important;
    box-shadow: 0 0 0 4px rgba(98, 91, 221, 0.164) !important;
}

/* State Error Focus (Merah Tegas) */
.form-control.is-invalid:focus {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 4px rgba(220, 53, 69, 0.2) !important;
    background-color: #fff !important;
}

/* Memastikan label floating juga berwarna merah saat error */
.form-floating>.form-control.is-invalid~label {
    color: #dc3545;
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
    backdrop-filter: blur(4px);
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
