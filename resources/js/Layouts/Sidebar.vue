<script setup>
import { Link, router, usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import { onMounted, ref } from "vue";
const page = usePage();
const currentPath = page.props.path;
const getId = page.props.path.split('/')[2];
const is = (pattern) => {
    const regex = new RegExp("^" + pattern.replace("*", ".*") + "$");
    return regex.test(currentPath);
};
const roles = ref("");
const priority = ["super-admin", "developer", "admin", "editor"];
const textToUppercase = (text) => {
    return text.replace(/\b\w/g, l => l.toUpperCase())
}
onMounted(() => {
    const userRoles = page.props.auth.user.roles || [];
    roles.value = priority.find(roles => userRoles.includes(roles)) || "";
});
const loaderActive = ref(null);
const logout = () => {
    loaderActive.value?.show("Keluar Aplikasi...");
    router.get(route('logout'), {}, {
        onFinish: () => loaderActive.value?.hide()
    });
}
const storyReport = () => {
    loaderActive.value?.show("Sedang membuka...");
    router.get(route('story_report'), {}, {
        onFinish: () => loaderActive.value?.hide()
    });
}
const dailyReport = () => {
    loaderActive.value?.show("Sedang membuka...");
    router.get(route('daily_report'), {}, {
        onFinish: () => loaderActive.value?.hide()
    });
}
const dashboard = () => {
    loaderActive.value?.show("Sedang membuka...");
    router.get(route('home'), {}, {
        onFinish: () => loaderActive.value?.hide()
    });
}
const getDetailProfile = (id) => {
    loaderActive.value?.show("Sedang membuka...");
    router.get(route('profile.detail', id), {}, {
        onFinish: () => loaderActive.value?.hide()
    });
}


const imageSource = computed(() => {
    const users = page.props.auth.user
    if (users.profile.images != null) {
        return `/storage/${users.profile.images}`
    }
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(users.name)}`
})
const branch = page.props.auth.user.profile.branch;
const jobTitle = page.props.auth.user.profile.job_title;

</script>
<template>
    <div id="layoutSidenav_nav">
        <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
            <div class="sb-sidenav-menu">
                <div class="nav">
                    <div class="user-panel-sidebar d-flex align-items-center">
                        <div class="user-image-wrapper">
                            <img :src="imageSource" class="profile-image-sidebar" alt="User Image">
                        </div>
                        <div class="user-info-text">
                            <Link @click.prevent="getDetailProfile(page.props.auth.user.id)"
                                :href="route('profile.detail', page.props.auth.user.id)" class="user-name-link"
                                :class="{ 'active-link-prf': is('profile*') }">
                            {{ page.props.auth.user.name }}
                            </Link>
                            <small class="status">{{ textToUppercase(jobTitle.title) + `
                                (${textToUppercase(branch.name)})` }}
                            </small>
                        </div>
                    </div>
                    <div class="sb-sidenav-menu-heading">Home</div>
                    <Link @click.prevent="dashboard" class="nav-link" :class="{ 'active active-link': is('home*') }"
                        :href="route('home')">
                    <div class="sb-nav-link-icon">
                        <i class="fas fa-tachometer-alt"></i>
                    </div>
                    Dashboard
                    </Link>

                    <Link @click.prevent="dailyReport" class="nav-link"
                        :class="{ 'active active-link': is('daily_report*') }" :href="route('daily_report')">
                    <div class="sb-nav-link-icon">
                        <i class="fas fa-clipboard"></i>
                    </div>
                    Laporan Leads
                    </Link>

                    <Link @click.prevent="storyReport" class="nav-link"
                        :class="{ 'active active-link': is('story_report*') }" :href="route('story_report')">
                    <div class="sb-nav-link-icon">
                        <i class="fas fa-sticky-note"></i>
                    </div>
                    Laporan Status
                    </Link>

                    <Link v-if="roles" class="nav-link" :class="{ 'active active-link': is('job_title*') }"
                        :href="route('job_title')">
                    <div class="sb-nav-link-icon">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    Jabatan
                    </Link>

                    <div v-if="page.props.auth.user.roles == 'developer'" class="sb-sidenav-menu-heading">Otorisasi
                    </div>

                    <Link v-if="page.props.auth.user.roles == 'developer'" class="nav-link"
                        :class="{ 'active active-link': is('authorization/roles*') }" :href="route('roles')">
                    <div class="sb-nav-link-icon">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    Peran
                    </Link>

                    <Link v-if="page.props.auth.user.roles == 'developer'" class="nav-link"
                        :class="{ 'active active-link': is('authorization/permissions*') }"
                        :href="route('permissions')">
                    <div class="sb-nav-link-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    Izin Akses
                    </Link>
                    <Link v-if="page.props.auth.user.roles == 'developer'" class="nav-link"
                        :class="{ 'active active-link': is('authorization/users*') }" :href="route('users')">
                    <div class="sb-nav-link-icon">
                        <i class="fas fa-user-cog"></i>
                    </div>
                    Izin Pengguna
                    </Link>

                    <div class="sb-sidenav-menu-heading">Akun
                    </div>




                    <Link @click.prevent="logout" class="nav-link" :href="route('logout')">
                    <div class="sb-nav-link-icon">
                        <i class="fas fa-sign-out-alt"></i>
                    </div>
                    Keluar
                    </Link>

                </div>
            </div>
        </nav>
    </div>
    <loader-page ref="loaderActive" />
</template>

<style>
/* Gaya untuk wadah utama di sidebar */
.user-panel-sidebar {
    /* Tambahkan padding atau margin agar tidak menempel di tepi sidebar */
    padding: 10px;
    /* Pastikan tampilan flex sudah aktif (sesuai dengan d-flex) */
    display: flex;
    flex-direction: column;
    align-items: center;
    /* Rata tengah secara vertikal */
    color: #fff;
    /* Atau warna teks default sidebar Anda */
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    /* Garis pemisah opsional */
    justify-content: center;
}

/* Gaya untuk pembungkus gambar */
.user-image-wrapper {
    /* Menentukan ukuran wadah agar gambar pas */
    width: 75px;
    height: 75px;
    margin-bottom: 10px;
    /* Jarak antara gambar dan teks nama */
    flex-shrink: 0;
    /* Mencegah gambar menyusut */
}

/* Gaya untuk gambar profil itu sendiri */
.profile-image-sidebar {
    width: 100%;
    height: 100%;
    /* Membuat gambar menjadi bulat */
    border-radius: 50%;
    /* Garis pinggir opsional untuk menonjolkan gambar */
    border: 2px solid #d4d7da;
    /* Pastikan gambar menutupi area tanpa terdistorsi */
    object-fit: fill;
    object-position: center;

}

/* Gaya untuk wadah teks (nama pengguna) */
.user-info-text {
    overflow: hidden;
    /* Penting untuk menangani nama yang panjang */
    white-space: normal;
    /* Mencegah nama terpotong ke baris baru */
    text-overflow: ellipsis;
    text-align: center;
    /* Menambahkan '...' jika nama terlalu panjang */
}

/* Gaya untuk nama pengguna (Link/a) */
.user-name-link {
    /* Hapus dekorasi bawaan dari tautan */
    text-decoration: none;
    /* Atur warna teks, misalnya putih untuk sidebar gelap */
    color: #f7f7f7;
    display: block;
    /* Memastikan Link menempati lebar yang tersedia */
    /* Membuat nama pengguna lebih tebal */
    font-size: 1rem;
    /* Ukuran font yang sesuai untuk sidebar */
}

.status {
    color: #c9ccce
}

.user-name-link:hover {
    color: #39d1ff;
}

.active-link-prf {
    color: #1bbef0;
}
</style>
