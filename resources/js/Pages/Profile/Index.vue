<script setup>
import { useForm, Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import ProfileComponent from './profileComponent.vue';
const props = defineProps({
    profile: Object,
    branches: Array,
    jobTitle: Array
})
const form = useForm({
    name: props.profile.user.name ?? "",
    email: props.profile.user.email ?? "",
    employee_id_number: "",
    national_id_number: "",
    jobTitle: "",
    employee_status: "",
    branches: "",
    date_of_entry: "",
    religion: "",
    birthdate: "",
    birthplace: "",
    entry_year: "",
    education: "",
    major: "",
    graduation_year: "",
    gender: "",
    number_phone: "",
    address: "",
    images: null
});
const submit = () => {
    form.post(route("profile.update", props.profile.profile_id), {
        _method: 'put',
        preserveScroll: true,
        forceFormData: true,
    });
};
const page = usePage().props.flash;

const branchOptions = computed(() => {
    return props.branches?.map(item => {
        return {
            value: item.branches_id,
            label: (item.name).replace(/\b\w/g, l => l.toUpperCase())
        }
    })
})
const jobTitleOptions = computed(() => {
    return props.jobTitle?.map(item => {
        return {
            value: item.job_title_id,
            label: (item.title).replace(/\b\w/g, l => l.toUpperCase())
        }
    })
})

const fieldsPerStep = {
    1: ["name", "birthplace", "religion", "national_id_number", "birthdate", "gender", "address"],
    2: ["entry_year", "education", "major", "graduation_year"],
    3: ["employee_id_number", "date_of_entry", "email", "number_phone", "jobTitle", "branches"],
    4: ["images"],
};
const labels = {
    1: "Data Diri",
    2: "Data Pendidikan",
    3: "Data Pegawai",
    4: "Photo",
};
const hasAnyErrors = computed(() => Object.keys(form.errors).length > 0);
// console.log(hasAnyErrors.value);
</script>

<template>

    <Head title="Halaman Profile" />
    <loader-overlay />
    <div class="container py-5">
        <div class="row justify-content-center align-items-center">
            <div class="col-xl-12">
                <alert :duration="15" :variant="page.message ? 'info' : 'success'" :message="page.message" />

                <div class="bg-light border rounded-3 p-4 mb-4 d-flex align-items-center border-secondary-subtle">
                    <div class="me-3 text-primary">
                        <i class="fas fa-user-circle fa-5x"></i>
                    </div>
                    <div>
                        <h2 class="mb-1 fw-semibold text-dark">Lengkapi Profil Anda</h2>
                        <p class="mb-0 text-muted">
                            Isi data pribadi Anda dengan lengkap agar dapat menggunakan sistem ini.
                        </p>
                    </div>
                </div>
                <ProfileComponent :step_indicator="labels" :step="4" :fieldPerStep="fieldsPerStep" :form="form"
                    @submit="submit">

                    <template #step-1>
                        <h5 class="mb-4 text-primary fw-bold"><i class="fas fa-user me-2"></i>Informasi Pribadi</h5>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <input-label class="small text-muted fw-bold text-uppercase" for="name"
                                    value="Nama Lengkap" />
                                <text-input class="input-height-1" type="text" name="name" v-model="form.name"
                                    placeholder="Sesuai KTP" />
                                <input-error :message="form.errors.name" />
                            </div>
                            <div class="col-md-6">
                                <input-label class="small text-muted fw-bold text-uppercase" for="national_id_number"
                                    value="NIK (KTP)" />
                                <text-input class="input-height-1" placeholder="16 digit NIK" type="text"
                                    name="national_id_number" v-model="form.national_id_number" />
                                <input-error :message="form.errors.national_id_number" />
                            </div>

                            <div class="col-md-6">
                                <input-label class="small text-muted fw-bold text-uppercase" for="birthplace"
                                    value="Tempat Lahir" />
                                <text-input class="input-height-1" placeholder="Nama Kota" type="text" name="birthplace"
                                    v-model="form.birthplace" />
                                <input-error :message="form.errors.birthplace" />
                            </div>
                            <div class="col-md-6">
                                <input-label class="small text-muted fw-bold text-uppercase" for="birthdate"
                                    value="Tanggal Lahir" />
                                <text-input class="input-height-1" type="date" name="birthdate"
                                    v-model="form.birthdate" />
                                <input-error :message="form.errors.birthdate" />
                            </div>

                            <div class="col-md-6">
                                <input-label class="small text-muted fw-bold text-uppercase" for="religion"
                                    value="Agama" />
                                <select-input class="input-height-1" text="-- Pilih Agama --" name="religion"
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
                                <input-label class="small text-muted fw-bold text-uppercase" for="gender"
                                    value="Jenis Kelamin" />
                                <select-input class="input-height-1" text="-- Pilih Gender --" name="gender"
                                    v-model="form.gender" :options="[
                                        { value: 'male', label: 'Laki-laki' },
                                        { value: 'female', label: 'Perempuan' },
                                    ]" />
                                <input-error :message="form.errors.gender" />
                            </div>

                            <div class="col-12">
                                <input-label class="small text-muted fw-bold text-uppercase" for="address"
                                    value="Alamat Lengkap" />
                                <text-area placeholder="Jalan, RT/RW, Kelurahan, Kecamatan..." name="address"
                                    v-model="form.address" :rows="3" />
                                <input-error :message="form.errors.address" />
                            </div>
                        </div>
                    </template>

                    <template #step-2>
                        <h5 class="mb-4 text-primary fw-bold"><i class="fas fa-graduation-cap me-2"></i>Riwayat
                            Pendidikan</h5>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <input-label class="small text-muted fw-bold text-uppercase" for="education"
                                    value="Jenjang Pendidikan" />
                                <select-input class="input-height-1" text="-- Pilih Jenjang --" name="education"
                                    v-model="form.education" :options="[
                                        { value: 'SMA', label: 'SMA/SMK/Sederajat' },
                                        { value: 'D3/Diploma', label: 'D3 - Diploma' },
                                        { value: 'S1/Sarjana', label: 'S1 - Sarjana' },
                                        { value: 'S2/Magister', label: 'S2 - Magister' },
                                    ]" />
                                <input-error :message="form.errors.education" />
                            </div>

                            <div class="col-md-6">
                                <input-label class="small text-muted fw-bold text-uppercase" for="major"
                                    value="Jurusan" />
                                <text-input placeholder="Contoh: Teknik Informatika" class="input-height-1" type="text"
                                    name="major" v-model="form.major" />
                                <input-error :message="form.errors.major" />
                            </div>

                            <div class="col-md-6">
                                <input-label class="small text-muted fw-bold text-uppercase" for="entry_year"
                                    value="Tahun Masuk" />
                                <input-years input-class="input-height-1" placeholder="YYYY" name="entry_year"
                                    v-model="form.entry_year" />
                                <input-error :message="form.errors.entry_year" />
                            </div>

                            <div class="col-md-6">
                                <input-label class="small text-muted fw-bold text-uppercase" for="graduation_year"
                                    value="Tahun Lulus" />
                                <input-years input-class="input-height-1" placeholder="YYYY" name="graduation_year"
                                    v-model="form.graduation_year" />
                                <input-error :message="form.errors.graduation_year" />
                            </div>
                        </div>
                    </template>

                    <template #step-3>
                        <h5 class="mb-4 text-primary fw-bold"><i class="fas fa-briefcase me-2"></i>Data Kepegawaian</h5>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <input-label class="small text-muted fw-bold text-uppercase" for="employee_id_number"
                                    value="ID Pegawai / NIP" />
                                <text-input class="input-height-1" placeholder="Nomor Induk Pegawai" type="text"
                                    name="employee_id_number" v-model="form.employee_id_number" />
                                <input-error :message="form.errors.employee_id_number" />
                            </div>
                            <div class="col-md-6">
                                <input-label class="small text-muted fw-bold text-uppercase" for="date_of_entry"
                                    value="Tanggal Bergabung" />
                                <text-input class="input-height-1" type="date" name="date_of_entry"
                                    v-model="form.date_of_entry" />
                                <input-error :message="form.errors.date_of_entry" />
                            </div>

                            <div class="col-md-6">
                                <input-label class="small text-muted fw-bold text-uppercase" for="email"
                                    value="Alamat Email" />
                                <text-input class="input-height-1 bg-light" disabled name="email"
                                    v-model="form.email" />
                                <small class="text-muted fst-italic">*Email tidak dapat diubah</small>
                                <input-error :message="form.errors.email" />
                            </div>
                            <div class="col-md-6">
                                <input-label class="small text-muted fw-bold text-uppercase" for="number_phone"
                                    value="No. WhatsApp / HP" />
                                <input-number class="input-height-1" placeholder="08..." name="number_phone"
                                    v-model="form.number_phone" />
                                <input-error :message="form.errors.number_phone" />
                            </div>

                            <div class="col-md-4">
                                <input-label class="small text-muted fw-bold text-uppercase" for="employee_status"
                                    value="Status Pegawai" />
                                <select-input class="input-height-1" text="-- Pilih Status --" name="employee_status"
                                    v-model="form.employee_status" :options="[
                                        { value: 'contract', label: 'Kontrak' },
                                        { value: 'permanent', label: 'Permanen' },
                                        { value: 'intern', label: 'Magang' },
                                        { value: 'freelance', label: 'Freelance' },
                                    ]" />
                                <input-error :message="form.errors.employee_status" />
                            </div>
                            <div class="col-md-4">
                                <input-label class="small text-muted fw-bold text-uppercase" for="jobTitle"
                                    value="Jabatan" />
                                <select-input class="input-height-1" text="-- Pilih Jabatan --" name="jobTitle"
                                    v-model="form.jobTitle" :options="jobTitleOptions" />
                                <input-error :message="form.errors.jobTitle" />
                            </div>
                            <div class="col-md-4">
                                <input-label class="small text-muted fw-bold text-uppercase" for="branches"
                                    value="Lokasi Cabang" />
                                <select-input class="input-height-1" text="-- Pilih Lokasi --" name="branches"
                                    v-model="form.branches" :options="branchOptions" />
                                <input-error :message="form.errors.branches" />
                            </div>
                        </div>
                    </template>

                    <template #step-4>
                        <div class="text-center mb-4">
                            <h5 class="text-primary fw-bold"><i class="fas fa-camera me-2"></i>Foto Profil</h5>
                            <p class="text-muted small">Upload foto terbaru untuk identitas profil Anda.</p>
                        </div>

                        <div class="row justify-content-center mb-4">
                            <div class="col-md-8">
                                <div class="card border-0 shadow-sm bg-light">
                                    <div class="card-body p-4 text-center">
                                        <file-upload v-model="form.images" name="images"
                                            :default-image-url="props.profile?.images ? '/storage/' + props.profile?.images : ''" />
                                        <input-error :message="form.errors.images" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row justify-content-center">
                            <div class="col-md-10">
                                <div class="alert alert-info border-0 shadow-sm d-flex align-items-start" role="alert">
                                    <i class="fas fa-info-circle fs-4 me-3 mt-1"></i>
                                    <div>
                                        <h6 class="fw-bold mb-1">Konfirmasi Data</h6>
                                        <ul class="mb-0 ps-3 small text-muted">
                                            <li>Pastikan seluruh data yang diinput sudah benar dan sesuai dokumen resmi.
                                            </li>
                                            <li>Anda dapat kembali ke langkah sebelumnya ("Back") jika perlu melakukan
                                                koreksi.</li>
                                            <li>Tekan tombol <strong>"Submit"</strong> di bawah untuk menyimpan profil
                                                Anda.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                </ProfileComponent>
            </div>
        </div>
    </div>
</template>
