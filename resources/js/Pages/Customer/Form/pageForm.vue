<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { Head, Link, router, useForm, usePage } from "@inertiajs/vue3";
const props = defineProps({
    customers: {
        type: Object,
        default: () => [{}]
    },
})
const form = useForm({
    national_id: props.customers?.national_id_number ?? '',
    customer_name: props.customers?.customer_name ?? '',
    number_phone: props.customers?.number_phone_customer ?? '',
    city: props.customers?.city ?? '',
    province: props.customers?.province ?? '',
    address: props.customers?.address ?? '',
});
const isSubmit = () => {
    if (props.customers?.customer_id) {
        form.put(route('customers.update', props.customers?.customer_id), {
            onSuccess: () => {
                form.reset();
            },
        })
    } else {
        // Create
        form.post(route('customers.store'), {
            onSuccess: () => {
                form.reset();
            }
        });
    }
};
const title = ref("");
const icon = ref("");
const url = ref("")
onMounted(() => {
    if (props.customers && props.customers?.customer_id) {
        title.value = "Ubah Data Pelanggan " + props.customers?.customer_name
        icon.value = "fas fa-edit"
        url.value = route('customers')
    } else {
        title.value = "Buat Pelanggan Baru"
        icon.value = "fas fa-plus-square"
        url.value = route('customers')
    }
})
const breadcrumbItems = computed(() => {
    if (props.customers && props.customers?.customer_id) {
        return [
            { text: "Daftar Pelanggan", url: route("customers") },
            { text: "Buat Pelanggan Baru", url: route("customers.create") },
            { text: title.value }
        ]
    }
    return [
        { text: "Daftar Pelanggan", url: route("customers") },
        { text: title.value }
    ]
})

const loaderActive = ref(null);
const goBack = () => {
    loaderActive.value?.show("Memproses...");
    router.get(url.value, {}, {
        onFinish: () => loaderActive.value?.hide()
    });
}

const inputRef = ref(null);
onMounted(() => {
    inputRef.value.focus();
})
</script>
<template>

    <Head :title="title" />
    <app-layout>
        <template #content>
            <loader-page ref="loaderActive" />
            <bread-crumbs :icon="icon" :title="title" :items="breadcrumbItems" />
            <div class="d-flex justify-content-between">
                <Link @click.prevent="goBack" :href="url" class="btn btn-danger mb-3">
                    <i class="fas fa-arrow-left"></i>
                    Kembali
                </Link>
            </div>
            <div class="row">
                <div class="col-xl-12 col-sm-12 pb-3">
                    <div class="card overflow-hidden rounded-3 shadow-sm">
                        <h5 class="card-header fw-bold text-uppercase p-3 text-bg-dark">
                            <i class="fas fa-info-circle me-1 text-light"></i>
                            Form Pelanggan
                        </h5>
                        <div v-if="form.processing">
                            <loader-horizontal
                                :message="props.customers?.customer_id ? 'Sedang memperbarui data' : 'Sedang menyimpan data'" />
                        </div>
                        <div class="card-body" :class="['blur-area', form.processing ? 'is-blurred' : '']">
                            <form-wrapper @submit="isSubmit">
                                <div class="mb-2">
                                    <input-label class="fw-bold" for="national_id" value="NIK/SIM" />
                                    <text-input ref="inputRef" v-model="form.national_id" name="national_id" />
                                    <small v-if="!form.errors.national_id && !form.national_id"
                                        class="text-muted">Opsional: Jika ada,
                                        masukkan NIK/SIM</small>
                                    <input-error :message="form.errors.national_id" />

                                </div>
                                <div class="mb-2">
                                    <input-label class="fw-bold" for="customer_name" value="Nama Pelanggan" />
                                    <text-input v-model="form.customer_name" name="customer_name" />
                                    <input-error :message="form.errors.customer_name" />
                                </div>
                                <div class="mb-2">
                                    <input-label class="fw-bold" for="number_phone" value="Nomor Handphone" />
                                    <input-number placeholder="0812******" name="number_phone" type="text"
                                        v-model="form.number_phone" />
                                    <input-error :message="form.errors.number_phone" />
                                </div>

                                <div class="mb-2">
                                    <input-label class="fw-bold" for="city" value="Kota" />
                                    <text-input input-class="text-uppercase" v-model="form.city" name="city" />
                                    <input-error :message="form.errors.city" />
                                </div>
                                <div class="mb-2">
                                    <input-label class="fw-bold" for="province" value="Provinsi" />
                                    <text-input input-class="text-uppercase" v-model="form.province" name="province" />
                                    <input-error :message="form.errors.province" />
                                </div>
                                <div class="mb-3">
                                    <input-label class="fw-bold" for="address" value="Alamat" />
                                    <text-area :rows="6" :cols="6" v-model="form.address" name="address" />
                                    <input-error :message="form.errors.address" />
                                </div>
                                <div class="d-grid d-xl-block">
                                    <base-button :loading="form.processing"
                                        class="rounded-3 bg-gradient px-5 btn-height-2"
                                        :icon="props.customers?.customer_id && props.customers?.customer_id ? 'fas fa-edit' : 'fas fa-save'"
                                        :variant="props.customers?.customer_id && props.customers?.customer_id ? 'success' : 'primary'"
                                        type="submit"
                                        :name="props.customers?.customer_id && props.customers?.customer_id ? 'ubah' : 'simpan'"
                                        :label="props.customers?.customer_id && props.customers?.customer_id ? 'Ubah' : 'Simpan'" />
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
</style>
