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
        title.value = "Ubah Data Jabatan " + props.jobTitle?.title
        icon.value = "fas fa-edit"
        url.value = route('job_title')
    } else {
        title.value = "Buat Jabatan Baru"
        icon.value = "fas fa-plus-square"
        url.value = route('job_title')
    }
})
const breadcrumbItems = computed(() => {
    if (props.jobTitle && props.jobTitle?.job_title_id) {
        return [
            { text: "Daftar Jabatan", url: route("job_title") },
            { text: "Buat Jabatan Baru", url: route("job_title.create") },
            { text: title.value }
        ]
    }
    return [
        { text: "Daftar Jabatan", url: route("job_title") },
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
                    <div class="card overflow-hidden rounded-3 bg-light">
                        <h5 class="card-header fw-bold text-uppercase p-3 text-bg-dark">
                            <i class="fas fa-info-circle me-1 text-light"></i>
                            Form Jabatan
                        </h5>
                        <div v-if="form.processing">
                            <loader-horizontal
                                :message="props.jobTitle?.job_title_id ? 'Sedang memperbarui data' : 'Sedang menyimpan data'" />
                        </div>
                        <div class="card-body" :class="['blur-area', form.processing ? 'is-blurred' : '']">
                            <form-wrapper @submit="isSubmit">
                                <div class="mb-3">
                                    <input-label class="fw-bold" for="code" value="Kode Jabatan" />
                                    <text-input :is-valid="false" disabled v-model="form.job_title_code" name="code" />
                                </div>
                                <div class="mb-3">
                                    <input-label class="fw-bold" for="title" value="Nama jabatan" />
                                    <text-input autofocus v-model="form.title" name="title" placeholder="exp: Admin" />
                                    <input-error :message="form.errors.title" />
                                </div>
                                <div class="mb-3">
                                    <input-label class="fw-bold" for="title_alias" value="Singkatan Jabatan" />
                                    <text-input v-model="form.title_alias" name="title_alias" placeholder="exp: Adm" />
                                    <input-error :message="form.errors.title_alias" />
                                </div>
                                <div class="mb-3">
                                    <input-label class="fw-bold" for="description" value="Deskripsi jabatan" />
                                    <quill-text :maxChar="500" placeholder="Tulis deskripsi disini..."
                                        v-model="form.description" height="350px" />
                                    <input-error :message="form.errors.description" />
                                </div>
                                <div class="d-grid d-xl-block">
                                    <base-button :loading="form.processing"
                                        class="rounded-3 bg-gradient px-5 btn-height-2"
                                        :icon="props.jobTitle?.job_title_id && props.jobTitle?.job_title_id ? 'fas fa-edit' : 'fas fa-save'"
                                        :variant="props.jobTitle?.job_title_id && props.jobTitle?.job_title_id ? 'success' : 'primary'"
                                        type="submit"
                                        :name="props.jobTitle?.job_title_id && props.jobTitle?.job_title_id ? 'ubah' : 'simpan'"
                                        :label="props.jobTitle?.job_title_id && props.jobTitle?.job_title_id ? 'Ubah' : 'Simpan'" />
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
