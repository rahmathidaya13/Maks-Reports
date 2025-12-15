<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { Head, Link, router, useForm, usePage } from "@inertiajs/vue3";
const props = defineProps({
    branch: {
        type: Object,
        default: () => [{}]
    },
    branchCode: {
        type: String,
        default: ""
    },
})
const form = useForm({
    name: props.branch?.name ?? '',
    branch_code: props.branch?.branch_code ?? props.branchCode,
    address: props.branch?.address ?? '',
    status: props.branch?.status ?? 'active',
    number_phone: props.branch?.branch_phone?.length
        ? [...props.branch?.branch_phone?.map(value => value.phone)]
        : [''],
});
const isSubmit = () => {
    if (props.branch?.branches_id) {
        form.put(route('branch.update', props.branch?.branches_id), {
            onSuccess: () => {
                form.reset();
            },
        })
    } else {
        // Create
        form.post(route('branch.store'), {
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
    if (props.branch && props.branch?.branches_id) {
        title.value = "Ubah Data Cabang " + props.branch?.name
        icon.value = "fas fa-edit"
        url.value = route('branch')
    } else {
        title.value = "Buat Cabang Baru"
        icon.value = "fas fa-plus-square"
        url.value = route('branch')
    }
})
const breadcrumbItems = computed(() => {
    if (props.branch && props.branch?.branches_id) {
        return [
            { text: "Daftar Cabang", url: route("branch") },
            { text: "Buat Cabang Baru", url: route("branch.create") },
            { text: title.value }
        ]
    }
    return [
        { text: "Daftar Cabang", url: route("branch") },
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

const addPhone = () => {
    if (form.number_phone.length) {
        form.number_phone.push('')
    }
}

const removePhone = (index) => {
    if (form.number_phone.length > 1) {
        form.number_phone.splice(index, 1)
    }
}
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
                            Form Cabang
                        </h5>
                        <div v-if="form.processing">
                            <loader-horizontal
                                :message="props.branch?.branches_id ? 'Sedang memperbarui data' : 'Sedang menyimpan data'" />
                        </div>
                        <div class="card-body" :class="['blur-area', form.processing ? 'is-blurred' : '']">
                            <form-wrapper @submit="isSubmit">
                                <div class="mb-2">
                                    <input-label class="fw-bold" for="branch_code" value="Kode Cabang" />
                                    <text-input :is-valid="false" disabled v-model="form.branch_code"
                                        name="branch_code" />
                                    <small class="text-danger fst-italic">*Kode Cabang akan di generate secara
                                        otomatis</small>
                                </div>
                                <div class="mb-2">
                                    <input-label class="fw-bold" for="name" value="Nama Cabang" />
                                    <text-input autofocus v-model="form.name" name="name"
                                        placeholder="contoh: Jakarta" />
                                    <input-error :message="form.errors.name" />
                                </div>
                                <div class="mb-2">
                                    <div class="mb-2">
                                        <input-label class="fw-bold" value="Nomor Telepon" />
                                        <button type="button" class="btn btn-outline-primary btn-sm ms-2"
                                            @click="addPhone">
                                            + Tambah Nomor
                                        </button>
                                    </div>
                                    <div class="row g-1">
                                        <div :class="`col-12 col-xl-${form.number_phone.length == 1 ? '12' : '6'} mb-2`"
                                            v-for="(phone, index) in form.number_phone" :key="index">
                                            <div class="d-flex gap-1 align-items-start">
                                                <text-input :tabindex="index + 1" v-model="form.number_phone[index]"
                                                    :name="`number_phone.${index}`"
                                                    :placeholder="`Nomor Telepon ${index + 1}`" />

                                                <button v-if="form.number_phone.length > 1" type="button"
                                                    class="btn btn-danger" @click="removePhone(index)">
                                                    &times;
                                                </button>
                                            </div>
                                            <input-error :message="form.errors[`number_phone.${index}`]" />
                                        </div>
                                    </div>
                                    <input-error :message="form.errors.number_phone" />
                                </div>
                                <div class="mb-2">
                                    <input-label class="fw-bold" for="status" value="Status" />
                                    <select-input name="status" text="Pilih Status Cabang" :options="[
                                        { value: 'active', label: 'Aktif' },
                                        { value: 'inactive', label: 'Tidak Aktif' }
                                    ]" v-model="form.status" />
                                    <input-error :message="form.errors.status" />
                                </div>
                                <div class="mb-2">
                                    <input-label class="fw-bold" for="address" value="Alamat Cabang" />
                                    <quill-text :maxChar="500" placeholder="Tulis Alamat Lengkap disini..."
                                        v-model="form.address" height="350px" />
                                    <input-error :message="form.errors.address" />
                                </div>
                                <div class="d-grid d-xl-block">
                                    <base-button :loading="form.processing"
                                        class="rounded-3 bg-gradient px-5 btn-height-2"
                                        :icon="props.branch?.branches_id && props.branch?.branches_id ? 'fas fa-edit' : 'fas fa-save'"
                                        :variant="props.branch?.branches_id && props.branch?.branches_id ? 'success' : 'primary'"
                                        type="submit"
                                        :name="props.branch?.branches_id && props.branch?.branches_id ? 'ubah' : 'simpan'"
                                        :label="props.branch?.branches_id && props.branch?.branches_id ? 'Ubah' : 'Simpan'" />
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
