<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { Head, Link, router, useForm, usePage } from "@inertiajs/vue3";
const props = defineProps({
    customers: {
        type: Object,
        default: () => [{}],
    },
});
const form = useForm({
    national_id: props.customers?.national_id_number ?? "",
    customer_name: props.customers?.customer_name ?? "",
    number_phone: props.customers?.number_phone_customer ?? "",
    city: props.customers?.city ?? "",
    province: props.customers?.province ?? "",
    address: props.customers?.address ?? "",
});
const isSubmit = () => {
    if (props.customers?.customer_id) {
        form.put(route("customers.update", props.customers?.customer_id), {
            onSuccess: () => {
                form.reset();
            },
        });
    } else {
        // Create
        form.post(route("customers.store"), {
            onSuccess: () => {
                form.reset();
            },
        });
    }
};
const title = ref("");
const icon = ref("");
const url = ref("");
onMounted(() => {
    if (props.customers && props.customers?.customer_id) {
        title.value = "Ubah Data Pelanggan - " + props.customers?.customer_name;
        icon.value = "fas fa-edit";
        url.value = route("customers");
    } else {
        title.value = "Buat Pelanggan Baru";
        icon.value = "fas fa-plus-square";
        url.value = route("customers");
    }
});
const breadcrumbItems = computed(() => {
    if (props.customers && props.customers?.customer_id) {
        return [
            { text: "Daftar Pelanggan", url: route("customers") },
            { text: "Buat Pelanggan Baru", url: route("customers.create") },
            { text: title.value },
        ];
    }
    return [{ text: "Daftar Pelanggan", url: route("customers") }, { text: title.value }];
});

const loaderActive = ref(null);
const goBack = () => {
    loaderActive.value?.show("Memproses...");
    router.get(
        url.value,
        {},
        {
            onFinish: () => loaderActive.value?.hide(),
        }
    );
};

