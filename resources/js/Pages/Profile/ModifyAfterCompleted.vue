<script setup>
import { useForm, Head, Link, usePage, router } from '@inertiajs/vue3';
import moment from 'moment';
import { computed, nextTick, ref, watch } from 'vue';
import { hasRole, hasPermission } from "@/composables/useAuth";
const props = defineProps({
    profile: Object,
    branches: Object,
    jobTitle: Object
})

import { useConfirm } from "@/helpers/useConfirm.js"
const confirm = useConfirm(); // alert

const originalBranchId = props.profile?.branches_id; // ID cabang awal sebelum diganti
const form = useForm({
    name: props.profile.user.name ?? "",
    email: props.profile.user.email ?? "",
    employee_id_number: props.profile.employee_id_number ?? "",
    jobTitle: props.profile.job_title_id ?? "",
    branches: originalBranchId ?? "",
    branch_code: "",
    date_of_entry: props.profile.date_of_entry ?? "",
    birthdate: props.profile.birthdate ?? "",
    education: props.profile.education ?? "",
    gender: props.profile.gender ?? "",
    number_phone: props.profile.number_phone ?? "",
    address: props.profile.address ?? "",
    images: null,

    national_id_number: props.profile?.national_id_number ?? "",
    employee_status: props.profile?.employee_status ?? "",
    religion: props.profile?.religion ?? "",
    birthplace: props.profile?.birthplace ?? "",
    major: props.profile?.major ?? "",
    entry_year: props.profile?.entry_year ?? "",
    graduation_year: props.profile?.graduation_year ?? "",
});


const showCodeInput = ref(false); // State untuk menampilkan form input kode
const nameBranch = ref('');
const branchOptions = computed(() => {
    return props.branches?.map(item => {
        return {
            value: item.branches_id,
            label: (item.name).replace(/\b\w/g, l => l.toUpperCase())
        }
    })
})
// Fungsi untuk menangani perubahan pada form branches
watch(() => form.branches, (newValue, oldValue) => {
    if (newValue === originalBranchId) {
        showCodeInput.value = false;
        form.branch_code = '';
        return;
    }
    if (newValue !== originalBranchId) {
        const branch = props.branches.find(item => item.branches_id === newValue);
        showCodeInput.value = true;
        nameBranch.value = branch.name;
    } else {
        // Jika batal, kembalikan pilihan dropdown dan sembunyikan input
        showCodeInput.value = false;
        form.branch_code = '';
        form.branches = oldValue;
    }
})
const jobTitleOptions = computed(() => {
    return props.jobTitle?.map(item => {
        return {
            value: item.job_title_id,
            label: (item.title).replace(/\b\w/g, l => l.toUpperCase())
        }
    })
})

const submit = () => {
    form.post(route("profile.after.update", props.profile?.users_id), {
        _method: 'put',
        preserveScroll: true,
        replace: true,
        preserveState: true,
        forceFormData: true,
    });
};

const previewImage = ref(null)

// menerima event preview dari child
function onPreview(url) {
    previewImage.value = url
}
const loaderActive = ref(null);
const goToBack = (id) => {
    loaderActive.value?.show("Memproses...");
    router.get(route('profile.detail', id), {}, {
        onFinish: () => loaderActive.value?.hide()
    });
}



