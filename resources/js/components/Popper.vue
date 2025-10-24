<script setup>
import { ref } from "vue";

const showForm = ref(false);
const formX = ref(0);
const formY = ref(0);
const formData = ref({
  nama: "",
  kategori: "",
});

const openForm = (e) => {
  e.preventDefault();
  showForm.value = true;
  formX.value = e.pageX;
  formY.value = e.pageY;
};

const submitForm = () => {
  alert(`Nama: ${formData.value.nama}, Kategori: ${formData.value.kategori}`);
  showForm.value = false;

  // Reset form jika perlu
  formData.value.nama = "";
  formData.value.kategori = "";
};

const closeForm = () => {
  showForm.value = false;
};
</script>

<template>
  <div
    @contextmenu="openForm"
    class="border border-dark rounded p-5 text-center"
    style="height: 300px; background-color: #f8f9fa"
  >
    Klik kanan di area ini untuk tambah data
  </div>

  <div
    v-if="showForm"
    :style="{
      position: 'absolute',
      top: formY + 'px',
      left: formX + 'px',
      zIndex: 9999,
      backgroundColor: 'white',
      border: '1px solid #ccc',
      padding: '10px',
      borderRadius: '5px',
      boxShadow: '0 4px 8px rgba(0,0,0,0.2)',
      minWidth: '200px',
    }"
  >
    <form @submit.prevent="submitForm">
      <div class="mb-2">
        <label class="form-label">Nama</label>
        <input v-model="formData.nama" type="text" class="form-control" />
      </div>
      <div class="mb-2">
        <label class="form-label">Kategori</label>
        <select v-model="formData.kategori" class="form-select">
          <option value="">Pilih</option>
          <option value="rumah">Rumah</option>
          <option value="toko">Toko</option>
        </select>
      </div>
      <div class="d-flex justify-content-between">
        <button type="submit" class="btn btn-sm btn-primary">Simpan</button>
        <button type="button" class="btn btn-sm btn-secondary" @click="closeForm">
          Batal
        </button>
      </div>
    </form>
  </div>
</template>
