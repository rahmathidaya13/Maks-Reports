<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { Head, Link, router, useForm, usePage } from "@inertiajs/vue3";
const props = defineProps({
    jobTitle: {
        type: Object,
        default: () => [{}]
    },
    uniqCode: {
        type: String,
        default: ""
    },
})
const form = useForm({
    job_title_code: props.jobTitle?.job_title_code ?? props.uniqCode,
    title: props.jobTitle?.title ?? '',
    title_alias: props.jobTitle?.title_alias ?? '',
    description: props.jobTitle?.description ?? '',
});
const isSubmit = () => {
    if (props.jobTitle?.job_title_id) {
        form.put(route('job_title.update', props.jobTitle.job_title_id), {
            onSuccess: () => {
                form.reset();
            },
        })
    } else {
        // Create
        form.post(route('job_title.store'), {
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
    if (props.jobTitle && props.jobTitle?.job_title_id) {
        title.value = "Ubah Data Jabatan"
        icon.value = "fas fa-edit"
        url.value = route('job_title')
    } else {
        title.value = "Buat Data Jabatan"
        icon.value = "fas fa-plus-square"
        url.value = route('job_title')
    }
})
const breadcrumbItems = computed(() => {
    if (props.jobTitle && props.jobTitle?.job_title_id) {
        return [
            { text: "Daftar Jabatan", url: route("job_title") },
            { text: "Buat Data Jabatan", url: route("job_title.create") },
            { text: title.value }
        ]
    }
    return [
        { text: "Daftar Jabatan", url: route("job_title") },
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
                            <input-label class="fw-bold" for="code" value="Kode Jabatan" />
                            <text-input disabled v-model="form.job_title_code" name="code" />
                        </div>
                        <div class="mb-3">
                            <input-label class="fw-bold" for="title" value="Nama jabatan" />
                            <text-input v-model="form.title" name="title" placeholder="exp: Admin" />
                            <input-error :message="form.errors.title" />
                        </div>
                        <div class="mb-3">
                            <input-label class="fw-bold" for="title_alias" value="Singkatan Jabatan" />
                            <text-input v-model="form.title_alias" name="title_alias" placeholder="exp: Adm" />
                            <input-error :message="form.errors.title_alias" />
                        </div>
                        <div class="mb-3">
                            <input-label class="fw-bold" for="description" value="Deskripsi jabatan" />
                            <summernote-editor v-model="form.description" :options="{
                                height: 350,
                                placeholder: 'Tulis deskripsi disini...',
                                toolbar: [
                                    ['font', ['bold', 'underline']],
                                    ['para', ['ul', 'ol', 'paragraph']],
                                ]
                            }" :max-length="500" />
                            <!-- <text-area :rows="10" :cols="10" :is-valid="!form.errors.description"
                                v-model="form.description" name="description" label="Deskripsi Jabatan"
                                placeholder="Masukkan deskripsi jabatan..." /> -->
                            <input-error :message="form.errors.description" />
                        </div>
                        <div class="d-grid d-xl-block">
                            <base-button :loading="form.processing" class="rounded-3 bg-gradient px-5"
                                :icon="props.jobTitle?.job_title_id && props.jobTitle?.job_title_id ? 'fas fa-edit' : 'fas fa-paper-plane'"
                                :variant="props.jobTitle?.job_title_id && props.jobTitle?.job_title_id ? 'success' : 'primary'"
                                type="submit"
                                :name="props.jobTitle?.job_title_id && props.jobTitle?.job_title_id ? 'ubah' : 'simpan'"
                                :label="props.jobTitle?.job_title_id && props.jobTitle?.job_title_id ? 'Ubah' : 'Simpan'" />
                        </div>
                    </form-wrapper>
                </div>
            </div>
        </template>
    </app-layout>

</template>