</script>
<template>

    <Head title="Halaman Ubah Profil" />
    <app-layout>
        <template #content>

            <bread-crumbs @action="goToBack(props.profile?.users_id)" :home="false" icon="fas fa-user-edit"
                title="Ubah Profil" :items="[
                    { text: 'Detail Profil', url: route('profile.detail', props.profile?.users_id) },
                    { text: 'Ubah Profil' },
                ]" />
            <loader-page ref="loaderActive" />
            <callout />

            <div class="row pb-4">
                <div class="col-12">
                    <div
                        class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
                        <div class="d-flex align-items-center">
                            <div class="icon-square-lg bg-primary text-white rounded-circle shadow-sm me-3">
                                <i class="fas fa-user-circle fs-3"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold text-dark mb-1 ls-tight">Profil Saya</h4>
                                <p class="text-muted small mb-0">Kelola informasi pribadi dan data kepegawaian Anda.</p>
                            </div>
                        </div>
                        <Link @click.prevent="goToBack(props.profile?.users_id)" :href="props.profile?.users_id"
                            class="btn btn-danger btn-sm ms-auto px-3 fw-bold">
                            <i class="fas fa-arrow-left me-2"></i> Kembali
                        </Link>
                    </div>

                    <div v-if="form.processing"
                        class="position-absolute w-100 h-100 bg-white opacity-75 d-flex align-items-center justify-content-center"
                        style="z-index: 10;">
                        <loader-horizontal message="Memperbarui Profil" />
                    </div>

                    <form-wrapper @submit="submit">
                        <div class="row g-4">

                            <div class="col-xl-4 col-lg-5">
                                <div class="card profile-card border-0 shadow-sm rounded-4 text-center overflow-hidden">
                                    <div class="card-body p-4">
                                        <h6 class="text-uppercase fw-bold text-muted small mb-3">Foto Profil</h6>

                                        <div class="avatar-upload-container mb-3 mx-auto">
                                            <file-input :width="200" :height="200"
                                                :pathUrls="props.profile?.images ? '/storage/' + props.profile?.images : ''"
                                                objectFit="cover" @preview="onPreview" name="images"
                                                v-model="form.images" class="rounded-circle shadow-sm" />
                                        </div>
                                        <input-error :message="form.errors.images" />

                                        <h5 class="fw-bold text-dark mt-3 mb-2">{{ form.name || 'Nama Pengguna' }}</h5>
                                        <span
                                            class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">
                                            {{ form.national_id_number || 'NIK Belum Diisi' }}
                                        </span>
                                    </div>
                                    <div class="card-footer bg-light p-3 border-0">
                                        <small class="text-muted fst-italic">
                                            <i class="fas fa-info-circle me-1"></i>Pastikan foto/gambar terlihat jelas.
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-8 col-lg-7">
                                <div class="card profile-card border-0 shadow-sm rounded-4">
                                    <div class="card-body p-4"
                                        :class="['blur-area', form.processing ? 'is-blurred' : '']">

                                        <div class="form-section mb-5">
                                            <h6 class="section-title text-primary mb-4">
                                                <i class="fas fa-address-card me-2"></i>Identitas Diri
                                            </h6>
                                            <div class="row g-3">
                                                <div class="col-md-12">
                                                    <input-label class="form-label-custom" for="name"
                                                        value="NAMA LENGKAP" />
                                                    <text-input type="text" name="name" v-model="form.name" />
                                                    <input-error :message="form.errors.name" />
                                                </div>

                                                <div class="col-md-6">
                                                    <input-label class="form-label-custom" for="birthplace"
                                                        value="TEMPAT LAHIR" />
                                                    <text-input type="text" name="birthplace"
                                                        v-model="form.birthplace" />
                                                    <input-error :message="form.errors.birthplace" />
                                                </div>

                                                <div class="col-md-6">
                                                    <input-label class="form-label-custom" for="birthdate"
                                                        value="TANGGAL LAHIR" />
                                                    <text-input type="date" name="birthdate" v-model="form.birthdate" />
                                                    <input-error :message="form.errors.birthdate" />
                                                </div>

                                                <div class="col-md-6">
                                                    <input-label class="form-label-custom" for="gender"
                                                        value="JENIS KELAMIN" />
                                                    <select-input text="-- Pilih --" name="gender" v-model="form.gender"
                                                        :options="[
                                                            { value: 'male', label: 'Laki-laki' },
                                                            { value: 'female', label: 'Perempuan' },
                                                        ]" />
                                                    <input-error :message="form.errors.gender" />
                                                </div>

                                                <div class="col-md-6">
                                                    <input-label class="form-label-custom" for="religion"
                                                        value="AGAMA" />
                                                    <select-input text="-- Pilih --" name="religion"
                                                        v-model="form.religion" :options="[
                                                            { value: 'islam', label: 'Islam' },
                                                            { value: 'kristen', label: 'Kristen' },
                                                            { value: 'katolik', label: 'Katolik' },
                                                            { value: 'hindu', label: 'Hindu' },
                                                            { value: 'budha', label: 'Budha' },
                                                            { value: 'konghucu', label: 'Konghucu' },
                                                        ]" />
                                                    <input-error :message="form.errors.religion" />
                                                </div>

                                                <div class="col-md-6">
                                                    <input-label class="form-label-custom" for="national_id_number"
                                                        value="NOMOR NIK" />
                                                    <text-input placeholder="16 digit NIK" type="text"
                                                        name="national_id_number" v-model="form.national_id_number" />
                                                    <input-error :message="form.errors.national_id_number" />
                                                </div>

                                                <div class="col-md-6">
                                                    <input-label class="form-label-custom" for="number_phone"
                                                        value="NO. HANDPHONE" />
                                                    <input-number placeholder="08xxx" name="number_phone"
                                                        v-model="form.number_phone" />
                                                    <input-error :message="form.errors.number_phone" />
                                                </div>

                                                <div class="col-12 col-md-12">
                                                    <input-label class="form-label-custom" for="address"
                                                        value="ALAMAT LENGKAP" />
                                                    <text-area :rows="4" name="address" v-model="form.address"
                                                        placeholder="Jalan, RT/RW, Kelurahan..."
                                                        input-class="bg-light" />
                                                    <input-error :message="form.errors.address" />
                                                </div>
                                            </div>
                                        </div>

                                        <hr class="border-light my-4">

                                        <div class="form-section mb-5">
                                            <h6 class="section-title text-info mb-4">
                                                <i class="fas fa-graduation-cap me-2"></i>Riwayat Pendidikan
                                            </h6>
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <input-label class="form-label-custom" for="education"
                                                        value="JENJANG" />
                                                    <select-input text="-- Pilih Jenjang --" name="education"
                                                        v-model="form.education" :options="[
                                                            { value: 'SD', label: 'SD/Sederajat' },
                                                            { value: 'SMP', label: 'SMP/Sederajat' },
                                                            { value: 'SMA', label: 'SMA/SMK/Sederajat' },
                                                            { value: 'D1/Diploma', label: 'D1 - Diploma' },
                                                            { value: 'D2/Diploma', label: 'D2 - Diploma' },
                                                            { value: 'D3/Diploma', label: 'D3 - Diploma' },
                                                            { value: 'D4/Diploma', label: 'D4 - Diploma' },
                                                            { value: 'S1/Sarjana', label: 'S1 - Sarjana' },
                                                            { value: 'S2/Magister', label: 'S2 - Magister' },
                                                            { value: 'S3/Doktor', label: 'S3 - Doktor' },
                                                        ]" />
                                                    <input-error :message="form.errors.education" />
                                                </div>
                                                <div class="col-md-6">
                                                    <input-label class="form-label-custom" for="major"
                                                        value="JURUSAN" />
                                                    <text-input type="text" name="major" v-model="form.major"
                                                        placeholder="Contoh: Teknik Informatika" />
                                                    <input-error :message="form.errors.major" />
                                                </div>
                                                <div class="col-md-6">
                                                    <input-label class="form-label-custom" for="entry_year"
                                                        value="TAHUN MASUK" />
                                                    <input-years :placeholder="'YYYY'" name="entry_year"
                                                        v-model="form.entry_year" />
                                                    <input-error :message="form.errors.entry_year" />
                                                </div>
                                                <div class="col-md-6">
                                                    <input-label class="form-label-custom" for="graduation_year"
                                                        value="TAHUN LULUS" />
                                                    <input-years :placeholder="'YYYY'" name="graduation_year"
                                                        v-model="form.graduation_year" />
                                                    <input-error :message="form.errors.graduation_year" />
                                                </div>
                                            </div>
                                        </div>

                                        <hr class="border-light my-4">

                                        <div class="form-section">
                                            <h6 class="section-title text-success mb-4">
                                                <i class="fas fa-briefcase me-2"></i>Data Kepegawaian
                                            </h6>
                                            <div class="p-3 rounded-3 bg-light border border-light-subtle">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <input-label class="form-label-custom" for="employee_id_number"
                                                            value="ID PEGAWAI" />
                                                        <text-input type="text" name="employee_id_number"
                                                            v-model="form.employee_id_number"
                                                            placeholder="1234567890" />
                                                        <input-error :message="form.errors.employee_id_number" />
                                                    </div>
                                                    <div class="col-md-6">
                                                        <input-label class="form-label-custom" for="date_of_entry"
                                                            value="TANGGAL BERGABUNG" />
                                                        <text-input type="date" name="date_of_entry"
                                                            v-model="form.date_of_entry" />
                                                        <input-error :message="form.errors.date_of_entry" />
                                                    </div>
                                                    <div class="col-md-6">
                                                        <input-label class="form-label-custom" for="jobTitle"
                                                            value="JABATAN" />
                                                        <select-input text="Pilih Jabatan" name="jobTitle"
                                                            v-model="form.jobTitle" :options="jobTitleOptions" />
                                                        <input-error :message="form.errors.jobTitle" />
                                                    </div>
                                                    <div class="col-md-6">
                                                        <input-label class="form-label-custom" for="branches"
                                                            value="LOKASI CABANG" />
                                                        <select-input text="Pilih Lokasi" name="branches"
                                                            v-model="form.branches" :options="branchOptions" />
                                                        <input-error :message="form.errors.branches" />
                                                    </div>
                                                    <div class="col-md-12"
                                                        v-if="showCodeInput && !hasRole(['admin', 'developer'])">
                                                        <div
                                                            class="p-3 bg-warning bg-opacity-10 border border-warning rounded-3 shadow-sm transition-all">
                                                            <div class="d-flex align-items-center mb-2">
                                                                <i class="fas fa-key text-warning me-2"></i>
                                                                <span
                                                                    class="fw-bold text-dark small text-uppercase">Otorisasi
                                                                    Diperlukan</span>
                                                            </div>

                                                            <text-input type="text" v-model="form.branch_code"
                                                                name="branch_code"
                                                                :placeholder="`Masukkan kode cabang ${nameBranch}...`"
                                                                class="w-100 border-warning" />

                                                            <div class="form-text text-muted text-xs mt-1">
                                                                Hubungi Admin untuk mendapatkan kode cabang tujuan.
                                                            </div>

                                                            <input-error :message="form.errors.branch_code" />
                                                        </div>

                                                    </div>
                                                    <div class="col-md-12">
                                                        <input-label class="form-label-custom" for="employee_status"
                                                            value="STATUS PEGAWAI" />
                                                        <div class="d-flex gap-2">
                                                            <div class="form-check form-check-inline" v-for="status in [
                                                                { value: 'contract', label: 'Kontrak' },
                                                                { value: 'permanent', label: 'Pegawai Tetap' },
                                                                { value: 'intern', label: 'Magang' },
                                                                { value: 'freelance', label: 'Freelance' },
                                                            ]" :key="status.value">
                                                                <input class="form-check-input" type="radio"
                                                                    :value="status.value" v-model="form.employee_status"
                                                                    :id="status.value">
                                                                <label class="form-check-label" :for="status.value">{{
                                                                    status.label }}</label>
                                                            </div>
                                                        </div>
                                                        <input-error :message="form.errors.employee_status" />
                                                    </div>
                                                </div>


                                            </div>

                                        </div>

                                        <div class="d-flex justify-content-end mt-5 pt-3">
                                            <base-button waiting="Menyimpan..." :loading="form.processing"
                                                button-class="rounded-3 px-3 btn-height-1 shadow" type="submit"
                                                icon="fas fa-save" name="submit" label="Simpan Perubahan"
                                                variant="primary" />
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </form-wrapper>
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

