<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { Head, Link, router, useForm, usePage } from "@inertiajs/vue3";
import { formatText } from "@/helpers/formatText";
import { formatTextFromSlug } from "@/helpers/formatTextFromSlug";
const props = defineProps({
    users: Object,
    roles: Array,
    permissions: Array,
})
const form = useForm({
    name: props.users?.name ?? "",
    email: props.users?.email ?? "",
    status: props.users?.status ?? "",
    roles: props.users?.roles[0] ?? "",
    permissions: props.users?.permissions
        ? props.users.permissions.map(p => p.id)
        : [],
})
const isSubmit = () => {
    form.put(route('users.update', props.users.id), {
        onSuccess: () => {
            form.reset();
        },
        preserveScroll: true,
    })
};

const isAllSelected = computed(() => {
    return form.permissions.length === props.permissions.length && props.permissions.length > 0
})
const toggleSelectAll = () => {
    if (isAllSelected.value) {
        form.permissions = []
    } else {
        form.permissions = props.permissions.map(p => p.id)
    }
}
// breadcrumbs
const title = ref("");
const icon = ref("");
const url = ref("")
onMounted(() => {
    if (props.users && props.users.id) {
        title.value = "Ubah Izin Pengguna " + props.users.name
        icon.value = "fas fa-edit"
        url.value = route('users')
    }
})

const breadcrumbItems = computed(() => {
    if (props.users && props.users.id) {
        return [
            { text: "Daftar Izin Pengguna", url: route("users") },
            { text: title.value }
        ]
    }
})

const roleOptions = computed(() => {
    return props.roles?.map((role) => ({
        value: role.name,
        label: formatTextFromSlug(role.name)
    }))
})
const defaultPermissionByRole = computed(() => {
    const selectedRole = props.roles.find(role => role.name === form.roles)
    return selectedRole ? selectedRole.permissions.map(p => p.name) : []
})


const userOptions = computed(() =>
    props.users.map(user => ({
        label: user.name,
        value: user.name,
    }))
)

const getBadgeClass = (permName) => {
    switch (true) {
        case /create|export|import/i.test(permName):
            return 'badge-soft-success'; // Hijau lembut
        case /edit|update/i.test(permName):
            return 'badge-soft-warning'; // Oranye/Kuning lembut
        case /delete|remove/i.test(permName):
            return 'badge-soft-danger';  // Merah lembut
        case /view|show/i.test(permName):
            return 'badge-soft-info';    // Biru muda lembut
        case /manage|access|assign|share/i.test(permName):
            return 'badge-soft-primary'; // Biru tua lembut
        default:
            return 'badge-soft-secondary'; // Abu-abu lembut
    }
}
// otomatis set permission form ke permission default saat role berubah
const initialRole = ref(form.roles)
watch(
    () => form.roles,
    (newRole) => {

        // ⛔ Jangan override saat pertama load edit
        if (newRole === initialRole.value) return

        const selectedRole = props.roles.find(role => role.name === newRole)
        form.permissions = selectedRole
            ? selectedRole.permissions.map(p => p.id)
            : []
    }
)
// cek apakah permission terpilih
const isChecked = (id) => {
    return form.permissions.includes(id)
}
// cek apakah permission berasal dari role
const isFromRole = (perm) => {
    return defaultPermissionByRole.value.includes(perm.name)
}
//
const isCustom = (perm) => {
    return isChecked(perm.id) && !isFromRole(perm)
}

