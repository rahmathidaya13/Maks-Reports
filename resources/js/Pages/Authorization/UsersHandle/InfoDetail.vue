<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { formatTextFromSlug } from '../../../helpers/formatTextFromSlug';
import moment from 'moment';
import { computed, ref } from 'vue';
moment.locale('id');
const props = defineProps({
    users: Object
})
const getBadgeClass = (permName) => {
    switch (true) {
        case /create|download/i.test(permName):
            return 'bg-success'
        case /edit|update/i.test(permName):
            return 'bg-warning text-dark'
        case /delete|remove|cancel/i.test(permName):
            return 'bg-danger'
        case /read|share|export|import/i.test(permName):
            return 'bg-info text-white'
        case /manage|access|assign/i.test(permName):
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
</script>
<template>

    <Head title="Detail Profil Pengguna" />
    <app-layout>
        <template #content>
            <bread-crumbs :home="false" icon="fas fa-user-cog" title="Detail Profil Pengguna"
                :items="[{ text: 'Detail Profil Pengguna' }]" />
            <!-- <alert :duration="10" :message="message" /> -->
            <div class="row">
                <div class="col-xl-12 col-sm-12">

                    <div class="card shadow-sm border-0 rounded-3">
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
                                    <span class="badge w-25 mt-2"
                                        :class="users.is_active ? 'bg-primary' : 'bg-secondary'">
                                        {{ users.is_active ? 'online' : 'offline' }}
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
                                        moment(users.profile.birthdate).format('LL') : '-'
                                    }}</div>
                                </div>
                                <div class="col-xl-6 col-md-6 col-sm-12 mb-2">
                                    <span class="text-muted">Tanggal Bergabung</span>
                                    <div class="fw-semibold">{{ users.profile.date_of_entry ?
                                        moment(users.profile.date_of_entry).format('LL') : '-' }}</div>
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
                                    <span class="text-muted">Lokasi/Penampatan</span>
                                    <div class="fw-semibold">{{ users.profile.branch ?
                                        formatTextFromSlug(users.profile.branch.name) : '-' }}
                                    </div>
                                </div>

                                <div class="col-xl-6 col-md-6 col-sm-12 mb-2">
                                    <span class="text-muted">Alamat</span>
                                    <div class="fw-semibold">{{ users.profile.address ?? '-' }}</div>
                                </div>
                                <div class="col-xl-6 col-md-6 col-sm-12 mb-2">
                                    <span class="text-muted">Peran/Tugas</span>
                                    <div class="fw-semibold">{{formatTextFromSlug(users.roles.map((role) =>
                                        role.name).join(', ')) ?? '-'}}
                                    </div>
                                </div>

                            </div>

                            <hr>
                            <!-- Role dan Permission -->
                            <h5 class="fw-semibold mb-2">Hak Akses</h5>
                            <div class="row mb-3">
                                <div class="col-xl-6 col-sm-12 col-md-6 border p-3 rounded-4">
                                    <ul class="list-unstyled mb-0 d-flex flex-wrap gap-2">
                                        <li v-for="permission in users.permissions" :key="permission">
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

                            <!-- Tombol Aksi -->
                            <div class="mt-4 d-flex justify-content-start flex-wrap">
                                <Link :href="route('users')" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali
                                </Link>
                                <Link :href="route('users.edit', users.id)" class="btn btn-primary ms-2">
                                <i class="bi bi-pencil-square"></i> Ubah
                                </Link>
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
