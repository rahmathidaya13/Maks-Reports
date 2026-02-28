<script setup>
import { router } from "@inertiajs/vue3";
import { computed } from "vue";
import { onMounted, ref } from "vue";
import { hasRole, hasPermission } from "@/composables/useAuth";
const loaderActive = ref(null);

const goToDashboard = () => {
    const checkUserRole = hasRole(['developer', 'admin']);
    const routeName = checkUserRole ? 'admin.dashboard.index' : 'home';
    loaderActive.value?.show("Memproses...");
    router.get(route(routeName), {}, {
        onFinish: () => loaderActive.value?.hide(),
        onError: () => loaderActive.value?.hide(),
        preserveScroll: true,
        replace: true,
        preserveState: true,
    })
}
</script>
<template>
    <loader-page ref="loaderActive" />
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <!-- Navbar Brand-->
        <button type="button" class="navbar-brand text-start bg-transparent border-0" @click.prevent="goToDashboard">{{
            $page.props.app.name
            }}</button>
        <!-- Sidebar Toggle-->
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>

        <!-- <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown"
                    aria-expanded="false"><i class="fas fa-user fa-fw"></i></a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="#!">Settings</a></li>
                    <li><a class="dropdown-item" href="#!">Ganti</a></li>
                    <li>
                        <hr class="dropdown-divider" />
                    </li>
                    <li><a class="dropdown-item" href="#!">Logout</a></li>
                </ul>
            </li>
        </ul> -->
    </nav>
</template>
