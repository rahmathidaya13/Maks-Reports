<script setup>
import { ref, watch } from 'vue';
const props = defineProps({
    form: {
        type: Object,
        required: true
    },
    fieldPerStep: {
        type: Object,
        required: true
    },
    step: {
        type: Number,
        default: 1
    },
    step_indicator: {
        type: Object,
        default: () => ({})
    }
});
const emit = defineEmits(['submit']);
// Step aktif
const step = ref(1);
// ------------------------------
// 🔥 FUNGSI VALIDASI PER-STEP
// ------------------------------
const canGoNext = () => {
    const fields = props.fieldPerStep[step.value] || [];
    return fields.every(field => {
        const value = props.form[field];
        // Cek: jika kosong → tidak valid
        if (value === null || value === "" || value === undefined) return false;

        // array kosong (misal multiselect/select2)
        if (Array.isArray(value) && value.length === 0) return true;

        // object kosong
        if (typeof value === 'object' && !Array.isArray(value) && Object.keys(value).length === 0) return true;

        // Cek: kalau backend error muncul → tidak valid
        if (props.form.errors && props.form.errors[field]) return false;
        return true;
    });
};

// ------------------------------
// 🔥 Navigasi step
// ------------------------------
const nextStep = () => {
    if (canGoNext()) {
        step.value++
    }
};

const prevStep = () => step.value--;

// ------------------------------
// 🔥 CLEAR ERROR OTOMATIS SAAT USER KETIK / PILIH
// ------------------------------
Object.values(props.fieldPerStep).flat().forEach(field => {
    watch(
        () => props.form[field],
        (newVal) => {
            // Jika error ada, cek apakah value SUDAH TIDAK KOSONG
            if (props.form.errors[field]) {

                const isEmpty =
                    newVal === null ||
                    newVal === "" ||
                    newVal === undefined ||
                    (Array.isArray(newVal) && newVal.length === 0) ||
                    (typeof newVal === "object" && !Array.isArray(newVal) && Object.keys(newVal).length === 0);

                if (!isEmpty) {
                    props.form.clearErrors(field);
                }
            }
        }
    );
});

// ------------------------------
// 🔥 AUTO-JUMP KE STEP YANG ADA ERROR
//
watch(() => props.form.errors, (errors) => {
    if (!errors || Object.keys(errors).length === 0) return;

    // Cari step pertama yang ada error
    for (const [stepNumber, fields] of Object.entries(props.fieldPerStep)) {
        const hasError = fields.some(field => errors[field]);
        if (hasError) {
            step.value = parseInt(stepNumber);
            break;
        }
    }
},
    { deep: true }
)

