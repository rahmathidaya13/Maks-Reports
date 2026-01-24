<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { formatTextFromSlug } from "@/helpers/formatTextFromSlug";
import moment from "moment";
const props = defineProps({
    users: Object,
    show: Boolean,
});
const emit = defineEmits(["update:show"]);
const close = () => {
    emit("update:show", false);
}
const getBadgeClass = (permName) => {
    switch (true) {
        case /create|export|import/i.test(permName):
            return 'badge-soft-success'; // Hijau lembut
        case /edit|update/i.test(permName):
            return 'badge-soft-warning'; // Oranye/Kuning lembut
        case /delete|remove/i.test(permName):
            return 'badge-soft-danger';  // Merah lembut
        case /view|show/i.test(permName):
            return 'badge-soft-info';    // Biru muda lembut
        case /manage|access|assign|share/i.test(permName):
            return 'badge-soft-primary'; // Biru tua lembut
        default:
            return 'badge-soft-secondary'; // Abu-abu lembut
    }
}
const imageSource = computed(() => {
    if (props.users?.profile.images != null) {
        return `/storage/${props.users?.profile.images}`
    }
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(props.users?.name)}`
})
function daysOnlyConvert(dayValue) {
    const dayConvert = {
        "Sunday": "Minggu",
        "Monday": "Senin",
        "Tuesday": "Selasa",
        "Wednesday": "Rabu",
        "Thursday": "Kamis",
        "Friday": "Jumat",
        "Saturday": "Sabtu",
    };
    const dayName = moment(dayValue).format('dddd');
    const dateFormat = moment(dayValue).format('DD/MM/YYYY');
    return dayConvert[dayName] + ", " + dateFormat ?? dayName;
}
const handleEditFromDetail = (id) => {
    window.location.href = route("users.edit", id);
}

</script>
<template>
    <div class="row" v-if="show">
        <div class="col-xl-12 col-sm-12">
            <modal size="modal-lg" :footer="false" icon="fas fa-info-circle" :show="show" title="Informasi Pengguna"
                @closed="close">
                <template #body>
                    <div class="user-detail-container">
                        <div class="profile-header-card d-flex align-items-center p-4 mb-4">
                            <div class="position-relative">
                                <img :src="imageSource" :alt="users.name"
                                    class="rounded-circle profile-img-lg border border-4 border-white shadow-sm" />
                                <span
                                    :class="['status-indicator-lg', users.is_active ? 'bg-success' : 'bg-secondary']"></span>
                            </div>
                            <div class="ms-4">
                                <h3 class="fw-bold mb-1 text-dark">{{ users.name }}</h3>
                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                    <span class="text-muted"><i class="far fa-envelope me-1"></i> {{ users.email ?? '-'
                                    }}</span>
                                    <span class="badge-dot-divider"></span>
                                    <span class="text-muted"><i class="fas fa-phone-alt me-1"></i> {{
                                        users.profile.number_phone ?? '-' }}</span>
                                </div>
                                <div class="mt-2 d-flex gap-2">

                                    <span
                                        :class="users.status === 'active' ? 'badge-soft-primary' : 'badge-soft-danger'">
                                        Status: {{ users.status ? 'Aktif' : 'Tidak Aktif' }}
                                    </span>

                                    <button @click.prevent="handleEditFromDetail(users.id)"
                                        class="badge-soft-success">
                                        <i class="fas fa-user-edit me-2"></i>
                                        <span class="fw-bold">Ubah Akses Pengguna</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-7">
                                <div class="info-group h-100">
                                    <h6 class="section-title"><i class="fas fa-user-tag me-2"></i>Informasi Umum</h6>
                                    <div class="info-grid shadow-sm border rounded-4 p-3 bg-white">
                                        <div class="row g-3">
                                            <div class="col-6">
                                                <label>Tanggal Lahir</label>
                                                <p>{{ users.profile.birthdate ? daysOnlyConvert(users.profile.birthdate)
                                                    : '-' }}</p>
                                            </div>
                                            <div class="col-6">
                                                <label>Gender</label>
                                                <p>{{ users.profile.gender == 'male' ? 'Laki-laki' : 'Perempuan' }}</p>
                                            </div>
                                            <div class="col-6">
                                                <label>Pendidikan</label>
                                                <p>{{ formatTextFromSlug(users.profile.education) ?? '-' }}</p>
                                            </div>
                                            <div class="col-6">
                                                <label>Tgl Bergabung</label>
                                                <p>{{ users.profile.date_of_entry ?
                                                    daysOnlyConvert(users.profile.date_of_entry) : '-' }}</p>
                                            </div>
                                            <div class="col-12 border-top pt-2 mt-2">
                                                <label>Alamat</label>
                                                <p class="mb-0 text-wrap">{{ users.profile.address ?? '-' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-5">
                                <div class="info-group h-100">
                                    <h6 class="section-title"><i class="fas fa-briefcase me-2"></i>Pekerjaan</h6>
                                    <div class="info-grid shadow-sm border rounded-4 p-3 bg-white">
                                        <div class="d-grid gap-3">
                                            <div>
                                                <label>Jabatan Utama</label>
                                                <p class="text-primary fw-bold">{{ users.profile.job_title ?
                                                    formatTextFromSlug(users.profile.job_title.title) : '-' }}</p>
                                            </div>
                                            <div>
                                                <label>Cabang / Unit</label>
                                                <p>{{ users.profile.branch ?
                                                    formatTextFromSlug(users.profile.branch.name) : '-' }}</p>
                                            </div>
                                            <div>
                                                <label>Peran (Role)</label>
                                                <p class="mb-0"><span
                                                        class="badge bg-light text-dark border fw-semibold">{{
                                                            formatTextFromSlug(users.roles[0]) ?? '-' }}</span></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 mt-4">
                                <div class="info-group">
                                    <h6 class="section-title d-flex justify-content-between">
                                        <span><i class="fas fa-key me-2"></i>Hak Akses Spesifik</span>
                                        <small class="text-muted fw-normal">{{ users.permissions.length }} Izin</small>
                                    </h6>
                                    <div class="permission-card border rounded-4 p-3 bg-light-subtle">
                                        <div class="d-flex flex-wrap gap-2">
                                            <span v-for="permission in users.permissions" :key="permission.id"
                                                :class="['badge-permission shadow-sm', getBadgeClass(permission.name)]">
                                                {{ formatTextFromSlug(permission.name) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </modal>
        </div>
    </div>
</template>
<style scoped>
/* Container Utama */
.user-detail-container {
    padding: 10px 5px;
    max-height: 70vh;
    /* Batasi tinggi agar tidak melebihi layar */
    overflow-y: auto;
    /* Scroll internal jika data sangat panjang */
    overflow-x: hidden;
}

/* Custom Scrollbar untuk Modal */
.user-detail-container::-webkit-scrollbar {
    width: 6px;
}

.user-detail-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.user-detail-container::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}

.user-detail-container::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

.profile-header-card {
    background: linear-gradient(135deg, #f8faff 0%, #ffffff 100%);
    border: 1px solid #edf2f9;
    border-radius: 20px;
}

.profile-img-lg {
    width: 90px;
    height: 90px;
    object-fit: cover;
}

.status-indicator-lg {
    position: absolute;
    bottom: 5px;
    right: 5px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    border: 3px solid #fff;
}

/* Typography */
.section-title {
    font-size: 0.85rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #64748b;
    margin-bottom: 12px;
}

.info-grid label {
    display: block;
    font-size: 0.75rem;
    font-weight: 600;
    color: #94a3b8;
    margin-bottom: 2px;
}

.info-grid p {
    font-size: 0.9rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0;
}

/* CSS untuk Soft Badges (Warna Pastel) */
.badge-soft-success {
    background-color: #dcfce7 !important;
    color: #15803d !important;
}

.badge-soft-warning {
    background-color: #fef9c3 !important;
    color: #a16207 !important;
}

.badge-soft-danger {
    background-color: #fee2e2 !important;
    color: #b91c1c !important;
}

.badge-soft-info {
    background-color: #e0f2fe !important;
    color: #0369a1 !important;
}

.badge-soft-primary {
    background-color: #e0e7ff !important;
    color: #4338ca !important;
}

.badge-soft-secondary {
    background-color: #f1f5f9 !important;
    color: #475569 !important;
}

/* Pastikan badge punya padding yang pas */
.badge-permission,
.badge-soft-success,
.badge-soft-warning,
.badge-soft-danger,
.badge-soft-info,
.badge-soft-primary,
.badge-soft-secondary {
    padding: 6px 12px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.75rem;
    display: inline-block;
    border: none;
}


/* Tombol Edit Soft */
.btn-warning-soft {
    background-color: #fef9c3;
    color: #a16207;
    border: 1px solid #fef08a;
    padding: 8px 16px;
    border-radius: 12px;
    transition: all 0.2s ease;
}

.btn-warning-soft:hover {
    background-color: #fef08a;
    color: #854d0e;
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.btn-warning-soft:active {
    transform: translateY(0);
}
</style>
