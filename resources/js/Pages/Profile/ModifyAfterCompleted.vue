<script setup>
import { useForm, Head, Link, usePage } from '@inertiajs/vue3';
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
const previewImage = ref(null)

// menerima event preview dari child
function onPreview(url) {
    previewImage.value = url
}


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
                                <div class="row g-2">
                                    <div class="col-xl-3 col-md-12 col-sm-12">
                                        <div class="d-flex justify-content-center">
                                            <file-input :width="300" :height="300"
                                                :pathUrls="props.profile?.images ? '/storage/' + props.profile?.images : ''"
                                                objectFit="fill" @preview="onPreview" name="images"
                                                v-model="form.images" />
                                            <input-error :message="form.errors.images" />
                                        </div>
                                    </div>

                                    <div class="col-xl-4 col-md-8 col-sm-12">
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
                                    <div class="col-xl-4 col-md-8 col-sm-12">
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
<style scoped>
.img-circle-profile {
    width: 300px;
    height: auto;
    object-fit: cover;
    object-position: center;
}
</style>