const inputRef = ref(null);
onMounted(() => {
    inputRef.value.focus();
});
</script>
<template>

    <Head :title="title" />
    <app-layout>
        <template #content>
            <loader-page ref="loaderActive" />
            <bread-crumbs :icon="icon" :title="title" :items="breadcrumbItems" />
            <div class="row pb-3">
                <div class="col-xl-12 col-12">
                    <div class="d-block d-xl-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="fw-bold text-dark mb-1">Input Pelanggan</h4>
                            <p class="text-muted small mb-0">Isi data pelanggan Anda dengan lengkap.</p>
                        </div>
                        <Link @click.prevent="goBack" :href="url" class="btn btn-danger border shadow-sm hover-scale">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </Link>
                    </div>
                    <div class="card border-0 shadow rounded-4 overflow-hidden">
                        <div class="card-header bg-white py-3 px-4 border-bottom">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3 me-3">
                                    <i class="fas fa-user-edit fs-4"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0 text-dark">
                                        {{
                                            props.customers?.customer_id
                                                ? "Ubah Data Pelanggan"
                                                : "Registrasi Pelanggan Baru"
                                        }}
                                    </h5>
                                    <p class="text-muted small mb-0">
                                        Lengkapi formulir di bawah ini dengan data yang valid.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div v-if="form.processing"
                            class="position-absolute w-100 h-100 bg-white opacity-75 d-flex align-items-center justify-content-center"
                            style="z-index: 10">
                            <loader-horizontal :message="props.customers?.customer_id
                                ? 'Menyimpan perubahan...'
                                : 'Mendaftarkan pelanggan...'
                                " />
                        </div>

                        <div class="card-body p-4" :class="['blur-area', form.processing ? 'is-blurred' : '']">
                            <form-wrapper @submit="isSubmit">
                                <h6 class="text-uppercase text-primary fw-bold small mb-3 letter-spacing-1">
                                    <i class="far fa-id-card me-2"></i> Identitas Diri
                                </h6>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <input-label class="fw-bold small" for="national_id" value="NIK / Nomor KTP" />
                                        <div class="position-relative">
                                            <i class="fas fa-fingerprint text-muted input-icon-left"></i>
                                            <text-input ref="inputRef" placeholder="Cth: 1234567890..."
                                                class="input-fixed-height" v-model="form.national_id"
                                                name="national_id" />
                                        </div>
                                        <small class="text-muted fst-italic" style="font-size: 0.75rem">
                                            *Opsional, kosongkan jika belum ada.
                                        </small>
                                        <input-error :message="form.errors.national_id" />
                                    </div>

                                    <div class="col-md-6">
                                        <input-label class="fw-bold small" for="customer_name" value="Nama Lengkap" />
                                        <div class="position-relative">
                                            <i class="fas fa-user text-muted input-icon-left"></i>
                                            <text-input placeholder="Masukkan nama lengkap" class="input-fixed-height"
                                                v-model="form.customer_name" name="customer_name" />
                                        </div>
                                        <input-error :message="form.errors.customer_name" />
                                    </div>

                                    <div class="col-md-12">
                                        <input-label class="fw-bold small" for="number_phone"
                                            value="Nomor WhatsApp / HP" />
                                        <div class="position-relative">
                                            <i class="fab fa-whatsapp text-success input-icon-left"></i>
                                            <input-number input-class="input-fixed-height"
                                                placeholder="0812xxxx (Aktif)" name="number_phone" type="text"
                                                v-model="form.number_phone" />
                                        </div>
                                        <input-error :message="form.errors.number_phone" />
                                    </div>
                                </div>

                                <hr class="border-dashed my-4 opacity-50" />

                                <h6 class="text-uppercase text-primary fw-bold small mb-3 letter-spacing-1">
                                    <i class="fas fa-map-marker-alt me-2"></i> Alamat Domisili
                                </h6>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <input-label class="fw-bold small" for="city" value="Kota / Kabupaten" />
                                        <div class="position-relative">
                                            <i class="fas fa-city input-icon-left"></i>
                                            <text-input input-class="text-uppercase input-fixed-height"
                                                v-model="form.city" name="city" placeholder="Nama Kota" />
                                        </div>
                                        <input-error :message="form.errors.city" />
                                    </div>

                                    <div class="col-md-6">
                                        <input-label class="fw-bold small" for="province" value="Provinsi" />
                                        <div class="position-relative">
                                            <i class="fas fa-globe input-icon-left"></i>
                                            <text-input input-class="text-uppercase input-fixed-height"
                                                v-model="form.province" name="province" placeholder="Nama Provinsi" />
                                        </div>
                                        <input-error :message="form.errors.province" />
                                    </div>

                                    <div class="col-12">
                                        <input-label class="fw-bold small" for="address"
                                            value="Alamat Lengkap (Jalan, RT/RW, No. Rumah)" />
                                        <text-area :rows="5" v-model="form.address" name="address" class="form-control"
                                            placeholder="Jalan Mawar No. 10, RT 01/RW 02..." />
                                        <input-error :message="form.errors.address" />
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                                    <button @click.prevent="goBack"
                                        class="btn btn-light border px-4 fw-semibold text-muted">
                                        Batal
                                    </button>

                                    <base-button :loading="form.processing"
                                        class="btn-save-custom rounded-3 shadow-sm px-4 btn-height-1 fw-bold" :icon="props.customers?.customer_id ? 'fas fa-save' : 'fas fa-paper-plane'
                                            " :variant="props.customers?.customer_id ? 'success' : 'primary'"
                                        type="submit" :label="props.customers?.customer_id ? 'Simpan Perubahan' : 'Simpan Data'
                                            " />
                                </div>
                            </form-wrapper>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </app-layout>
</template>
<style scoped>
.blur-area {
    transition: all 0.3s ease;
}

.blur-area.is-blurred {
    filter: blur(3px);
    pointer-events: none;
    user-select: none;
    opacity: 0.6;
}

/* Utility Class untuk Tombol Kembali */
.hover-scale {
    transition: transform 0.2s;
}

.hover-scale:hover {
    transform: scale(1.05);
}

.btn-save-custom {
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
}

.btn-save-custom:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
}
</style>
