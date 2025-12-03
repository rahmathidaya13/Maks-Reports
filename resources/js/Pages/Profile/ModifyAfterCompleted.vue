<script setup>
import { useForm, Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    profile: Object,
    branches: Object,
    jobTitle: Object
})
const message = computed(() => usePage().props.flash.message);
const form = useForm({
    name: props.profile.user.name ?? "",
    email: props.profile.user.email ?? "",
    id_number: props.profile.id_number_employee ?? "",
    jobTitle: props.profile.job_title_id ?? "",
    branches: props.profile.branches_id ?? "",
    date_of_entry: props.profile.date_of_entry ?? "",
    birthdate: props.profile.birthdate ?? "",
    education: props.profile.education ?? "",
    gender: props.profile.gender ?? "",
    number_phone: props.profile.number_phone ?? "",
    address: props.profile.address ?? "",
    images: null
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
            id: item.branches_id,
            name: (item.name).replace(/\b\w/g, l => l.toUpperCase())
        }
    })
})
const jobTitleOptions = computed(() => {
    return props.jobTitle?.map(item => {
        return {
            id: item.job_title_id,
            name: (item.title).replace(/\b\w/g, l => l.toUpperCase())
        }
    })
})
console.log(props.profile);
</script>
<template>

    <Head title="Halaman Ubah Profile" />
    <app-layout>
        <template #content>
            <bread-crumbs :home="false" icon="fas fa-user-edit" title="Ubah Profile" :items="[
                { text: 'Detail Profile', url: route('profile.detail', props.profile?.users_id) },
                { text: 'Ubah Profile' },
            ]" />
            <alert :variant="$page.props.flash.message ? 'success' : 'danger'" :duration="10" :message="message" />
            <div class="row pb-3">
                <div class="col-xl-12 col-12">
                    <div class="card bg-light shadow-sm rounded-3 rounded overflow-hidden border-secondary-subtle">
                        <h5 class="card-header fw-bold text-uppercase p-3 text-bg-dark">
                            <i class="fas fa-clipboard me-1 text-light"></i>
                            Form PRofile
                        </h5>
                        <div class="card-body">
                            <form-wrapper @submit="submit">
                                <div class="row g-0">
                                    <div class="col-xl-4 mb-2 col-md-4 col-sm-12">
                                        <file-upload v-model="form.images" name="images"
                                            :default-image-url="props.profile?.images ? '/storage/' + props.profile?.images : ''" />
                                        <input-error :message="form.errors.images" />
                                    </div>

                                    <div class="col-xl-8 col-md-8 col-sm-12">
                                        <div class="mb-2">
                                            <input-label class="fw-bold" for="name" value="Nama" />
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                                <text-input type="text" name="name" v-model="form.name" />
                                            </div>
                                            <input-error :message="form.errors.name" />
                                        </div>
                                        <div class="mb-2">
                                            <input-label class="fw-bold" for="id_number" value="ID Karyawan" />
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-id-card-alt"></i></span>
                                                <text-input placeholder="1234567890" type="text" name="id_number"
                                                    v-model="form.id_number" />
                                            </div>
                                            <input-error :message="form.errors.id_number" />
                                        </div>
                                        <div class="mb-2">
                                            <input-label class="fw-bold" for="birthdate" value="Tanggal Lahir" />
                                            <div class="input-group">
                                                <span class="input-group-text"> <i class="fas fa-calendar"></i></span>
                                                <text-input autofocus type="date" name="birthdate"
                                                    v-model="form.birthdate" />
                                            </div>
                                            <input-error :message="form.errors.birthdate" />
                                        </div>
                                        <div class="mb-2">
                                            <input-label class="fw-bold" for="date_of_entry" value="Tanggal Masuk" />
                                            <div class="input-group">
                                                <span class="input-group-text"> <i class="fas fa-calendar"></i></span>
                                                <text-input type="date" name="date_of_entry"
                                                    v-model="form.date_of_entry" />
                                            </div>
                                            <input-error :message="form.errors.date_of_entry" />
                                        </div>
                                    </div>

                                    <div class="col-xl-12 col-sm-12 col-md-12 ">
                                        <div class="mb-2">
                                            <input-label class="fw-bold" for="gender" value="Jenis Kelamin" />
                                            <div class="input-group">
                                                <span class="input-group-text"> <i class="fas fa-venus-mars"></i></span>
                                                <select-input text="Pilih Jenis Kelamin" name="gender"
                                                    v-model="form.gender" :options="[
                                                        { value: 'male', label: 'Laki-laki' },
                                                        { value: 'female', label: 'Perempuan' },
                                                        { value: '-', label: 'Tidak ada pilihan' },
                                                    ]" />
                                            </div>
                                            <input-error :message="form.errors.gender" />
                                        </div>
                                        <div class="mb-2">
                                            <input-label class="fw-bold" for="email" value="Email" />
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-envelope"></i>
                                                </span>
                                                <text-input disabled name="email" v-model="form.email" />
                                            </div>
                                            <input-error :message="form.errors.email" />
                                        </div>
                                        <div class="mb-2">
                                            <input-label class="fw-bold" for="number_phone" value="No.Handphone" />
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-mobile"></i>
                                                </span>
                                                <input-number placeholder="0" name="number_phone"
                                                    v-model="form.number_phone" />
                                            </div>
                                            <input-error :message="form.errors.number_phone" />
                                        </div>
                                        <div class="mb-2">
                                            <input-label class="fw-bold" for="education" value="Pendidikan Terakhir" />
                                            <div class="input-group">
                                                <span class="input-group-text"> <i class="fas fa-graduation-cap"></i>
                                                </span>
                                                <select-input text="Pilih Pendidikan Terakhir" name="education"
                                                    v-model="form.education" :options="[
                                                        { value: 'SD', label: 'SD' },
                                                        { value: 'SMP', label: 'SMP' },
                                                        { value: 'SMA', label: 'SMA' },
                                                        { value: 'S1', label: 'S1' },
                                                        { value: 'S2', label: 'S2' },
                                                        { value: 'S3', label: 'S3' },
                                                        { value: '-', label: 'Tidak ada pendidikan' },
                                                    ]" />
                                            </div>
                                            <input-error :message="form.errors.education" />
                                        </div>
                                        <div class="mb-2">
                                            <input-label class="fw-bold" for="jobTitle" value="Jabatan.Posisi" />
                                            <div class="input-group">
                                                <span class="input-group-text"> <i class="fas fa-briefcase"></i>
                                                </span>
                                                <select-2 name="jobTitle" v-model="form.jobTitle"
                                                    :options="jobTitleOptions" :settings="{
                                                        placeholder: 'Pilih Jabatan',
                                                        width: 'auto',
                                                        allowClear: true
                                                    }" />
                                            </div>
                                            <input-error :message="form.errors.roles" />
                                        </div>
                                        <div class="mb-2">
                                            <input-label class="fw-bold" for="branches" value="Lokasi" />
                                            <div class="input-group">
                                                <span class="input-group-text"> <i class="fas fa-map-marker-alt"></i>
                                                </span>
                                                <select-2 name="branches" v-model="form.branches"
                                                    :options="branchOptions" :settings="{
                                                        placeholder: 'Pilih Lokasi',
                                                        width: 'auto',
                                                        allowClear: true
                                                    }" />
                                            </div>
                                            <input-error :message="form.errors.branches" />
                                        </div>
                                        <div class="mb-3">
                                            <input-label class="fw-bold" for="address" value="Alamat" />
                                            <text-area name="address" v-model="form.address" :rows="5" :cols="5" />
                                            <input-error :message="form.errors.address" />
                                        </div>
                                    </div>
                                </div>
                                <div class="d-grid d-xl-flex justify-content-xl-end">
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
