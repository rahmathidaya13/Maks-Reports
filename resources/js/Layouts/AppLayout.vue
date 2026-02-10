<script setup>
import { onMounted } from "vue";
import Navbar from "./Navbar.vue";
import Sidebar from "./Sidebar.vue";
import Footer from "./Footer.vue";
import { useConfirm } from "@/helpers/useConfirm"
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

const { state } = useConfirm()
</script>
<template>

    <Teleport to="body">
        <div v-if="state.show" class="confirm-overlay d-flex align-items-center justify-content-center">
            <div class="confirm-card bg-white rounded-4 shadow-lg p-4">
                <div class="text-center mb-4">
                    <div
                        :class="`icon-circle bg-${state.variantIcon ?? state.variant} bg-opacity-10 text-${state.variantIcon ?? state.variant} mb-3`">
                        <i :class="` ${state.icon ?? 'fas fa-exclamation-triangle'} fa-2x`"></i>
                    </div>
                    <h5 class="fw-bold">{{ state.title }}</h5>
                    <p class="text-muted mb-0 px-2">{{ state.message }}</p>
                </div>

                <div v-if="state.requireCheckbox" class="bg-light p-3 rounded-3 mb-4 border border-light-subtle">
                    <div class="form-check d-flex align-items-center gap-2">
                        <input class="form-check-input mt-0 custom-checkbox" type="checkbox" v-model="state.isChecked"
                            id="confirmCheck" style="width: 1.2rem; height: 1.2rem; cursor: pointer;">
                        <label class="form-check-label small fw-medium text-dark cursor-pointer" for="confirmCheck">
                            {{ state.checkboxText }}
                        </label>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button @click="state.onCancel"
                        class="btn btn-light btn-sm rounded-pill flex-fill py-2 text-muted fw-semibold">
                        {{ state.cancelText }}
                    </button>
                    <button @click="state.onConfirm" v-if="state.showButtonConfirm"
                        :disabled="state.requireCheckbox && !state.isChecked"
                        :class="`btn btn-${state.variantButton ?? state.variant} rounded-pill btn-sm flex-fill py-2 fw-semibold shadow-sm`">
                        {{ state.confirmText }}
                    </button>
                </div>
            </div>
        </div>
    </Teleport>

    <Navbar />
    <div id="layoutSidenav">
        <Sidebar />
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid">
                    <slot name="content" />
                </div>
            </main>
            <Footer />
        </div>
    </div>
</template>

<style scoped>
.confirm-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(15, 23, 42, 0.4);
    backdrop-filter: blur(4px);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: fadeIn 0.2s ease-out;
}

.confirm-card {
    width: 100%;
    max-width: 450px;
    border: none;
    animation: zoomIn 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.icon-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}

/* Keyframes Animasi */
@keyframes fadeIn {
    from {
        opacity: 0;
    }

    to {
        opacity: 1;
    }
}

@keyframes zoomIn {
    from {
        opacity: 0;
        transform: scale(0.9);
        /* Mulai dari sedikit kecil */
    }

    to {
        opacity: 1;
        transform: scale(1);
        /* Kembali ke ukuran asli */
    }
}

.custom-checkbox {
    width: 1.2em;
    height: 1.2em;
    cursor: pointer;
    border-radius: 4px;
}

.custom-checkbox:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

/* Efek saat tombol disabled */
button:disabled {
    cursor: not-allowed;
    filter: grayscale(1);
    opacity: 0.6;
}
</style>
