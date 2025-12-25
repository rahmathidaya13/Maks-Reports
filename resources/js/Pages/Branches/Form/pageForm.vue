<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { Head, Link, router, useForm, usePage } from "@inertiajs/vue3";
import { cleanTextCapitalize } from '@/helpers/cleanTextCapitalize.js'
const props = defineProps({
    branch: {
        type: Object,
        default: () => [{}]
    },
    branchCode: {
        type: String,
        default: ""
    },
})
const form = useForm({
    name: cleanTextCapitalize(props.branch?.name) ?? '',
    branch_code: props.branch?.branch_code ?? props.branchCode,
    address: props.branch?.address ?? '',
    status: props.branch?.status ?? 'active',
    number_phone: props.branch?.branch_phone?.length
        ? [...props.branch?.branch_phone?.map(value => value.phone)]
        : [''],
});
const isSubmit = () => {
    if (props.branch?.branches_id) {
        form.put(route('branch.update', props.branch?.branches_id), {
            onSuccess: () => {
                form.reset();
            },
        })
    } else {
        // Create
        form.post(route('branch.store'), {
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
    if (props.branch && props.branch?.branches_id) {
        title.value = "Ubah Data Cabang - " + props.branch?.name
        icon.value = "fas fa-edit"
        url.value = route('branch')
    } else {
        title.value = "Buat Cabang Baru"
        icon.value = "fas fa-plus-square"
        url.value = route('branch')
    }
})
const breadcrumbItems = computed(() => {
    if (props.branch && props.branch?.branches_id) {
        return [
            { text: "Daftar Cabang", url: route("branch") },
            { text: "Buat Cabang Baru", url: route("branch.create") },
            { text: title.value }
        ]
    }
    return [
        { text: "Daftar Cabang", url: route("branch") },
        { text: title.value }
    ]
})

const loaderActive = ref(null);
const goBack = () => {
    loaderActive.value?.show("Memproses...");
    router.get(url.value, {}, {
        onFinish: () => loaderActive.value?.hide()
    });
}

const addPhone = () => {
    if (form.number_phone.length) {
        form.number_phone.push('')
    }
}

const removePhone = (index) => {
    if (form.number_phone.length > 1) {
        form.number_phone.splice(index, 1)
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
                <div class="col-xl-12 col-12">

                    <div class="card form-card border-0 shadow-lg rounded-4 overflow-hidden">

                        <div
                            class="card-header bg-white p-4 border-bottom-0 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="icon-square-lg bg-success bg-opacity-10 text-success rounded-3 me-3">
                                    <i class="fas fa-building fs-4"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold text-dark mb-1">Form Data Cabang</h5>
                                    <p class="text-muted small mb-0">Kelola informasi kantor cabang.</p>
                                </div>
                            </div>
                            <Link @click.prevent="goBack" :href="url"
                                class="btn btn-danger fw-bold border hover-scale px-3">
                                <i class="fas fa-arrow-left me-2"></i> Kembali
                            </Link>
                        </div>

                        <div v-if="form.processing"
                            class="position-absolute w-100 h-100 bg-white opacity-75 d-flex align-items-center justify-content-center"
                            style="z-index: 10">
                            <loader-horizontal :message="props.branch?.branches_id
                                ? 'Menyimpan perubahan...'
                                : 'Mendaftarkan Cabang Baru...'
                                " />
                        </div>

                        <div class="card-body p-4" :class="['blur-area', form.processing ? 'is-blurred' : '']">
                            <form-wrapper @submit="isSubmit">

                                <div class="row g-4">

                                    <div class="col-lg-5 border-end-lg">
                                        <h6 class="section-title text-success mb-3"><i
                                                class="fas fa-id-card me-2"></i>Identitas Utama</h6>

                                        <div class="mb-3">
                                            <input-label class="form-label-custom mb-2" for="branch_code"
                                                value="KODE CABANG (AUTO)" />
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0 text-muted">
                                                    <i class="fas fa-lock"></i>
                                                </span>
                                                <text-input :is-valid="false" disabled v-model="form.branch_code"
                                                    name="branch_code"
                                                    input-class="bg-light fw-bold border-start-0 text-secondary" />
                                            </div>
                                            <div class="form-text text-muted fst-italic small mt-1">
                                                <i class="fas fa-info-circle me-1"></i>Kode akan digenerate otomatis
                                                sistem.
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <input-label class="form-label-custom mb-2" for="name"
                                                value="NAMA CABANG" />
                                            <text-input autofocus v-model="form.name" name="name"
                                                placeholder="Contoh: Kantor Pusat Jakarta" />
                                            <input-error :message="form.errors.name" />
                                        </div>

                                        <div class="mb-3">
                                            <input-label class="form-label-custom mb-2" for="status"
                                                value="STATUS OPERASIONAL" />
                                            <select-input name="status" text="-- Pilih Status --" :options="[
                                                { value: 'active', label: 'Aktif / Beroperasi' },
                                                { value: 'inactive', label: 'Tidak Aktif / Tutup' }
                                            ]" v-model="form.status" />
                                            <input-error :message="form.errors.status" />
                                        </div>
                                    </div>

                                    <div class="col-lg-7 ps-lg-4">
                                        <h6 class="section-title text-primary mb-3"><i
                                                class="fas fa-address-book me-2"></i>Kontak & Lokasi</h6>

                                        <div class="mb-4">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <input-label class="form-label-custom mb-0" value="NOMOR TELEPON" />
                                                <button type="button"
                                                    class="btn btn-outline-primary btn-sm px-3 py-1 fw-bold fs-7"
                                                    @click="addPhone">
                                                    <i class="fas fa-plus me-1"></i> Tambah
                                                </button>
                                            </div>

                                            <div class="bg-light p-3 rounded-3 border">
                                                <div v-for="(phone, index) in form.number_phone" :key="index"
                                                    class="mb-2 last-no-margin">
                                                    <div class="position-relative d-flex gap-1">
                                                        <i class="fas fa-phone-alt fs-7 input-icon-left"></i>
                                                        <text-input :tabindex="index + 1"
                                                            v-model="form.number_phone[index]"
                                                            :name="`number_phone.${index}`"
                                                            :placeholder="`Nomor kontak ${index + 1}`"
                                                            input-class="input-fixed-height" />

                                                        <button v-if="form.number_phone.length > 1" type="button"
                                                            class="btn btn-outline-danger" @click="removePhone(index)"
                                                            title="Hapus nomor ini">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                    <input-error :message="form.errors[`number_phone.${index}`]"
                                                        class="mt-1" />
                                                </div>
                                                <div v-if="form.number_phone.length === 0"
                                                    class="text-center text-muted small py-2">
                                                    Belum ada nomor telepon. Klik tambah.
                                                </div>
                                            </div>
                                            <input-error :message="form.errors.number_phone" />
                                        </div>

                                        <div class="mb-3">
                                            <input-label class="form-label-custom mb-2" for="address"
                                                value="ALAMAT LENGKAP" />
                                            <div class="quill-wrapper rounded-3 border overflow-hidden">
                                                <quill-text :maxChar="500"
                                                    placeholder="Jalan, Nomor, RT/RW, Kelurahan, Kecamatan..."
                                                    v-model="form.address" height="250px" />
                                            </div>
                                            <input-error :message="form.errors.address" />
                                        </div>
                                    </div>

                                </div>

                                <div class="d-flex justify-content-end mt-4 pt-3 border-top gap-2">
                                    <button type="button" @click.prevent="goBack"
                                        class="btn btn-outline-secondary btn-height-1 border px-3">Batal</button>
                                    <base-button :loading="form.processing"
                                        class="btn btn-height-1 rounded-3 px-3 shadow-sm btn-save-animate"
                                        :icon="props.branch?.branches_id ? 'fas fa-check-circle' : 'fas fa-save'"
                                        :variant="props.branch?.branches_id ? 'success' : 'primary'" type="submit"
                                        :name="props.branch?.branches_id ? 'ubah' : 'simpan'"
                                        :label="props.branch?.branches_id ? 'Simpan Perubahan' : 'Simpan Data'" />
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
.blur-area {
    transition: all 0.3s ease;
}

.blur-area.is-blurred {
    filter: blur(3px);
    pointer-events: none;
    user-select: none;
    opacity: 0.6;
}

/* Styling Kartu & Header (Sama dengan sebelumnya) */
.form-card {
    background: #ffffff;
    transition: all 0.3s ease;
}

.icon-square-lg {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

/* Typography Label */
.form-label-custom {
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    color: #6c757d;
    text-transform: uppercase;
}

.section-title {
    font-size: 0.9rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid rgba(0, 0, 0, 0.05);
}

/* Border Pemisah Kolom (Hanya di layar besar) */
@media (min-width: 992px) {
    .border-end-lg {
        border-right: 1px solid #f0f0f0;
        padding-right: 2rem;
    }
}

/* Styling Khusus Input Group Telepon */
.last-no-margin:last-child {
    margin-bottom: 0 !important;
}


.hover-scale {
    transition: transform 0.2s;
}

.hover-scale:hover {
    transform: scale(1.05);
}

/* Quill Editor Styling Override */
/* Agar editor terlihat menyatu dengan desain input Bootstrap */
.quill-wrapper {
    background-color: #fff;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.quill-wrapper:focus-within {
    border-color: #86b7fe !important;
    box-shadow: 0 0 0 0.20rem rgba(13, 110, 253, 0.25);
}

.fs-7 {
    font-size: 0.8rem;
}

/* Button Animation */
.btn-save-animate {
    transition: transform 0.2s;
    font-weight: 600;
}

.btn-save-animate:hover {
    transform: translateY(-2px);
}

.btn-save-animate:active {
    transform: translateY(0);
}
</style>
