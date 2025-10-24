<script setup>
import Navbar from "./Navbar.vue";
import Sidebar from "./Sidebar.vue";
import Breadcrumbs from "./Breadcrumbs.vue";
import Footer from "./Footer.vue";

import { onMounted } from "vue";

const toggleSidebar = () => {
  document.body.classList.toggle("sb-sidenav-toggled");
  localStorage.setItem(
    "sb|sidebar-toggle",
    document.body.classList.contains("sb-sidenav-toggled")
  );
};

onMounted((p) => {
  const sidebarToggle = document.querySelector("#sidebarToggle");
  if (sidebarToggle) {
    const isToggled = localStorage.getItem("sb|sidebar-toggle") === "true";
    if (isToggled) {
      document.body.classList.add("sb-sidenav-toggled");
    }
    sidebarToggle.addEventListener("click", (event) => {
      event.preventDefault();
      toggleSidebar();
    });
  }
});
</script>
<template>
  <Navbar />
  <div id="layoutSidenav">
    <Sidebar />
    <div id="layoutSidenav_content">
      <main>
        <div class="container-fluid">
          <!-- <Breadcrumbs /> -->
          <slot name="content" />
        </div>
      </main>
      <Footer />
    </div>
  </div>
</template>
