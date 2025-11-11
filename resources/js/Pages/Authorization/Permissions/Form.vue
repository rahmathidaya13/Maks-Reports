<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { Head, Link, router, useForm, usePage } from "@inertiajs/vue3";
import { formatTextFromSlug } from "../../../helpers/formatTextFromSlug";
const props = defineProps({
    permissions: Object,
})
const form = useForm({
    name: formatTextFromSlug(props.permissions?.name) || "",
    guard_name: props.permissions?.guard_name || "web",
});
const isSubmit = () => {
    if (props.permissions?.id) {
        form.put(route('permissions.update', props.permissions.id), {
            onSuccess: () => {
                form.reset();
            },
            preserveScroll: true,
        })
    } else {
        // Create
        form.post(route('permissions.store'), {
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
    if (props.permissions && props.permissions.id) {
        title.value = "Ubah Data Permission"
        icon.value = "fas fa-edit"
        url.value = route('permissions', props.permissions.id)
    } else {
        title.value = "Buat Data Permission"
        icon.value = "fas fa-plus-square"
        url.value = route('permissions')
    }
})

const breadcrumbItems = computed(() => {
    if (props.permissions && props.permissions.id) {
        return [
            { text: "Daftar Permission", url: route("permissions") },
            { text: "Buat Data Permission", url: route("permissions.create") },
            { text: title.value }
        ]
    }
    return [
        { text: "Daftar Permission", url: route("permissions") },
        { text: title.value }
    ]
})


// const togglePermission = (perm) => {
//     if (form.permissions.includes(perm)) {
//         form.permissions = form.permissions.filter(p => p !== perm)
//     } else {
//         form.permissions.push(perm)
//     }
// }


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
                    Form Permission
                </h5>
                <div class="card-body">
                    <form-wrapper @submit="isSubmit">
                        <div class="mb-3">
                            <input-label class="fw-bold" for="name" value="Name Permission" />
                            <text-input autofocus v-model="form.name" name="name" />
                            <input-error :message="form.errors.name" />
                        </div>
                        <div class="mb-3">
                            <input-label class="fw-bold" for="guard_name" value="Guard Name" />
                            <select-input text="Select guard name" :options="[
                                { value: 'web', label: 'Web' },
                                { value: 'api', label: 'API' },
                            ]" v-model="form.guard_name" name="guard_name" />
                            <input-error :message="form.errors.guard_name" />
                        </div>


                        <div class="d-grid d-xl-block">
                            <base-button :loading="form.processing" class="rounded-3 bg-gradient px-5"
                                :icon="props.permissions?.id ? 'fas fa-edit' : 'fas fa-paper-plane'"
                                :variant="props.permissions?.id ? 'success' : 'primary'" type="submit"
                                :name="props.permissions?.id ? 'ubah' : 'simpan'"
                                :label="props.permissions?.id ? 'Ubah' : 'Simpan'" />
                        </div>
                    </form-wrapper>
                </div>
            </div>
        </template>
    </app-layout>

</template>
