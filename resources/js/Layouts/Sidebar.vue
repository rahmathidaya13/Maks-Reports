<script setup>
import { Link, usePage } from "@inertiajs/vue3";
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

onMounted(() => {
    const userRoles = page.props.auth.user.roles || [];
    roles.value = priority.find(roles => userRoles.includes(roles)) || "";
});

</script>
<template>
    <div id="layoutSidenav_nav">
        <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
            <div class="sb-sidenav-menu">
                <div class="nav">
                    <div class="sb-sidenav-menu-heading">Home</div>
                    <Link class="nav-link" :class="{ 'active active-link': is('home*') }" :href="route('home')">
                    <div class="sb-nav-link-icon">
                        <i class="fas fa-tachometer-alt"></i>
                    </div>
                    Dashboard
                    </Link>

                    <Link class="nav-link" :class="{ 'active active-link': is('daily_report*') }"
                        :href="route('daily_report')">
                    <div class="sb-nav-link-icon">
                        <i class="fas fa-clipboard"></i>
                    </div>
                    Laporan Harian
                    </Link>

                    <Link class="nav-link" :class="{ 'active active-link': is('story_report*') }"
                        :href="route('story_report')">
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




                    <Link class="nav-link" :href="route('logout')">
                    <div class="sb-nav-link-icon">
                        <i class="fas fa-sign-out-alt"></i>
                    </div>
                    Logout
                    </Link>

                </div>
            </div>
        </nav>
    </div>
</template>
