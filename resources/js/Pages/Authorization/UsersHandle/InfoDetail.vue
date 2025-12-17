<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { formatTextFromSlug } from '@/helpers/formatTextFromSlug';
import moment from 'moment';
import { computed, ref } from 'vue';
moment.locale('id');
const props = defineProps({
    users: Object
})
const getBadgeClass = (permName) => {
    switch (true) {
        case /create|export|import/i.test(permName):
            return 'bg-success'
        case /edit|update/i.test(permName):
            return 'bg-warning text-dark'
        case /delete|remove/i.test(permName):
            return 'bg-danger'
        case /view|show/i.test(permName):
            return 'bg-info text-dark'
        case /manage|access|assign|share/i.test(permName):
            return 'bg-primary'
        default:
            return 'bg-secondary'
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
const loaderActive = ref(null);
const goToEdit = (id) => {
    loaderActive.value?.show("Sedang memuat data...");
    router.get(route("users.edit", id), {}, {
        onFinish: () => loaderActive.value?.hide()
    });
}
const goBack = (id) => {
    loaderActive.value?.show("Memproses...");
    router.get(route("users"), {}, {
        onFinish: () => loaderActive.value?.hide()
    });
}
</script>
<template>

    <Head title="Detail Profil Pengguna" />
    <app-layout>
        <template #content>
            <loader-page ref="loaderActive" />
            <bread-crumbs :home="false" icon="fas fa-user-cog" title="Detail Profil Pengguna"
                :items="[{ text: 'Detail Profil Pengguna' }]" />
            <!-- <alert :duration="10" :message="message" /> -->
            <div class="row pb-3">
                <div class="col-xl-12 col-sm-12">
                    <!-- Tombol Aksi -->
                    <div class="mb-3 d-flex gap-2">
                        <Link @click.prevent="goBack" :href="route('users')" class="btn btn-danger bg-gradient">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </Link>
                        <Link @click.prevent="goToEdit(users.id)" :href="route('users.edit', users.id)"
                            class="btn btn-primary bg-gradient px-4">
                            <i class="fas fa-edit"></i> Ubah
                        </Link>
                    </div>

                    <div class="card rounded-3 overflow-hidden shadow-sm">
                        <div class="card-body p-5">

                            <!-- Header Profil -->
                            <div class="d-flex align-items-center mb-4 flex-wrap ">
                                <div class="cover">
                                    <img :src="imageSource" :alt="users.name"
                                        class="rounded-circle me-3 border border-secondary images-detail" />
                                </div>

                                <div class="d-grid">
                                    <h4 class="mb-0 fw-bold">{{ users.name ?? '-' }}</h4>
                                    <span class="text-muted">{{ users.email ?? '-' }}</span>
                                    <span class="text-muted">{{ users.profile.number_phone ?? '-' }}</span>
                                    <span class="badge w-25 rounded-0"
                                        :class="users.is_active ? 'bg-primary' : 'bg-secondary'">
                                        {{ users.is_active ? 'Online' : 'Offline' }}
                                    </span>
                                </div>
                            </div>

                            <hr>

                            <!-- Informasi Umum -->
                            <h5 class="fw-semibold mb-3">Informasi Umum</h5>
                            <div class="row mb-3">
                                <div class="col-xl-6 col-md-6 col-sm-12 mb-2">
                                    <span class="text-muted">Tanggal Lahir</span>
                                    <div class="fw-semibold">{{ users.profile.birthdate ?
                                        daysOnlyConvert(users.profile.birthdate) : '-'
                                        }}</div>
                                </div>
                                <div class="col-xl-6 col-md-6 col-sm-12 mb-2">
                                    <span class="text-muted">Tanggal Bergabung</span>
                                    <div class="fw-semibold">{{ users.profile.date_of_entry ?
                                        daysOnlyConvert(users.profile.date_of_entry) : '-' }}</div>
                                </div>
                                <div class="col-xl-6 col-md-6 col-sm-12 mb-2">
                                    <span class="text-muted">Jenis Kelamin</span>
                                    <div class="fw-semibold">{{ users.profile.gender == 'male' ? 'Laki-laki' :
                                        'Perempuan' ?? '-' }}</div>
                                </div>
                                <div class="col-xl-6 col-md-6 col-sm-12 mb-2">
                                    <span class="text-muted">Pendidikan</span>
                                    <div class="fw-semibold">{{ formatTextFromSlug(users.profile.education) ?? '-'
                                        }}
                                    </div>
                                </div>

                                <div class="col-xl-6 col-md-6 col-sm-12 mb-2">
                                    <span class="text-muted">Jabatan</span>
                                    <div class="fw-semibold">{{ users.profile.job_title
                                        ? formatTextFromSlug(users.profile.job_title.title) : '-'
                                        }}
                                    </div>
                                </div>
                                <div class="col-xl-6 col-md-6 col-sm-12 mb-2">
                                    <span class="text-muted">Status Pegawai</span>
                                    <div class="fw-semibold">{{ users.status ? 'Aktif' : 'Tidak Aktif' }}
                                    </div>
                                </div>
                                <div class="col-xl-6 col-md-6 col-sm-12 mb-2">
                                    <span class="text-muted">Lokasi/Cabang</span>
                                    <div class="fw-semibold">{{ users.profile.branch ?
                                        formatTextFromSlug(users.profile.branch.name) : '-' }}
                                    </div>
                                </div>

                                <div class="col-xl-6 col-md-6 col-sm-12 mb-2">
                                    <span class="text-muted">Alamat</span>
                                    <div class="fw-semibold">{{ users.profile.address ?? '-' }}</div>
                                </div>
                                <div class="col-xl-6 col-md-6 col-sm-12 mb-2">
                                    <span class="text-muted">Roles</span>
                                    <div class="fw-semibold">{{ formatTextFromSlug(users.roles[0]) ?? '-' }}
                                    </div>
                                </div>

                            </div>

                            <hr>
                            <h5 class="fw-semibold mb-2">Hak Akses</h5>
                            <div class="row mb-3">
                                <div class="col-xl-12 col-sm-12 col-md-12 border p-3 rounded-3">
                                    <ul class="list-unstyled mb-0 d-inline-flex flex-wrap gap-2">
                                        <li :id="permission.id" v-for="permission in users.permissions"
                                            :key="permission.id">
                                            <span :class="[
                                                'badge text-capitalize px-3 py-2',
                                                getBadgeClass(permission.name)
                                            ]">
                                                {{ formatTextFromSlug(permission.name) }}
                                            </span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </template>
    </app-layout>
</template>
<style>
.images-detail {
    object-fit: fill;
    object-position: center;
    width: 100px;
    height: 100px;
}
</style>
