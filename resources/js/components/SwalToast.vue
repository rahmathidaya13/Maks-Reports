<script setup>
import Swal from "sweetalert2";
import "sweetalert2/dist/sweetalert2.min.css";
import { defineExpose } from "vue";

// Buat satu instance SweetAlert mixin
const Toast = Swal.mixin({
    toast: true,
    position: "top-end",
    iconColor: "white",
    customClass: {
        popup: "colored-toast",
    },
    showConfirmButton: false,
    timer: 5500,
    timerProgressBar: true,
});


// Fungsi umum untuk menampilkan toast
const show = (icon = "success", title = "Success", options = {}) => {
    Toast.fire({
        icon,
        title,
        ...options, // bisa override misal timer, position, dll
    });
};
defineExpose({ show });
</script>
<template>
    <div></div>
</template>
<style scoped>
/* Styling minimalis dan lembut */
.colored-toast.swal2-icon-success {
  background-color: #3cbd5a !important;
  color: white;
  border-radius: 10px;
}

.colored-toast.swal2-icon-error {
  background-color: #dc3545 !important;
  color: white;
  border-radius: 10px;
}

.colored-toast.swal2-icon-warning {
  background-color: #ffc107 !important;
  color: #000;
  border-radius: 10px;
}

.colored-toast.swal2-icon-info {
  background-color: #0dcaf0 !important;
  color: white;
  border-radius: 10px;
}

.colored-toast.swal2-icon-question {
  background-color: #6f42c1 !important;
  color: white;
  border-radius: 10px;
}
</style>
