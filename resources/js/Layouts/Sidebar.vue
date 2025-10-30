<script setup>
import { Link, usePage } from "@inertiajs/vue3";
const page = usePage();
const currentPath = page.props.path;
const getId = page.props.path.split('/')[2];
const is = (pattern) => {
    const regex = new RegExp("^" + pattern.replace("*", ".*") + "$");
    return regex.test(currentPath);
};
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

                    <Link class="nav-link" :class="{ 'active active-link': is('roles*') }" :href="route('roles')">
                    <div class="sb-nav-link-icon">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    Jabatan
                    </Link>

                    <Link class="nav-link" :class="{ 'active active-link': is('roles*') }" :href="route('roles')">
                    <div class="sb-nav-link-icon">
                        <i class="fas fa-cogs"></i>
                    </div>
                    Otoritas
                    </Link>


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