</script>
<template>
    <div class="row justify-content-center">
        <div class="col-xl-12 col-lg-12 col-md-12">

            <div class="position-relative mb-4">
                <div class="position-absolute top-50 start-0 translate-middle-y w-100 bg-light rounded-pill"
                    style="height: 4px; z-index: 0;">
                </div>

                <div class="position-absolute top-50 start-0 translate-middle-y bg-primary rounded-pill transition-all"
                    style="height: 4px; z-index: 0;" :style="{ width: ((step - 1) / (props.step - 1)) * 100 + '%' }">
                </div>

                <div class="d-flex justify-content-between position-relative pt-4" style="z-index: 4;">
                    <div v-for="indicator in props.step" :key="indicator"
                        class="d-flex flex-column align-items-center step-item cursor-default" :class="{
                            'active': step === indicator,
                            'completed': step > indicator
                        }">

                        <div class="step-circle rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm transition-all bg-white border-2"
                            :class="[
                                step > indicator ? 'bg-primary border-primary text-success' :
                                    step === indicator ? 'border-primary text-primary ring-effect' : 'border-light text-muted'
                            ]">
                            <i v-if="step > indicator" class="fas fa-check"></i>
                            <span v-else>{{ indicator }}</span>
                        </div>

                        <div class="step-label mt-2 text-center small fw-bold text-uppercase d-none d-sm-block"
                            :class="step >= indicator ? 'text-primary' : 'text-muted opacity-50'">
                            {{ step_indicator[indicator] || `Langkah ${indicator}` }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-lg rounded-4 overflow-hidden position-relative bg-white">

                <div class="position-absolute top-0 start-0 w-100 bg-gradient-primary-soft" style="height: 6px;"></div>

                <div class="card-body p-4 p-md-5">

                    <transition name="fade-slide" mode="out-in">
                        <div :key="step">
                            <div class="mb-4 pb-2 border-bottom">
                                <h4 class="fw-bold text-dark mb-1">
                                    {{ step_indicator[step] || 'Form Data' }}
                                </h4>
                                <p class="text-muted small mb-0">Silakan lengkapi data berikut dengan benar.</p>
                            </div>

                            <div class="form-step-content">
                                <slot :name="`step-${step}`"></slot>
                            </div>

                        </div>
                    </transition>

                </div>

                <div class="card-footer bg-white p-4 border-top d-flex justify-content-between align-items-center">

                    <div>
                        <button v-if="step > 1"
                            class="btn btn-light text-secondary border fw-bold px-4 rounded-pill hover-lift"
                            @click="prevStep">
                            <i class="fas fa-arrow-left me-2"></i> Kembali
                        </button>
                    </div>

                    <div class="d-flex gap-2">

                        <button v-if="step < props.step"
                            class="btn btn-primary fw-bold px-4 rounded-pill shadow-sm hover-lift d-flex align-items-center"
                            @click="nextStep" :disabled="!canGoNext()">
                            Selanjutnya <i class="fas fa-arrow-right ms-2"></i>
                        </button>

                        <button v-if="step === props.step"
                            class="btn btn-success fw-bold px-5 rounded-pill shadow hover-lift d-flex align-items-center"
                            @click="emit('submit')">
                            <i class="fas fa-check-circle me-2"></i> Simpan Data
                        </button>

                    </div>
                </div>

            </div>
        </div>
    </div>
</template>
<style scoped>
.step-item {
    flex: 1;
    position: relative;
    z-index: 2;
}

.cursor-default {
    cursor: default;
}


/* Area konten form agar transisi mulus */
.form-step-content {
    min-height: 200px;
    /* Mencegah card 'melompat' tinggi saat ganti step */
}

/* --- 1. Stepper Styles --- */
.step-circle {
    width: 45px;
    height: 45px;
    font-size: 16px;
    border-style: solid;
    z-index: 2;
    background-color: #fff;
    /* Pastikan background putih agar menutupi garis */
}

/* =========================================
   5. STATE STYLING (Active & Completed)
   ========================================= */

/* --- Default State (Belum dilewati) --- */
/* Lingkaran abu-abu, teks pudar */
.step-item .step-circle {
    border-color: #dee2e6;
    /* Abu-abu border */
    color: #adb5bd;
    /* Teks abu-abu */
    background-color: #fff;
    transition: all 0.3s ease;
}

.step-item .step-label {
    color: #adb5bd;
    font-weight: 600;
    transition: all 0.3s ease;
}

/* --- Active State (Sedang diisi) --- */
/* Lingkaran Biru Outline, ada efek Ring */
.step-item.active .step-circle {
    border-color: #0d6efd;
    /* Primary Color */
    color: #0d6efd;
    background-color: #fff;
    transform: scale(1.1);
    /* Membesar sedikit */
    box-shadow: 0 0 0 5px rgba(13, 110, 253, 0.15);
    /* Efek Ring/Glow */
}

.step-item.active .step-label {
    color: #0d6efd;
    font-weight: 800;
    /* Teks lebih tebal */
}

/* --- Completed State (Sudah selesai) --- */
/* Lingkaran Full Biru, Teks Putih */
.step-item.completed .step-circle {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: #fff;
    /* Icon centang jadi putih */
}

.step-item.completed .step-label {
    color: #0d6efd;
    /* Label tetap biru */
    font-weight: 700;
}

/* Efek Cincin Berdenyut saat Aktif */
.ring-effect {
    box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.15);
    transform: scale(1.1);
}

.step-label {
    font-size: 0.75rem;
    letter-spacing: 0.5px;
}

/* Transisi CSS Umum */
.transition-all {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Background gradient tipis di atas card */
.bg-gradient-primary-soft {
    background: linear-gradient(90deg, #0d6efd 0%, #0dcaf0 100%);
}

/* --- 2. Button Animations --- */
.hover-lift {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.hover-lift:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

/* --- 3. Animasi Perpindahan Step (Fade + Slide) --- */
.fade-slide-enter-active,
.fade-slide-leave-active {
    transition: opacity 0.3s ease, transform 0.3s ease;
}

.fade-slide-enter-from {
    opacity: 0;
    transform: translateX(15px);
    /* Masuk dari kanan */
}

.fade-slide-leave-to {
    opacity: 0;
    transform: translateX(-15px);
    /* Keluar ke kiri */
}

/* Mobile Tweaks */
@media (max-width: 576px) {
    .step-circle {
        width: 35px;
        height: 35px;
        font-size: 14px;
    }

    .step-label {
        display: none;
    }
}

.hover-lift {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.hover-lift:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}
</style>
