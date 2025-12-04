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
    step.value++
    // if (canGoNext()) {
    //     step.value++
    // }
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
    console.log(errors);
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
    <div class="card shadow border overflow-hidden text-bg-grey">
        <div class="card-body p-3 p-xl-5">

            <div class="step-container mb-4">
                <div v-for="indicator in props.step" :key="indicator" class="step-arrow" :class="{
                    active: step === indicator,
                    completed: step > indicator
                }">
                    {{ step_indicator[indicator] }}
                </div>
            </div>
            <hr />

            <div v-if="step <= props.step">

                <slot :name="`step-${step}`"></slot>
                <hr />

                <div class="d-flex justify-content-between mt-3">

                    <button v-if="step > 1" class="btn btn-danger btn-height-2 bg-gradient" @click="prevStep">
                        <i class="fas fa-arrow-left"></i>
                        Kembali
                    </button>

                    <button v-if="step < props.step" class="btn btn-primary ms-auto btn-height-2 bg-gradient"
                        @click="nextStep">
                        Selanjutnya
                        <i class="fas fa-arrow-right"></i>
                    </button>

                    <button v-if="step === props.step" class="btn btn-success ms-auto btn-height-2 bg-gradient"
                        @click="emit('submit')">
                        <i class="fas fa-edit"></i>
                        Perbarui Profil
                    </button>

                </div>
            </div>

        </div>
    </div>
</template>
<style scoped>
.step-container {
    display: flex;
    width: 100%;
    user-select: none;
    border: 2px solid #d6d6d6;
    border-radius: 50px;
    overflow: hidden;
    white-space: nowrap;
}

.step-arrow {
    position: relative;
    padding: 12px 30px;
    background: #e9ecef;
    color: #57595a;
    font-weight: 600;
    flex: 1;
    text-align: center;
    transition: 0.2s ease-in-out;
}


.step-arrow.active {
    background: #5592ee;
    color: white;
}

.step-arrow.completed {
    background: #14a561;
    color: white;
}

.step-arrow.active::after,
.step-arrow.completed::after {
    background: inherit;
}
</style>
