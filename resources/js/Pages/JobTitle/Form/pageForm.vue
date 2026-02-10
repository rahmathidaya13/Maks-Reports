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
const isEditMode = computed(() => !!props.jobTitle?.job_title_id);
const form = useForm({
    job_title_code: props.jobTitle?.job_title_code ?? props.uniqCode,
    title: props.jobTitle?.title ?? '',
    title_alias: props.jobTitle?.title_alias ?? '',
    description: props.jobTitle?.description ?? '',
});
const isSubmit = () => {
    const method = isEditMode.value ? 'put' : 'post';
    const url = isEditMode.value
        ? route('job_title.update', props.jobTitle.job_title_id)
        : route('job_title.store');
    form[method](url, {
        onSuccess: () => {
            form.reset();
        },
    })
};
const pageMeta = computed(() => {
    if (isEditMode.value) {
        return {
            title: "Ubah Data Jabatan " + props.jobTitle?.title,
            icon: "fas fa-edit",
            url: route('job_title')
        }
    }
    return {
        title: "Buat Jabatan Baru",
        icon: "fas fa-plus-square",
        url: route('job_title')
    }
})
const breadcrumbItems = computed(() => {
    const items = [{ text: "Daftar Jabatan", url: route("job_title") }];
    items.push({
        text: pageMeta.value.title,
        url: null,
    })
    return items;
})

const loaderActive = ref(null);
const goBack = () => {
    loaderActive.value?.show("Memproses...");
    router.get(pageMeta.value.url, {}, {
        onFinish: () => loaderActive.value?.hide()
    });
}
const inputTitle = ref(null);
onMounted(() => {
    inputTitle.value?.focus();
});

</script>
<template>

    <Head :title="pageMeta.title" />
    <app-layout>
        <template #content>
            <loader-page ref="loaderActive" />
            <bread-crumbs :icon="pageMeta.icon" :title="pageMeta.title" :items="breadcrumbItems" />
            <div class="row pb-3">
                <div class="col-xl-12 col-sm-12 ">
                    <div class="card form-card border-0 shadow-lg rounded-4 overflow-hidden">
                        <div class="card-header bg-white p-4 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="icon-square-lg bg-primary bg-opacity-10 text-primary rounded-3 me-3">
                                    <i class="fas fa-building fs-4"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold text-dark mb-1">Form Data Jabatan</h5>
                                    <p class="text-muted small mb-0">Kelola informasi Jabatan.</p>
                                </div>
                            </div>
                            <Link @click.prevent="goBack" :href="pageMeta.url"
                                class="btn btn-danger fw-bold border px-3">
                                <i class="fas fa-arrow-left me-2"></i> Kembali
                            </Link>
                        </div>

                        <div class="card-body p-4 position-relative">
                            <loading-overlay :show="form.processing"
                                :text="props.jobTitle?.job_title_id ? 'Memperbarui Data Jabatan...' : 'Menyimpan Data Jabatan...'" />
                            <form-wrapper @submit="isSubmit">

                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label-custom text-muted mb-2">KODE JABATAN (AUTO)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0 text-muted">
                                                <i class="fas fa-hashtag"></i>
                                            </span>
                                            <text-input :is-valid="false" disabled v-model="form.job_title_code"
                                                name="code" input-class="bg-light text-dark fw-bold border-start-0" />
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <input-label class="form-label-custom mb-2" for="title" value="NAMA JABATAN" />
                                        <text-input ref="inputTitle" v-model="form.title" name="title"
                                            placeholder="Contoh: Staff Administrasi"
                                            input-class="form-control-lg fs-6" />
                                        <input-error :message="form.errors.title" />
                                    </div>

                                    <div class="col-md-6">
                                        <input-label class="form-label-custom mb-2" for="title_alias"
                                            value="SINGKATAN" />
                                        <text-input v-model="form.title_alias" name="title_alias"
                                            placeholder="Contoh: ADM" input-class="form-control-lg fs-6" />
                                        <input-error :message="form.errors.title_alias" />
                                    </div>

                                    <div class="col-12">
                                        <input-label class="form-label-custom mb-2" for="description"
                                            value="DESKRIPSI TUGAS" />
                                        <div class="quill-wrapper rounded-3 border overflow-hidden">
                                            <quill-text :maxChar="500"
                                                placeholder="Jelaskan tanggung jawab utama jabatan ini..."
                                                v-model="form.description" height="300px" />
                                        </div>
                                        <input-error :message="form.errors.description" />
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end mt-4 pt-3 gap-2">
                                    <button @click.prevent="goBack" class="btn btn-outline-secondary px-4">Batal &
                                        Kembali</button>
                                    <base-button :loading="form.processing"
                                        class="btn btn-height-1 rounded-3 px-4 shadow-sm"
                                        :icon="props.jobTitle?.job_title_id ? 'fas fa-check-circle' : 'fas fa-paper-plane'"
                                        :variant="props.jobTitle?.job_title_id ? 'success' : 'primary'" type="submit"
                                        :name="props.jobTitle?.job_title_id ? 'ubah' : 'simpan'"
                                        :label="props.jobTitle?.job_title_id ? 'Simpan Perubahan' : 'Buat Jabatan Baru'" />
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


/* Card Container */
.form-card {
    background: #ffffff;
    transition: all 0.3s ease;
}

/* Header Icon Box */
.icon-square-lg {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

/* Label Custom Modern */
.form-label-custom {
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    color: #6c757d;
    text-transform: uppercase;
}

/* Quill Editor Styling Override */
/* Agar editor terlihat menyatu dengan desain input Bootstrap */
.quill-wrapper {
    background-color: #fff;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.quill-wrapper:focus-within {
    border-color: #86b7fe !important;
    box-shadow: 0 0 0 0.20rem rgba(13, 109, 253, 0.089);
}



/* Read-Only Input Styling */
input:disabled {
    background-color: #f8f9fa !important;
    opacity: 1;
    cursor: not-allowed;
}

/* Button Animation */
.btn-save-animate {
    transition: transform 0.2s;
    font-weight: 600;
}

.btn-save-animate:hover {
    transform: translateY(-2px);
}

.btn-save-animate:active {
    transform: translateY(0);
}

.hover-scale {
    transition: transform 0.2s;
}

.hover-scale:hover {
    transform: scale(1.05);
}
</style>
