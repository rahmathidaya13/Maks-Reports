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

function daysTranslate(dayValue) {
    const dayConvert = {
        "Sunday": "Minggu",
        "Monday": "Senin",
        "Tuesday": "Selasa",
        "Wednesday": "Rabu",
        "Thursday": "Kamis",
        "Friday": "Jumat",
        "Saturday": "Sabtu",
    };
    const dateFormat = moment(dayValue).format("DD/MM/YYYY");
    return dateFormat;
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

            <callout :type="$page.props.flash.message ? 'success' : 'danger'" :duration="10"
                :message="$page.props.flash.message || $page.props.flash.error" />

            <div class="row g-4 pb-4">

                <div class="col-xl-4 col-lg-5">
                    <div class="card card-profile border-0 shadow-sm rounded-4 overflow-hidden position-sticky"
                        style="top:80px">

                        <div class="card-header-banner bg-gradient-primary"></div>

                        <div class="card-body text-center p-4 position-relative">
                            <div class="profile-avatar-wrapper mx-auto mb-3">
                                <img :src="imageSource" alt="Foto Profil"
                                    class="img-fluid rounded-circle shadow border border-4 border-white object-fit-cover"
                                    style="width: 120px; height: 120px;">
                            </div>

                            <h5 class="fw-bold text-dark mb-1">
                                {{ textToUppercase(props.detail?.user.name) ?? 'NAMA PENGGUNA' }}
                            </h5>
                            <p class="text-primary fw-semibold mb-2">
                                {{ textToUppercase(props.detail?.job_title.title) ?? 'Jabatan Tidak Diketahui' }}
                            </p>
                            <div class="badge bg-light text-secondary border px-3 py-2 rounded-pill mb-4">
                                {{ props.detail?.employee_id_number ?? 'No ID' }}
                            </div>

                            <div class="d-flex justify-content-center gap-2 mb-4">
                                <a :href="'mailto:' + props.detail?.user.email"
                                    class="btn btn-light btn-sm rounded-circle shadow-sm" title="Email">
                                    <i class="fas fa-envelope text-danger"></i>
                                </a>
                                <a href="#" class="btn btn-light btn-sm rounded-circle shadow-sm" title="Telepon">
                                    <i class="fas fa-phone text-success"></i>
                                </a>
                            </div>

                            <button @click="goToEditProfile(props.detail?.users_id)"
                                class="btn btn-primary w-100 rounded-3 shadow-sm btn-hover-effect">
                                <i class="fas fa-user-edit me-2"></i> Perbarui Profil
                            </button>
                        </div>

                        <div class="card-footer bg-white border-0 py-2 text-center mb-2">
                            <small class="text-muted text-uppercase fs-8 fw-bold ls-1">Status:
                                <span class="text-success">
                                    {{ textToUppercase(props.detail?.employee_status) ?? 'AKTIF' }}
                                </span>
                            </small>
                        </div>
                    </div>
                </div>

                <div class="col-xl-8 col-lg-7">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-4">

                            <div class="mb-5">
                                <div class="d-flex align-items-center mb-4 pb-2 border-bottom">
                                    <div class="icon-box bg-primary bg-opacity-10 text-primary rounded-3 me-3">
                                        <i class="bi bi-person-lines-fill fs-5"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-0 text-uppercase ls-1">Informasi Pribadi</h6>
                                </div>

                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="info-group">
                                            <label class="text-muted text-uppercase fs-8 mb-1">Nomor Induk Kependudukan
                                                (NIK)</label>
                                            <div class="fw-bold text-dark">{{ props.detail?.national_id_number || '-' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-group">
                                            <label class="text-muted text-uppercase fs-8 mb-1">Email</label>
                                            <div class="fw-bold text-dark">{{ props.detail?.user.email }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-group">
                                            <label class="text-muted text-uppercase fs-8 mb-1">Tempat, Tanggal
                                                Lahir</label>
                                            <div class="fw-bold text-dark">
                                                {{ textToUppercase(props.detail?.birthplace) }}, {{
                                                    daysTranslate(props.detail?.birthdate) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-group">
                                            <label class="text-muted text-uppercase fs-8 mb-1">Agama
                                            </label>
                                            <div class="fw-bold text-dark">
                                                {{ textToUppercase(props.detail?.religion) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-group">
                                            <label class="text-muted text-uppercase fs-8 mb-1">Jenis Kelamin </label>
                                            <div class="fw-bold text-dark">
                                                {{ props.detail?.gender == 'male' ? 'Laki-laki' : 'Perempuan' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-group">
                                            <label class="text-muted text-uppercase fs-8 mb-1">Kontak Telepon</label>
                                            <div class="fw-bold text-dark">{{ props.detail?.number_phone || '-' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="info-group p-3 bg-light rounded-3">
                                            <label class="text-muted text-uppercase fs-8 mb-1"><i
                                                    class="fas fa-map-marker-alt me-1"></i>Alamat Lengkap</label>
                                            <div class="fw-bold text-dark">{{ props.detail?.address || '-' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-5">
                                <div class="d-flex align-items-center mb-4 pb-2 border-bottom">
                                    <div class="icon-box bg-info bg-opacity-10 text-info rounded-3 me-3">
                                        <i class="fas fa-graduation-cap fs-5"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-0 text-uppercase ls-1">Riwayat Pendidikan</h6>
                                </div>

                                <div class="row g-4">
                                    <div class="col-md-4">
                                        <div class="info-group">
                                            <label class="text-muted text-uppercase fs-8 mb-1">Jenjang</label>
                                            <div class="fw-bold text-dark">{{ props.detail?.education }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="info-group">
                                            <label class="text-muted text-uppercase fs-8 mb-1">Jurusan / Program
                                                Studi</label>
                                            <div class="fw-bold text-dark">{{ textToUppercase(props.detail?.major) ||
                                                '-' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-group">
                                            <label class="text-muted text-uppercase fs-8 mb-1">Tahun Masuk</label>
                                            <div class="fw-bold text-dark">{{ props.detail?.entry_year || '-' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-group">
                                            <label class="text-muted text-uppercase fs-8 mb-1">Tahun Lulus</label>
                                            <div class="fw-bold text-dark">{{ props.detail?.graduation_year || '-' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <div class="d-flex align-items-center mb-4 pb-2 border-bottom">
                                    <div class="icon-box bg-success bg-opacity-10 text-success rounded-3 me-3">
                                        <i class="fas fa-briefcase fs-5"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-0 text-uppercase ls-1">Data Kepegawaian</h6>
                                </div>

                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="info-group">
                                            <label class="text-muted text-uppercase fs-8 mb-1">ID Karyawan</label>
                                            <div class="fw-bold text-dark">{{
                                                props.detail?.employee_id_number || '-' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-group">
                                            <label class="text-muted text-uppercase fs-8 mb-1">Tanggal Bergabung</label>
                                            <div class="fw-bold text-dark">{{
                                                daysTranslate(props.detail?.date_of_entry) }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-group">
                                            <label class="text-muted text-uppercase fs-8 mb-1">Cabang / Unit
                                                Kerja</label>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-building text-secondary me-2"></i>
                                                <span class="fw-bold text-dark">{{
                                                    textToUppercase(props.detail?.branch.name) || 'PUSAT' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-group">
                                            <label class="text-muted text-uppercase fs-8 mb-1">Status
                                                Kepegawaian</label>
                                            <span
                                                class="badge bg-success bg-opacity-10 text-success border border-success px-3 py-2">
                                                {{ textToUppercase(props.detail?.employee_status) || '-' }}
                                            </span>
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
    width: 250px;
    height: 300px;
    overflow: hidden;
    /* Penting untuk menjaga bentuk bulat */
    /* border-radius: 50%; */
    /* Bayangan opsional */
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
}

/* Gaya untuk gambar di dalam wadah */
.profile-img-circle {
    width: 100%;
    height: 100%;
    /* Pastikan gambar menutupi area wadah tanpa terdistorsi */
    object-fit: fill;
    /* border-radius: 50%; */
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


/* Styling Kartu Profil Kiri */
.card-profile {
    background: #fff;
    transition: transform 0.3s;
}

/* Banner Gradient di header kartu */
.card-header-banner {
    height: 100px;
    background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
    /* Biru ke Cyan */
    width: 100%;
}

/* Wrapper agar foto naik ke atas banner */
.profile-avatar-wrapper {
    margin-top: -60px;
    /* Menarik foto ke atas */
    position: relative;
    z-index: 2;
}

/* Typography Helpers */
.fs-8 {
    font-size: 0.75rem !important;
}

.ls-1 {
    letter-spacing: 1px;
}

/* Icon Box untuk Header Section */
.icon-box {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Animasi Halus Tombol */
.btn-hover-effect {
    transition: all 0.3s ease;
}

.btn-hover-effect:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
}

/* Info Group Styling */
.info-group label {
    display: block;
    font-weight: 600;
    letter-spacing: 0.5px;
}

.info-group .fw-bold {
    font-size: 0.95rem;
}
</style>
