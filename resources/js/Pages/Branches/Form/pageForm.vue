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
    status_official: props.branch?.status_official ?? 'unofficial',
    phones: [],
});
const isEditMode = computed(() => {
    return !!props.branch?.branches_id
})
const addPhone = () => {
    form.phones.push({
        phone_id: null,
        phone: "",
    })
}

const removePhone = (index) => {
    form.phones.splice(index, 1);
}
onMounted(() => {
    if (isEditMode.value && props.branch?.branch_phone?.length > 0) {
        form.phones = props.branch.branch_phone.map(ph => ({
            phone_id: ph.branch_phone_id, // Simpan ID lama
            phone: ph.phone,
        }));
    } else {
        // Opsional: Jika mode create/edit kosong, otomatis tambah 1 kolom kosong biar user gak perlu klik tambah
        if (form.phones.length === 0) addPhone();
    }
})
const isSubmit = () => {
    const method = isEditMode.value ? "put" : "post";
    const url = isEditMode.value
        ? route("branch.update", props.branch?.branches_id)
        : route("branch.store");
    form[method](url, {
        onSuccess: () => {
            form.reset();
        },
    });
};
const pageMeta = computed(() => {
    if (isEditMode.value) {
        return {
            title: "Ubah Data Cabang - " + props.branch?.name,
            icon: "fas fa-edit",
            url: route('branch')
        }
    }
    return {
        title: "Buat Cabang Baru",
        icon: "fas fa-plus-square",
        url: route('branch')
    }
})
const breadcrumbItems = computed(() => {
    const items = [{ text: "Daftar Cabang",  url: route("branch")  }];
    items.push({
        text: pageMeta.value.title,
        url: null,
    });
    return items;
});



const loaderActive = ref(null);
const goBack = () => {
    loaderActive.value?.show("Memproses...");
    router.get(pageMeta.value.url, {}, {
        onFinish: () => loaderActive.value?.hide()
    });
}


const inputRef = ref(null);
onMounted(() => {
    inputRef.value.focus();
});
</script>
<template>

    <Head :title="pageMeta.title" />
    <app-layout>
        <template #content>
            <loader-page ref="loaderActive" />
            <bread-crumbs :icon="pageMeta.icon" :title="pageMeta.title" :items="breadcrumbItems" />
            <div class="row pb-3">
                <div class="col-xl-12 col-12">

                    <div class="card form-card border-0 shadow-lg rounded-4 overflow-hidden">

                        <div class="card-header bg-white p-3 border d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="icon-square-lg bg-success bg-opacity-10 text-success rounded-3 me-3">
                                    <i class="fas fa-building fs-4"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold text-dark mb-1">Form Data Cabang</h5>
                                    <p class="text-muted small mb-0">Kelola informasi kantor cabang.</p>
                                </div>
                            </div>
                            <Link @click.prevent="goBack" :href="pageMeta.url" class="btn btn-danger fw-bold border px-3">
                                <i class="fas fa-arrow-left me-2"></i> Kembali
                            </Link>
                        </div>

                        <div class="card-body p-4 position-relative">
                            <loading-overlay :show="form.processing"
                                :text="props.branch?.branches_id ? 'Memperbarui Data Cabang...' : 'Menyimpan Data Cabang...'" />
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
                                            <text-input ref="inputRef" v-model="form.name" name="name"
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
                                        <div class="mb-3">
                                            <input-label class="form-label-custom" for="status_official"
                                                value="STATUS OFFICIAL" />
                                            <div class="d-flex align-items-center gap-1">
                                                <div class="p-1 rounded-2 px-4 border"
                                                    :class="form.status_official === 'official' ? 'border-primary bg-primary bg-opacity-10 text-primary' : 'text-muted'">
                                                    <radio-box v-model:checked="form.status_official" value="official"
                                                        name="status_official">
                                                        Official
                                                    </radio-box>
                                                </div>
                                                <div class="p-1 rounded-2 px-4 border"
                                                    :class="form.status_official === 'unofficial' ? 'border-primary bg-primary bg-opacity-10 text-primary' : 'text-muted'">
                                                    <radio-box v-model:checked="form.status_official" value="unofficial"
                                                        name="is_official">
                                                        Unofficial
                                                    </radio-box>
                                                </div>

                                            </div>
                                            <input-error :message="form.errors.status_official" />

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
                                                <div v-for="(ph, index) in form.phones" :key="index"
                                                    class="mb-2 last-no-margin">
                                                    <div class="position-relative d-flex gap-1">
                                                        <i class="fas fa-phone-alt fs-7 input-icon-left"></i>

                                                        <input-number :tabindex="index + 1" v-model="ph.phone"
                                                            :name="`phones.${index}.phone`"
                                                            :placeholder="`Nomor kontak ${index + 1}`"
                                                            input-class="input-fixed-height" />

                                                        <button v-if="form.phones.length > 1" type="button"
                                                            class="btn btn-outline-danger"
                                                            @click.prevent="removePhone(index)" title="Hapus nomor ini">
                                                            <i class="fas fa-times"></i>
                                                        </button>

                                                    </div>
                                                    <input-error :message="form.errors[`phones.${index}.phone`]"
                                                        class="mt-1" />
                                                </div>
                                                <div v-if="!form.phones.length"
                                                    class="text-center text-muted small py-2">
                                                    Belum ada nomor telepon. Klik tambah.
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <input-label class="form-label-custom mb-2" for="address"
                                                value="ALAMAT LENGKAP" />
                                            <text-area name="address" v-model="form.address" :rows="5"
                                                placeholder="Jalan, Nomor, RT/RW, Kelurahan, Kecamatan..." />
                                            <input-error :message="form.errors.address" />
                                        </div>
                                    </div>

                                </div>

                                <div class="d-xl-flex d-grid justify-content-xl-end mt-4 pt-3 border-top gap-2">
                                    <button type="button" @click.prevent="goBack"
                                        class="btn btn-link btn-height-1 text-muted text-decoration-none px-3 order-last order-xl-0 ">
                                        Batal & Kembali
                                    </button>
                                    <base-button :loading="form.processing"
                                        class="btn btn-height-1 rounded-3 px-3 shadow-sm"
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
