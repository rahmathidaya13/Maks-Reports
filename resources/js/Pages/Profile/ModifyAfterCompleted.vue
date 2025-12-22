<script setup>
import { useForm, Head, Link, usePage, router } from '@inertiajs/vue3';
import moment from 'moment';
import { computed, ref } from 'vue';
const props = defineProps({
    profile: Object,
    branches: Object,
    jobTitle: Object
})
const message = computed(() => usePage().props.flash.message);
const form = useForm({
    name: props.profile.user.name ?? "",
    email: props.profile.user.email ?? "",
    employee_id_number: props.profile.employee_id_number ?? "",
    jobTitle: props.profile.job_title_id ?? "",
    branches: props.profile.branches_id ?? "",
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
const submit = () => {
    form.post(route("profile.after.update", props.profile?.users_id), {
        _method: 'put',
        preserveScroll: true,
        replace: true,
        preserveState: true,
        forceFormData: true,
    });
};

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
            <alert :variant="$page.props.flash.message ? 'success' : 'danger'" :duration="10" :message="message" />
            <div class="row pb-3">
                <div class="col-xl-12 col-12">
                    <div class="card bg-light shadow-sm rounded-3 rounded overflow-hidden border-secondary-subtle">
                        <h5 class="card-header fw-bold text-uppercase p-3 text-bg-dark">
                            <i class="fas fa-clipboard me-1 text-light"></i>
                            Form Profil
                        </h5>
                        <div v-if="form.processing">
                            <loader-horizontal message="Sedang memperbarui profil" />
                        </div>
                        <div class="card-body" :class="['blur-area', form.processing ? 'is-blurred' : '']">
                            <form-wrapper @submit="submit">
                                <div class="row g-0 border text-bg-grey p-3">
                                    <div class="col-xl-4 col-md-12 col-sm-12">
                                        <div class="d-flex justify-content-center">
                                            <file-input :width="250" :height="280"
                                                :pathUrls="props.profile?.images ? '/storage/' + props.profile?.images : ''"
                                                objectFit="fill" @preview="onPreview" name="images"
                                                v-model="form.images" />
                                        </div>
                                        <div class="text-center">
                                            <input-error :message="form.errors.images" />
                                        </div>
                                    </div>

                                    <div class="col-xl-8 col-md-12 col-sm-12">
                                        <div class="row g-1">
                                            <div class="col-xl-6 mb-2">
                                                <input-label class="fw-bold" for="name" value="Nama" />
                                                <div class="input-group">
                                                    <text-input type="text" name="name" v-model="form.name" />
                                                </div>
                                                <input-error :message="form.errors.name" />
                                            </div>
                                            <div class="mb-2 col-xl-6">
                                                <input-label class="fw-bold" for="national_id_number" value="NIK" />
                                                <div class="input-group">
                                                    <text-input placeholder="1234567890" type="text"
                                                        name="national_id_number" v-model="form.national_id_number" />
                                                </div>
                                                <input-error :message="form.errors.national_id_number" />
                                            </div>
                                            <div class="mb-2 col-xl-6">
                                                <input-label class="fw-bold" for="birthplace" value="Tempat Lahir" />
                                                <div class="input-group">
                                                    <text-input type="text" name="birthplace"
                                                        v-model="form.birthplace" />
                                                </div>
                                                <input-error :message="form.errors.birthplace" />
                                            </div>
                                            <div class="mb-2 col-xl-6">
                                                <input-label class="fw-bold" for="religion" value="Agama" />
                                                <div class="input-group">
                                                    <select-input text="Pilih Agama" name="religion"
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
                                            <div class="mb-2 col-xl-6">
                                                <input-label class="fw-bold" for="gender" value="Jenis Kelamin" />
                                                <div class="input-group">
                                                    <select-input text="Pilih Jenis Kelamin" name="gender"
                                                        v-model="form.gender" :options="[
                                                            { value: 'male', label: 'Laki-laki' },
                                                            { value: 'female', label: 'Perempuan' },
                                                        ]" />
                                                </div>
                                                <input-error :message="form.errors.gender" />
                                            </div>
                                            <div class="mb-2 col-xl-6">
                                                <input-label class="fw-bold" for="birthdate" value="Tanggal Lahir" />
                                                <div class="input-group">
                                                    <text-input type="date" name="birthdate" v-model="form.birthdate" />
                                                </div>
                                                <input-error :message="form.errors.birthdate" />
                                            </div>
                                            <div class="col-xl-12">
                                                <input-label class="fw-bold" for="address" value="Alamat" />
                                                <div class="input-group">
                                                    <text-area :rows="5" :cols="5" name="address"
                                                        v-model="form.address" />
                                                </div>
                                                <input-error :message="form.errors.address" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-2 border text-bg-grey p-3">
                                    <div class="text-bg-dark mb-3">
                                        <h5 class="text-uppercase p-2">
                                            <i class="fas fa-graduation-cap me-2"></i>Data Pendidikan
                                        </h5>
                                    </div>
                                    <div class="col-xl-6 col-12">
                                        <div class="mb-2">
                                            <input-label class="fw-bold" for="entry_year" value="Tahun Masuk" />
                                            <div class="input-group">
                                                <input-years :placeholder="'contoh: ' + moment().format('YYYY')"
                                                    name="entry_year" v-model="form.entry_year" />
                                            </div>
                                            <input-error :message="form.errors.entry_year" />
                                        </div>
                                    </div>

                                    <div class="col-xl-6 col-12">
                                        <div class="mb-2">
                                            <input-label class="fw-bold" for="education" value="Pendidikan Terakhir" />
                                            <div class="input-group">
                                                <select-input text="Pilih Pendidikan Terakhir" name="education"
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

                                    </div>
                                    <div class="col-xl-6">
                                        <div class="mb-2">
                                            <input-label class="fw-bold" for="major" value="Jurusan" />
                                            <div class="input-group">
                                                <text-input type="text" name="major" v-model="form.major" />
                                            </div>
                                            <input-error :message="form.errors.major" />
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-12">
                                        <div class="mb-2">
                                            <input-label class="fw-bold" for="graduation_year" value="Tahun Lulus" />
                                            <div class="input-group">
                                                <input-years :placeholder="'contoh: ' + moment().format('YYYY')"
                                                    name="graduation_year" v-model="form.graduation_year" />
                                            </div>
                                            <input-error :message="form.errors.graduation_year" />
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-2 border text-bg-grey p-3">
                                    <div class="text-bg-dark mb-3">
                                        <h5 class="text-uppercase p-2">
                                            <i class="fas fa-user me-2"></i>Data Pegawai
                                        </h5>
                                    </div>
                                    <div class="col-xl-6 col-md-12 col-sm-12">
                                        <div class="mb-2">
                                            <input-label class="fw-bold" for="employee_id_number" value="ID Pegawai" />
                                            <div class="input-group">
                                                <text-input type="text" name="employee_id_number"
                                                    v-model="form.employee_id_number" />
                                            </div>
                                            <input-error :message="form.errors.employee_id_number" />
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-md-12 col-sm-12">
                                        <div class="mb-2">
                                            <input-label class="fw-bold" for="date_of_entry" value="Tanggal Masuk" />
                                            <div class="input-group">
                                                <text-input type="date" name="date_of_entry"
                                                    v-model="form.date_of_entry" />
                                            </div>
                                            <input-error :message="form.errors.date_of_entry" />
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-md-12 col-sm-12">
                                        <div class="mb-2">
                                            <input-label class="fw-bold" for="date_of_entry" value="Status Pegawai" />
                                            <div class="input-group">
                                                <select-input text="Pilih Status Pegawai" name="employee_status"
                                                    v-model="form.employee_status" :options="[
                                                        { value: 'contract', label: 'Kontrak' },
                                                        { value: 'permanent', label: 'Permanen' },
                                                        { value: 'intern', label: 'Magang' },
                                                        { value: 'freelance', label: 'Freelance' },
                                                    ]" />
                                            </div>
                                            <input-error :message="form.errors.date_of_entry" />
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-md-12 col-sm-12">
                                        <div class="mb-2">
                                            <input-label class="fw-bold" for="number_phone" value="No.Handphone" />
                                            <div class="input-group">
                                                <input-number placeholder="0" name="number_phone"
                                                    v-model="form.number_phone" />
                                            </div>
                                            <input-error :message="form.errors.number_phone" />
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-md-12 col-sm-12">
                                        <div class="mb-2">
                                            <input-label class="fw-bold" for="jobTitle" value="Jabatan" />
                                            <div class="input-group">
                                                <select-input text="Pilih Jabatan" name="jobTitle"
                                                    v-model="form.jobTitle" :options="jobTitleOptions" />
                                            </div>
                                            <input-error :message="form.errors.jobTitle" />
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-md-12 col-sm-12">
                                        <div class="mb-2">
                                            <input-label class="fw-bold" for="branches" value="Lokasi" />
                                            <div class="input-group">
                                                <select-input text="Pilih Lokasi/Cabang" name="branches"
                                                    v-model="form.branches" :options="branchOptions" />
                                            </div>
                                            <input-error :message="form.errors.branches" />
                                        </div>
                                    </div>
                                </div>
                                <div class="d-grid d-xl-flex justify-content-xl-end mt-3">
                                    <base-button waiting="Memproses...." :loading="form.processing" class="btn-height-1"
                                        type="submit" :icon="!form.processing ? 'fas fa-user-edit' : ''" name="submit"
                                        label="Perbarui profile" variant="success" />
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

.img-circle-profile {
    width: 300px;
    height: auto;
    object-fit: cover;
    object-position: center;
}
</style>
