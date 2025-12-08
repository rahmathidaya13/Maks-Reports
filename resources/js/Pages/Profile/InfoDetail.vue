<script setup>
import { useForm, Head, Link, usePage, router } from '@inertiajs/vue3';
import moment from 'moment';
import { computed, ref } from 'vue';
moment.locale('id');
const props = defineProps({
    detail: Object,
})
const imageSource = computed(() => {
    if (props.detail?.images != null) {
        return `/storage/${props.detail?.images}`
    }
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(props.detail?.user.name)}`
})
const textToUppercase = (text) => {
    return text.replace(/\b\w/g, l => l.toUpperCase())
}
const loaderActive = ref(null);
const goToEditProfile = (id) => {
    loaderActive.value?.show("Sedang memuat data...");
    router.get(route('profile.edit', id), {}, {
        onFinish: () => loaderActive.value?.hide()
    });
}
</script>

<template>

    <Head title="Halaman Detail Profil" />
    <app-layout>
        <template #content>
            <loader-page ref="loaderActive" />


            <bread-crumbs :home="false" icon="fas fa-user" title="Detail Profil" :items="[
                { text: 'Detail Profil' },
            ]" />

            <alert :variant="$page.props.flash.message ? 'success' : 'danger'" :duration="10"
                :message="$page.props.flash.message || $page.props.flash.error" />
            <div class="row">
                <div class="col-xl-12 pb-3">
                    <div class="card shadow border border-secondary-subtle overflow-hidden">
                        <div class="card-body p-0">
                            <div class="row">
                                <div
                                    class="text-bg-grey col-md-12 col-xl-4 col-12 d-flex flex-column align-items-center profile-wrapper">
                                    <div class="profile-image-container mb-3">
                                        <img :src="imageSource" alt="Foto Profil"
                                            class="img-fluid profile-img-circle border border-5 border-light shadow-sm">
                                    </div>
                                    <h3 class="fw-bold text-center">
                                        {{ textToUppercase(props.detail?.user.name) ?? 'Nama Pengguna' }}
                                    </h3>
                                    <h6 class="text-muted">
                                        {{ textToUppercase(props.detail?.job_title.title) ?? 'Jabatan/Peran' }}
                                    </h6>

                                    <div class="d-flex justify-content-center gap-1 mt-3">
                                        <button @click="goToEditProfile(props.detail?.users_id)"
                                            class="btn btn-outline-success bg-gradient">
                                            <i class="fas fa-edit"></i>
                                            Perbarui Profil
                                        </button>
                                    </div>

                                </div>

                                <div class="col-xl-8 col-md-12 p-xl-3 p-4 col-12 ">
                                    <div>
                                        <h5 class="text-secondary mb-3 text-uppercase"><i
                                                class="bi bi-person-lines-fill me-2"></i>Data
                                            Pribadi</h5>
                                        <div class="row detail-row personal">
                                            <div class="col-xl-6 col-6 mb-2">
                                                <p class="mb-0 fw-bold">NIK:</p>
                                                <p class="text-muted">
                                                    {{ props.detail?.national_id_number }}
                                                </p>
                                            </div>
                                            <div class="col-xl-6 col-6 mb-2">
                                                <p class="mb-0 fw-bold">Tempat Lahir:</p>
                                                <p class="text-muted">
                                                    {{ textToUppercase(props.detail?.birthplace) }}
                                                </p>
                                            </div>
                                            <div class="col-xl-6 col-6 mb-2">
                                                <p class="mb-0 fw-bold">Tanggal Lahir:</p>
                                                <p class="text-muted">
                                                    {{ moment(props.detail?.birthdate).format("DD-MM-YYYY") }}
                                                </p>
                                            </div>
                                            <div class="col-xl-6 col-6 mb-2">
                                                <p class="mb-0 fw-bold">Jenis Kelamin:</p>
                                                <p class="text-muted">{{ props.detail?.gender == 'male'
                                                    ? 'Laki-laki' : 'Perempuan' }}</p>
                                            </div>
                                            <div class="col-xl-6 col-6 mb-2">
                                                <p class="mb-0 fw-bold">Agama:</p>
                                                <p class="text-muted">
                                                    {{ textToUppercase(props.detail?.religion) }}
                                                </p>
                                            </div>
                                            <div class="col-xl-6 col-6 mb-2">
                                                <p class="mb-0 fw-bold">Email:</p>
                                                <p class="text-muted">{{ props.detail?.user.email }}</p>
                                            </div>
                                            <div class="col-xl-6 col-6 mb-2">
                                                <p class="mb-0 fw-bold">Nomor Telepon:</p>
                                                <p class="text-muted">{{ props.detail?.number_phone }}</p>
                                            </div>
                                            <div class="col-xl-6 col-6 mb-2">
                                                <p class="mb-0 fw-bold">Alamat Lengkap:</p>
                                                <p class="text-muted">{{ props.detail?.address }}</p>
                                            </div>
                                        </div>
                                    </div>


                                    <div>
                                        <h5 class="text-secondary mb-3 text-uppercase"><i
                                                class="fas fa-graduation-cap me-2"></i>Data
                                            Pendidikan</h5>

                                        <div class="row detail-row education">
                                            <div class="col-xl-6 col-6 mb-2">
                                                <p class="mb-0 fw-bold">Tahun Masuk:</p>
                                                <p class="text-muted">{{ props.detail?.entry_year ?? '-' }}</p>
                                            </div>
                                            <div class="col-xl-6 col-6 mb-2">
                                                <p class="mb-0 fw-bold">Pendidikan Terakhir:</p>
                                                <p class="text-muted">{{ props.detail?.education }}</p>
                                            </div>
                                            <div class="col-xl-6 col-6 mb-2">
                                                <p class="mb-0 fw-bold">Jurusan:</p>
                                                <p class="text-muted">{{ textToUppercase(props.detail?.major) }}</p>
                                            </div>
                                            <div class="col-xl-6 col-6 mb-2">
                                                <p class="mb-0 fw-bold">Tahun Lulus:</p>
                                                <p class="text-muted">{{ props.detail?.graduation_year ?? '-' }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <h5 class="text-secondary mb-3 text-uppercase"><i
                                                class="bi bi-info-circle me-2"></i> Data
                                            Pegawai</h5>
                                        <div class="row detail-row">
                                            <div class="col-xl-6 col-6 mb-2">
                                                <p class="mb-0 fw-bold">ID Karyawan:</p>
                                                <p class="text-muted">{{ props.detail?.employee_id_number ?? '-' }}</p>
                                            </div>
                                            <div class="col-xl-6 col-6 mb-2">
                                                <p class="mb-0 fw-bold">Status:</p>
                                                <p class="text-muted">{{ textToUppercase(props.detail?.employee_status)
                                                    ??
                                                    '-' }}</p>
                                            </div>
                                            <div class="col-xl-6 col-6 mb-2">
                                                <p class="mb-0 fw-bold">Cabang/Unit:</p>
                                                <p class="text-muted">{{ textToUppercase(props.detail?.branch.name) ??
                                                    'Tidak Ditentukan' }}
                                                </p>
                                            </div>
                                            <div class="col-xl-6 col-6 mb-2">
                                                <p class="mb-0 fw-bold">Tanggal Masuk:</p>
                                                <p class="text-muted">
                                                    {{ moment(props.detail?.date_of_entry).format("DD-MM-YYYY") }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </app-layout>
</template>
<style scoped>
.profile-wrapper {
    padding-top: 1.5rem;
    padding-bottom: 1.5rem;
    border-left: 0;
    border-top: 0;
    border-bottom: 0;
    border-right: 1px solid #ccc;
    color: #222222 !important;
    /* background-color: rgba(238, 237, 237, 0.479); */
}

/* Gaya untuk membuat gambar profil bulat dan berukuran besar */
.profile-image-container {
    /* Ukuran wadah gambar */
    width: 180px;
    height: 180px;
    overflow: hidden;
    /* Penting untuk menjaga bentuk bulat */
    border-radius: 50%;
    /* Bayangan opsional */
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
}

/* Gaya untuk gambar di dalam wadah */
.profile-img-circle {
    width: 100%;
    height: 100%;
    /* Pastikan gambar menutupi area wadah tanpa terdistorsi */
    object-fit: fill;
    border-radius: 50%;
}

/* Gaya untuk baris detail agar lebih terstruktur */
.employee_info,
.personal,
.education {
    border-bottom: 1px solid #ccc;
    margin-bottom: 20px;
}

.detail-row p {
    font-size: 0.95rem;
    margin-bottom: 10px;
}


@media (max-width: 767.98px) {

    /* Di perangkat kecil, buat gambar sedikit lebih kecil dan pastikan rata tengah */
    .profile-image-container {
        width: 150px;
        height: 150px;
    }
}
</style>