const loaderActive = ref(null);
const goBack = () => {
    loaderActive.value?.show("Memproses...");
    router.get(url.value, {}, {
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
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div
                            class="card-header bg-white p-3 border-bottom d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="icon-wrapper-lg bg-info bg-opacity-10 text-info rounded-3 me-3">
                                    <i class="fas fa-user-shield fs-4"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold text-dark mb-1">Form Izin Pengguna</h5>
                                    <p class="text-muted small mb-0">Ubah izin dan peran pengguna saat ini</p>
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
                                        <span class="loading-text">{{ props.users?.id ? 'Menyimpan Perubahan...' :
                                            'Menyimpan Peran Baru...' }}</span>
                                    </div>
                                </div>
                            </transition>

                            <form-wrapper @submit="isSubmit">
                                <div class="mb-4 p-3 rounded-4 bg-light-subtle border">
                                    <input-label class="fw-bold mb-2" for="status" value="Ubah Status Pegawai" />
                                    <select-input name="status" text="Pilih Status"
                                        :options="[{ value: 'active', label: 'Aktif' }, { value: 'inactive', label: 'Non-Aktif' }]"
                                        v-model="form.status" class="form-select-lg border-0 shadow-sm" />
                                    <input-error :message="form.errors.status" />
                                </div>

                                <div class="mb-4">
                                    <div class="p-3 rounded-4 border"
                                        :class="form.roles.length > 0 ? 'bg-primary-light border-primary-subtle' : 'bg-light'">
                                        <input-label class="fw-bold mb-2" for="roles" value="Pilih Peran (Role)" />
                                        <select-input text="Pilih Peran" name="role" :options="roleOptions"
                                            v-model="form.roles" class="form-select-lg mb-3 shadow-sm border-0" />

                                        <div v-if="defaultPermissionByRole.length">
                                            <label class="text-muted small fw-bold mb-2 d-block">Izin Bawaan
                                                Role:</label>
                                            <div class="d-flex flex-wrap gap-2">
                                                <span v-for="perm in defaultPermissionByRole" :key="perm"
                                                    :class="['badge-permission shadow-sm', getBadgeClass(perm)]">
                                                    {{ formatTextFromSlug(perm) }}
                                                </span>
                                            </div>
                                        </div>
                                        <input-error :message="form.errors.roles" />
                                    </div>
                                </div>

                                <div class="mb-4">

                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <input-label class="fw-bold" value="Izin Otoritas Tambahan" />
                                        <button type="button" @click="toggleSelectAll" class="btn btn-light border"
                                            style="font-size: 0.9rem;">
                                            <i :class="isAllSelected ? 'fas fa-times-circle text-danger' : 'fas fa-check-double text-primary'"
                                                class="me-1"></i>
                                            {{ isAllSelected ? 'Batalkan Semua' : 'Pilih Semua' }}
                                        </button>
                                    </div>

                                    <div class="permission-grid p-3 rounded-4 border bg-light">
                                        <div v-for="perm in permissions" :key="perm.id" class="permission-check-item">
                                            <input type="checkbox" :id="'perm-' + perm.id" :value="perm.id"
                                                v-model="form.permissions" class="hidden-check" />
                                            <label :for="'perm-' + perm.id" class="permission-label shadow-sm ">
                                                <i
                                                    :class="isChecked(perm.id) ? 'fas fa-check-circle text-primary' : 'far fa-circle text-muted'"></i>
                                                <span>{{ formatTextFromSlug(perm.name) }}</span>
                                            </label>
                                        </div>
                                    </div>
                                    <input-error :message="form.errors.permissions" />
                                </div>

                                <div class="d-flex justify-content-end align-items-center gap-2 mt-4 pt-2 border-top">
                                    <Link @click.prevent="goBack" :href="url"
                                        class="btn btn-light px-4 rounded-3 text-muted border-0">
                                        Batal & Kembali</Link>
                                    <base-button :loading="form.processing"
                                        class="rounded-3 shadow  fw-bold hover-scale"
                                        :icon="props.users?.id ? 'fas fa-save me-2' : 'fas fa-check me-2'"
                                        :variant="props.users?.id ? 'primary' : 'success'" type="submit"
                                        :label="props.users?.id ? 'Simpan Perubahan' : 'Simpan'" />
                                </div>
                            </form-wrapper>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </app-layout>

</template>
<style scoped>

/* Background Soft */
.bg-primary-light {
    background-color: #f8faff;
}

/* Permission Grid */
.permission-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 12px;
}

/* Hidden Checkbox & Custom Label */
.hidden-check {
    display: none;
}

.permission-label {
    display: flex;
    align-items: center;
    gap: 10px;
    background: white;
    padding: 10px 14px;
    border-radius: 10px;
    cursor: pointer;
    border: 1px solid #edf2f9;
    font-size: 0.85rem;
    font-weight: 600;
    transition: all 0.2s ease;
}

.hidden-check:checked+.permission-label {
    border-color: #0d6efd;
    background-color: #e0e7ff33;
    color: #0d6efd;
}

.permission-label:hover {
    transform: translateY(-2px);
    border-color: #0d6efd;
}

/* Form Select LG */
.form-select-lg {
    font-size: 0.95rem;
    padding: 0.75rem 1rem;
    border-radius: 10px;
}


/* Animasi sederhana */
.hover-scale {
    transition: transform 0.2s;
}

.hover-scale:hover {
    transform: scale(1.02);
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

/* CSS untuk Soft Badges (Warna Pastel) */
.badge-soft-success {
    background-color: #dcfce7 !important;
    color: #15803d !important;
}

.badge-soft-warning {
    background-color: #fef9c3 !important;
    color: #a16207 !important;
}

.badge-soft-danger {
    background-color: #fee2e2 !important;
    color: #b91c1c !important;
}

.badge-soft-info {
    background-color: #e0f2fe !important;
    color: #0369a1 !important;
}

.badge-soft-primary {
    background-color: #e0e7ff !important;
    color: #4338ca !important;
}

.badge-soft-secondary {
    background-color: #f1f5f9 !important;
    color: #475569 !important;
}

/* Pastikan badge punya padding yang pas */
.badge-permission,
.badge-soft-success,
.badge-soft-warning,
.badge-soft-danger,
.badge-soft-info,
.badge-soft-primary,
.badge-soft-secondary {
    padding: 6px 12px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.75rem;
    display: inline-block;
    border: none;
}

</style>