.img-circle-profile {
    width: 300px;
    height: auto;
    object-fit: cover;
    object-position: center;
}


/* Container Utama */
.profile-card {
    background: #ffffff;
    transition: all 0.3s ease;
}

/* Header Avatar Icon */
.icon-square-lg {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Judul Bagian (Section Title) */
.section-title {
    font-size: 0.95rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid rgba(0, 0, 0, 0.05);
}

/* Label Input yang Konsisten */
.form-label-custom {
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    color: #6c757d;
    /* Abu-abu profesional */
    text-transform: uppercase;
    margin-bottom: 0.4rem;
}

/* Styling Khusus Input */
/* Menghapus border radius berlebih pada input group */
.input-group-text {
    background-color: #f8f9fa;
    color: #6c757d;
}

/* Styling Container Foto Profil */
.avatar-upload-container {
    width: fit-content;
    padding: 5px;
    background: #fff;
    border: 1px dashed #dee2e6;
    /* border-radius: 50%; */
}

/* Efek Tombol Simpan */
.btn-save-animate {
    font-weight: 600;
    letter-spacing: 0.5px;
    transition: transform 0.2s;
}

.btn-save-animate:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3) !important;
}

/* Radio Button Styling (Status Pegawai) */
.form-check-input:checked {
    background-color: #198754;
    border-color: #198754;
}

/* Responsive Padding */
@media (max-width: 768px) {
    .profile-card .card-body {
        padding: 1.5rem !important;
    }
}
</style>
