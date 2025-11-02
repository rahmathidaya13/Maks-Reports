<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { Head, Link, router, useForm, usePage } from "@inertiajs/vue3";
const props = defineProps({
    roles: {
        type: Array,
        default: () => [{}]
    },
    uniqCode: {
        type: String,
        default: ""
    },
})
const form = useForm({
    position_code: props.roles?.position_code ?? props.uniqCode,
    name: props.roles?.name ?? '',
    short_name: props.roles?.short_name ?? '',
    description: props.roles?.description ?? '',
    view: props.roles?.view ?? true,
    add: props.roles?.add ?? false,
    edit: props.roles?.edit ?? false,
    delete: props.roles?.delete ?? false,
    export: props.roles?.export ?? false,
    import: props.roles?.import ?? false,
    share: props.roles?.share ?? false,
});
const isSubmit = () => {
    if (props.roles?.id) {
        form.put(route('roles.update', props.roles.roles_id), {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                form.reset();
            },
        })
    } else {
        // Create
        form.post(route('roles.store'), {
            preserveScroll: true,
            forceFormData: true,
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
    if (props.roles && props.roles?.roles_id) {
        title.value = "Ubah Data Jabatan"
        icon.value = "fas fa-edit"
        url.value = route('roles')
    } else {
        title.value = "Buat Data Jabatan"
        icon.value = "fas fa-plus-square"
        url.value = route('roles')
    }
})
const breadcrumbItems = computed(() => {
    if (props.roles && props.roles?.roles_id) {
        return [
            { text: "Daftar Jabatan", url: route("roles") },
            { text: title.value }
        ]
    }
    return [
        { text: "Daftar Jabatan", url: route("roles") },
        { text: title.value }
    ]
})



</script>
<template>

    <Head :title="title" />
    <app-layout>
        <template #content>
            <bread-crumbs :icon="icon" :title="title" :items="breadcrumbItems" />

            <div class="d-flex justify-content-between">
                <Link :href="url" class="btn btn-danger btn-sm mb-3">
                <i class="fas fa-arrow-left"></i>
                Kembali
                </Link>
            </div>
            <div class="card overflow-hidden rounded-4 bg-light">
                <h5 class="card-header fw-bold text-uppercase p-3 text-bg-secondary">
                    <i class="fas fa-info-circle me-1 text-light"></i>
                    Form Jabatan
                </h5>
                <div class="card-body">
                    <form-wrapper @submit="isSubmit">
                        <div class="mb-3">
                            <input-label class="fw-bold" for="position_code" value="Kode Jabatan" />
                            <text-input disabled v-model="form.position_code" name="position_code" />
                        </div>
                        <div class="mb-3">
                            <input-label class="fw-bold" for="name" value="Nama jabatan" />
                            <text-input v-model="form.name" name="name" placeholder="exp: Admin" />
                            <input-error :message="form.errors.name" />
                        </div>
                        <div class="mb-3">
                            <input-label class="fw-bold" for="short_name" value="Nama Singkatan" />
                            <text-input v-model="form.short_name" name="short_name" placeholder="exp: Adm" />
                            <input-error :message="form.errors.short_name" />
                        </div>

                        <div class="mb-3">
                            <input-label class="fw-bold" value="Otoritas" />
                            <div
                                class="d-xl-flex d-block border border-secondary bg-light p-3 rounded-3 justify-content-start gap-3">
                                <check-box :value="form.view" v-model:checked="form.view" name="view"
                                    label="Dapat Melihat" />
                                <check-box :value="form.add" v-model:checked="form.add" name="add"
                                    label="Dapat Menambah" />
                                <check-box :value="form.edit" v-model:checked="form.edit" name="edit"
                                    label="Dapat Mengubah" />
                                <check-box :value="form.delete" v-model:checked="form.delete" name="delete"
                                    label="Dapat Menghapus" />
                                <check-box :value="form.export" v-model:checked="form.export" name="export"
                                    label="Dapat Mengekspor" />
                                <check-box :value="form.import" v-model:checked="form.import" name="import"
                                    label="Dapat Mengimpor" />
                                <check-box :value="form.share" v-model:checked="form.share" name="share"
                                    label="Dapat Membagikan" />

                            </div>
                        </div>

                        <div class="mb-3">
                            <input-label class="fw-bold" for="description" value="Deskripsi jabatan" />
                            <text-area :rows="10" :cols="10" :is-valid="!form.errors.description"
                                v-model="form.description" name="description" label="Deskripsi Jabatan"
                                placeholder="Masukkan deskripsi jabatan..." />
                            <input-error :message="form.errors.description" />
                        </div>
                        <div class="d-grid d-xl-block">
                            <base-button :loading="form.processing" class="rounded-3 bg-gradient px-5"
                                :icon="props.roles?.roles_id && props.roles?.roles_id ? 'fas fa-edit' : 'fas fa-paper-plane'"
                                :variant="props.roles?.roles_id && props.roles?.roles_id ? 'success' : 'primary'"
                                type="submit" :name="props.roles?.roles_id && props.roles?.roles_id ? 'ubah' : 'simpan'"
                                :label="props.roles?.roles_id && props.roles?.roles_id ? 'Ubah' : 'Simpan'" />
                        </div>
                    </form-wrapper>
                </div>
            </div>
        </template>
    </app-layout>

</template>
