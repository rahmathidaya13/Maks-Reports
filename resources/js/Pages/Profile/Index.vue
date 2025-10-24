<script setup>
import { useForm, Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
const props = defineProps({
    profile: Object,
    branches: Array,
    roles: Array
})
const form = useForm({
    name: props.profile.user.name ?? "",
    email: props.profile.user.email ?? "",
    roles: "",
    branches: "",
    date_of_entry: "",
    birthdate: "",
    education: "",
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
const page = usePage();
const message = computed(() => page.props.flash.message);


const branchOptions = computed(() => {
    return props.branches?.map(item => {
        return {
            id: item.branches_id,
            name: (item.name).replace(/\b\w/g, l => l.toUpperCase())
        }
    })
})
const roleOptions = computed(() => {
    return props.roles?.map(item => {
        return {
            id: item.roles_id,
            name: (item.name).replace(/\b\w/g, l => l.toUpperCase())
        }
    })
})
</script>

<template>

    <Head title="Halaman Profile" />

    <div class="container py-5">
        <div class="row justify-content-center align-items-center">
            <div class="col-xl-12">
                <alert :duration="15" :variant="$page.props.flash.status ? 'info' : 'success'"
                    :message="$page.props.flash.status" />

                <div class="bg-light border rounded-4 p-4 mb-4 d-flex align-items-center border-secondary-subtle">
                    <div class="me-3 text-primary">
                        <i class="fas fa-user-circle fa-5x"></i>
                    </div>
                    <div>
                        <h2 class="mb-1 fw-semibold text-dark">Lengkapi Profil Anda</h2>
                        <p class="mb-0 text-muted">
                            Isi data pribadi Anda dengan lengkap agar dapat mengakses seluruh fitur ini.
                        </p>
                    </div>
                </div>
                <div class="card bg-light shadow-sm rounded-3 rounded overflow-hidden border-secondary-subtle">
                    <div class="card-body">
                        <form-wrapper @submit="submit">
                            <div class="row g-1">

                                <div class="col-xl-4 mb-2">
                                    <file-upload v-model="form.images" name="images"
                                        :default-image-url="props.profile?.images ? '/storage/' + props.profile?.images : ''" />
                                    <input-error :message="form.errors.images" />
                                </div>

                                <div class="col-xl-8">
                                    <div class="mb-2">
                                        <input-label class="fw-bold" for="name" value="Nama" />
                                        <div class="position-relative">
                                            <i class="fas fa-user input-icon-left"></i>
                                            <text-input class="input-fixed-height" disabled type="text" name="name"
                                                v-model="form.name" />
                                        </div>
                                        <input-error :message="form.errors.name" />
                                    </div>
                                    <div class="mb-2">
                                        <input-label class="fw-bold" for="birthdate" value="Tanggal Lahir" />
                                        <div class="position-relative">
                                            <i class="fas fa-calendar input-icon-left"></i>
                                            <text-input autofocus class="input-fixed-height" type="date"
                                                name="birthdate" v-model="form.birthdate" />
                                        </div>
                                        <input-error :message="form.errors.birthdate" />
                                    </div>
                                    <div class="mb-2">
                                        <input-label class="fw-bold" for="date_of_entry" value="Tanggal Masuk" />
                                        <div class="position-relative">
                                            <i class="fas fa-calendar input-icon-left"></i>
                                            <text-input class="input-fixed-height" type="date" name="date_of_entry"
                                                v-model="form.date_of_entry" />
                                        </div>
                                        <input-error :message="form.errors.date_of_entry" />
                                    </div>

                                    <div class="mb-2">
                                        <input-label class="fw-bold" for="gender" value="Jenis Kelamin" />
                                        <div class="position-relative">
                                            <i class="fas fa-venus-mars input-icon-left"></i>
                                            <select-input class="input-fixed-select" text="Pilih Jenis Kelamin"
                                                name="gender" v-model="form.gender" :options="[
                                                    { value: 'male', label: 'Laki-laki' },
                                                    { value: 'female', label: 'Perempuan' },
                                                    { value: '-', label: 'Tidak ada pilihan' },
                                                ]" />
                                        </div>
                                        <input-error :message="form.errors.gender" />
                                    </div>
                                </div>

                                <div class="col-xl-12">
                                    <div class="mb-2">
                                        <input-label class="fw-bold" for="email" value="Email" />
                                        <div class="position-relative">
                                            <i class="fas fa-envelope input-icon-left"></i>
                                            <text-input disabled class="input-fixed-height" name="email"
                                                v-model="form.email" />
                                        </div>
                                        <input-error :message="form.errors.email" />
                                    </div>
                                    <div class="mb-2">
                                        <input-label class="fw-bold" for="number_phone" value="No.Handphone" />
                                        <div class="position-relative">
                                            <i class="fas fa-mobile input-icon-left"></i>
                                            <input-number class="input-fixed-height" placeholder="0" name="number_phone"
                                                v-model="form.number_phone" />
                                        </div>
                                        <input-error :message="form.errors.number_phone" />
                                    </div>
                                    <div class="mb-2">
                                        <input-label class="fw-bold" for="education" value="Pendidikan Terakhir" />
                                        <div class="position-relative">
                                            <i class="fas fa-graduation-cap input-icon-left"></i>
                                            <select-input class="input-fixed-select" text="Pilih Pendidikan Terakhir"
                                                name="education" v-model="form.education" :options="[
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
                                        <input-label class="fw-bold" for="roles" value="Jabatan" />
                                        <select-2 name="roles" v-model="form.roles" :options="roleOptions" :settings="{
                                            placeholder: 'Pilih Jabatan',
                                            width: '100%',
                                            allowClear: true
                                        }" />
                                        <input-error :message="form.errors.roles" />
                                    </div>
                                    <div class="mb-2">
                                        <input-label class="fw-bold" for="branches" value="Lokasi/Cabang" />
                                        <select-2 name="branches" v-model="form.branches" :options="branchOptions"
                                            :settings="{
                                                placeholder: 'Pilih Lokasi',
                                                width: '100%',
                                                allowClear: true
                                            }" />
                                        <input-error :message="form.errors.branches" />
                                    </div>
                                    <div class="mb-2">
                                        <input-label class="fw-bold" for="address" value="Alamat" />
                                        <text-area name="address" v-model="form.address" :rows="5" :cols="5" />
                                        <input-error :message="form.errors.address" />
                                    </div>
                                </div>
                            </div>
                            <div>
                                <base-button type="submit" icon="fas fa-user-edit" name="submit" label="Update profile"
                                    variant="success" />
                            </div>
                        </form-wrapper>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
