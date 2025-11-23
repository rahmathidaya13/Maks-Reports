<script setup>
const props = defineProps({
    message: {
        type: String,
        default: 'Sedang menyiapkan data...'
    }
})
</script>

<template>
    <div class="loader-wrapper">
        <span class="loader"></span>
        <div class="loader-message">{{ message }}</div>
    </div>
</template>

<style scoped>
/* --- WRAPPER CENTER --- */
.loader-wrapper {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 100;
    display: flex;
    flex-direction: column;
    /* loader + text di bawahnya */
    justify-content: center;
    align-items: center;
    gap: 20px;
    /* jarak loader & teks */
    width: max-content;
    pointer-events: none;
    /* biar tidak mengganggu klik */
}

/* --- LOADER --- */
.loader,
.loader:before,
.loader:after {
    border-radius: 50%;
    width: 2.5em;
    height: 2.5em;
    animation-fill-mode: both;
    animation: bblFadInOut 1.8s infinite ease-in-out;
}

.loader {
    color: #0d6efd;
    /* biru bootstrap */
    font-size: 7px;
    position: relative;
    text-indent: -9999em;
    transform: translateZ(0);
    animation-delay: -0.16s;
    margin-bottom: 10px;
}

.loader:before,
.loader:after {
    content: '';
    position: absolute;
    top: 0;
}

.loader:before {
    left: -3.5em;
    animation-delay: -0.32s;
}

.loader:after {
    left: 3.5em;
}

/* --- TEKS --- */
.loader-message {
    font-size: 1.2rem;
    font-weight: 600;
    color: #303030;
}

/* --- KEYFRAME --- */
@keyframes bblFadInOut {

    0%,
    80%,
    100% {
        box-shadow: 0 2.5em 0 -1.3em;
    }

    40% {
        box-shadow: 0 2.5em 0 0;
    }
}
</style>
