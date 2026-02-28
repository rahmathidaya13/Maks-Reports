<script setup>
import { Link, router, usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import { onMounted, ref } from "vue";
import { hasRole, hasPermission } from "@/composables/useAuth";

const page = usePage();
const currentPath = page.props.path;
const getId = page.props.path.split('/')[2];
const is = (pattern) => {
    const regex = new RegExp("^" + pattern.replace("*", ".*") + "$");
    return regex.test(currentPath);
};
const textToUppercase = (text) => {
    return text.replace(/\b\w/g, l => l.toUpperCase())
}

const loaderActive = ref(null);

const navigateTo = (routeName, params = {}, message = "Sedang membuka...") => {
    loaderActive.value?.show(message);
    router.get(route(routeName, params), {}, {
        onFinish: () => loaderActive.value?.hide(),
        onError: () => loaderActive.value?.hide(),
        preserveScroll: true,
        replace: true,
        preserveState: true,
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
                            <Link @click.prevent="navigateTo('profile.detail', (page.props.auth.user.id))"
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

                    <Link v-if="hasRole(['user', 'developer'])" @click.prevent="navigateTo('home')" class="nav-link"
                        :class="{ 'active active-link': is('dashboard*') }" :href="route('home')">
                        <div class="sb-nav-link-icon">
                            <i class="fas fa-tachometer-alt"></i>
                        </div>
                        Dashboard
                        <small class="ms-auto" style="font-size: 12px;">
                            U
                        </small>
                    </Link>

                    <Link v-if="hasRole(['developer', 'admin'])" @click.prevent="navigateTo('admin.dashboard.index')"
                        class="nav-link" :class="{ 'active active-link': is('admin*') }"
                        :href="route('admin.dashboard.index')">
                        <div class="sb-nav-link-icon">
                            <i class="fas fa-tachometer-alt"></i>
                        </div>
                        Dashboard
                        <small class="ms-auto" style="font-size: 12px;">
                            A
                        </small>
                    </Link>


                    <div class="sb-sidenav-menu-heading" v-if="hasPermission('product.view')">Produk
                    </div>

                    <Link v-if="hasPermission('product.view')" @click.prevent="navigateTo('product')"
                        :href="route('product')" class="nav-link" :class="{ 'active active-link': is('product*') }">
                        <div class="sb-nav-link-icon">
                            <i class="fas fa-tags"></i>
                        </div>
                        Daftar Produk
                    </Link>



                    <div class="sb-sidenav-menu-heading" v-if="hasPermission('transaction.view')">Laporan
                    </div>

                    <Link v-if="hasPermission('transaction.view')" @click.prevent="navigateTo('transaction')"
                        :href="route('transaction')" class="nav-link"
                        :class="{ 'active active-link': is('transaction*') }">
                        <div class="sb-nav-link-icon">
                            <i class="fas fa-money-bill"></i>
                        </div>
                        Transaksi
                    </Link>

                    <Link @click.prevent="navigateTo('customers')" v-if="hasPermission('customers.view')"
                        :href="route('customers')" class="nav-link" :class="{ 'active active-link': is('customers*') }">
                        <div class="sb-nav-link-icon">
                            <i class="fas fa-user-tag"></i>
                        </div>
                        Daftar Pelanggan
                    </Link>

                    <Link v-if="hasPermission('daily.report.leads.view')" @click.prevent="navigateTo('daily_report')"
                        class="nav-link" :class="{ 'active active-link': is('daily_report*') }"
                        :href="route('daily_report')">
                        <div class="sb-nav-link-icon">
                            <i class="fas fa-clipboard"></i>
                        </div>
                        Laporan Leads
                    </Link>

                    <Link v-if="hasPermission('status.report.view')" @click.prevent="navigateTo('story_report')"
                        class="nav-link" :class="{ 'active active-link': is('story_report*') }"
                        :href="route('story_report')">
                        <div class="sb-nav-link-icon">
                            <i class="fas fa-sticky-note"></i>
                        </div>
                        Laporan Status
                    </Link>


                    <div v-if="hasRole(['developer', 'admin'])" class="sb-sidenav-menu-heading">Manajemen
                    </div>

                    <Link @click.prevent="navigateTo('job_title')"
                        v-if="hasRole(['developer', 'admin']) && hasPermission('job.title.view')" class="nav-link"
                        :class="{ 'active active-link': is('job_title*') }" :href="route('job_title')">
                        <div class="sb-nav-link-icon">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        Daftar Jabatan
                    </Link>

                    <Link @click.prevent="navigateTo('branch')"
                        v-if="hasRole(['developer', 'admin']) && hasPermission('branches.view')" class="nav-link"
                        :class="{ 'active active-link': is('branch*') }" :href="route('branch')">
                        <div class="sb-nav-link-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        Daftar Cabang
                    </Link>

                    <div v-if="hasRole('developer')" class="sb-sidenav-menu-heading">Otorisasi
                    </div>

                    <Link @click.prevent="navigateTo('roles')" v-if="hasRole('developer')" class="nav-link"
                        :class="{ 'active active-link': is('authorization/roles*') }" :href="route('roles')">
                        <div class="sb-nav-link-icon">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        Peran
                    </Link>

                    <Link @click.prevent="navigateTo('permissions')" v-if="hasRole('developer')" class="nav-link"
                        :class="{ 'active active-link': is('authorization/permissions*') }"
                        :href="route('permissions')">
                        <div class="sb-nav-link-icon">
                            <i class="fas fa-cog"></i>
                        </div>
                        Izin Akses
                    </Link>

                    <Link @click.prevent="navigateTo('users')" v-if="hasRole('developer')" class="nav-link"
                        :class="{ 'active active-link': is('authorization/users*') }" :href="route('users')">
                        <div class="sb-nav-link-icon">
                            <i class="fas fa-user-cog"></i>
                        </div>
                        Izin Pengguna
                    </Link>




                    <Link @click.prevent="navigateTo('logout', {}, 'Keluar dari sistem...')" class="nav-link"
                        :href="route('logout')">
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

/* Animasi Denyut agar admin notice */
.animate-pulse {
    animation: pulse 1s infinite;
    border: 1px solid #919191;
}

@keyframes pulse {
    0% {
        transform: scale(0.95);
        box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
    }

    70% {
        transform: scale(1);
        box-shadow: 0 0 0 10px rgba(220, 53, 69, 0);
    }

    100% {
        transform: scale(0.95);
        box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
    }
}
</style>
