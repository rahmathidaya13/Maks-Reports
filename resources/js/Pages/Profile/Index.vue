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
    education: "",
    major: "",
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

console.log(page.status);
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
    1: ["name", "birthplace", "religion", "national_id_number", "birthdate", "gender", "address", "education", "major"],
    2: ["employee_id_number", "date_of_entry", "email", "number_phone", "jobTitle", "branches"],
    3: ["images"],
};
const labels = {
    1: "Data Diri",
    2: "Data Pegawai",
    3: "Photo",
};
const hasAnyErrors = computed(() => Object.keys(form.errors).length > 0);
console.log(hasAnyErrors.value);
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
                <ProfileComponent :step_indicator="labels" :step="3" :fieldPerStep="fieldsPerStep" :form="form"
                    @submit="submit">

                    <template #step-1>
                        <div class="mb-2">
                            <input-label class="fw-bold" for="name" value="Nama" />
                            <div class="input-group">
                                <text-input class="input-height-1" type="text" name="name" v-model="form.name" />
                            </div>
                            <input-error :message="form.errors.name" />
                        </div>

                        <div class="mb-2">
                            <input-label class="fw-bold" for="national_id_number" value="NIK" />
                            <div class="input-group">
                                <text-input class="input-height-1" placeholder="14718090909" type="text"
                                    name="national_id_number" v-model="form.national_id_number" />
                            </div>
                            <input-error :message="form.errors.national_id_number" />
                        </div>

                        <div class="mb-2">
                            <input-label class="fw-bold" for="birthplace" value="Tempat Lahir" />
                            <div class="input-group">
                                <text-input class="input-height-1" placeholder="contoh: Jakarta dll" type="text"
                                    name="birthplace" v-model="form.birthplace" />
                            </div>
                            <input-error :message="form.errors.birthplace" />
                        </div>

                        <div class="mb-2">
                            <input-label class="fw-bold" for="birthdate" value="Tanggal Lahir" />
                            <div class="input-group">
                                <text-input class="input-height-1" type="date" name="birthdate"
                                    v-model="form.birthdate" />
                            </div>
                            <input-error :message="form.errors.birthdate" />
                        </div>

                        <div class="mb-2">
                            <input-label class="fw-bold" for="religion" value="Agama" />
                            <div class="input-group">
                                <select-input class="input-height-1" text="Pilih Agama" name="religion"
                                    v-model="form.religion" :options="[
                                        { value: 'islam', label: 'Islam' },
                                        { value: 'kristen', label: 'Kristen' },
                                        { value: 'katolik', label: 'Katolik' },
                                        { value: 'hindu', label: 'Hindu' },
                                        { value: 'budha', label: 'Budha' },
                                        { value: 'konghucu', label: 'Konghucu' },
                                    ]" />
                            </div>
                            <input-error :message="form.errors.religion" />
                        </div>
                        <div class="mb-2">
                            <input-label class="fw-bold" for="gender" value="Jenis Kelamin" />
                            <div class="input-group">
                                <select-input class="input-height-1" text="Pilih Jenis Kelamin" name="gender"
                                    v-model="form.gender" :options="[
                                        { value: 'male', label: 'Laki-laki' },
                                        { value: 'female', label: 'Perempuan' },
                                    ]" />
                            </div>
                            <input-error :message="form.errors.gender" />
                        </div>

                        <div class="mb-2">
                            <input-label class="fw-bold" for="education" value="Pendidikan Terakhir" />
                            <div class="input-group">
                                <select-input class="input-height-1" text="Pilih Pendidikan Terakhir" name="education"
                                    v-model="form.education" :options="[
                                        { value: 'SD', label: 'SD' },
                                        { value: 'SMP', label: 'SMP/MTS/Sederajat' },
                                        { value: 'SMA', label: 'SMA/SMK/MA/PAKET C/Sederajat' },
                                        { value: 'S1/Sarjana', label: 'S1/Sarjana' },
                                        { value: 'S2/Magister', label: 'S2/Magister' },
                                        { value: 'S3/Doktor', label: 'S3/Doktor' },
                                    ]" />
                            </div>
                            <input-error :message="form.errors.education" />
                        </div>

                        <div class="mb-2">
                            <input-label class="fw-bold" for="major" value="Jurusan" />
                            <div class="input-group">
                                <text-input placeholder="contoh: Teknik Informatika,Accounting dll"
                                    class="input-height-1" type="text" name="major" v-model="form.major" />
                            </div>
                            <input-error :message="form.errors.major" />
                        </div>

                        <div class="col-xl-12 col-md-12 col-sm-12">
                            <div class="mb-3">
                                <input-label class="fw-bold" for="address" value="Alamat" />
                                <text-area placeholder="contoh: Jl. Kebon Jeruk, Jakarta, Indonesia" name="address"
                                    v-model="form.address" :rows="5" :cols="5" />
                                <input-error :message="form.errors.address" />
                            </div>
                        </div>
                    </template>

                    <template #step-2>
                        <div class="mb-2">
                            <input-label class="fw-bold" for="employee_id_number" value="ID Karyawan" />
                            <div class="input-group">
                                <text-input class="input-height-1" placeholder="1234567890" type="text"
                                    name="employee_id_number" v-model="form.employee_id_number" />
                            </div>
                            <input-error :message="form.errors.employee_id_number" />
                        </div>
                        <div class="mb-2">
                            <input-label class="fw-bold" for="date_of_entry" value="Tanggal Masuk" />
                            <div class="input-group">
                                <text-input class="input-height-1" type="date" name="date_of_entry"
                                    v-model="form.date_of_entry" />
                            </div>
                            <input-error :message="form.errors.date_of_entry" />
                        </div>

                        <div class="mb-2">
                            <input-label class="fw-bold" for="employee_status" value="Status Pegawai" />
                            <div class="input-group">
                                <select-input class="input-height-1" text="Pilih Status Pegawai" name="employee_status"
                                    v-model="form.employee_status" :options="[
                                        { value: 'contract', label: 'Kontrak' },
                                        { value: 'permanent', label: 'Permanen' },
                                        { value: 'intern', label: 'Magang' },
                                        { value: 'freelance', label: 'Freelance' },
                                    ]" />
                            </div>
                            <input-error :message="form.errors.employee_status" />
                        </div>

                        <div class="mb-2">
                            <input-label class="fw-bold" for="email" value="Email" />
                            <div class="input-group">
                                <text-input class="input-height-1" disabled name="email" v-model="form.email" />
                            </div>
                            <input-error :message="form.errors.email" />
                        </div>
                        <div class="mb-2">
                            <input-label class="fw-bold" for="number_phone" value="No.Handphone" />
                            <div class="input-group">
                                <input-number class="input-height-1" placeholder="0" name="number_phone"
                                    v-model="form.number_phone" />
                            </div>
                            <input-error :message="form.errors.number_phone" />
                        </div>

                        <div class="mb-2">
                            <input-label class="fw-bold" for="jobTitle" value="Jabatan" />
                            <div class="input-group">
                                <select-input class="input-height-1" text="Pilih Jabatan" name="jobTitle"
                                    v-model="form.jobTitle" :options="jobTitleOptions" />
                            </div>
                            <input-error :message="form.errors.jobTitle" />
                        </div>
                        <div class="mb-2">
                            <input-label class="fw-bold" for="branches" value="Lokasi" />
                            <div class="input-group">
                                <select-input class="input-height-1" text="Pilih Lokasi/Cabang" name="branches"
                                    v-model="form.branches" :options="branchOptions" />
                            </div>
                            <input-error :message="form.errors.branches" />
                        </div>
                    </template>
                    <template #step-3>
                        <div class="mb-2">
                            <file-upload v-model="form.images" name="images"
                                :default-image-url="props.profile?.images ? '/storage/' + props.profile?.images : ''" />
                            <input-error :message="form.errors.images" />
                        </div>
                        <div class="mb-2">
                            <div class="callout callout-info rounded-0">
                                <h5 class="fw-bold">
                                    <i class="fas fa-bullhorn me-2"></i> Informasi
                                </h5>
                                <ul class="mb-0 ps-3">
                                    <li> Pastikan semua data yang Anda masukkan sudah sesuai dengan informasi diri Anda.
                                    </li>
                                    <li>Jika terdapat kesalahan, Anda bisa kembali ke langkah sebelumnya untuk
                                        memperbaiki.</li>
                                    <li>Data yang lengkap dan benar akan membantu proses administrasi berjalan lancar.
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </template>

                </ProfileComponent>
            </div>
        </div>
    </div>
</template>
