<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { Head, Link, router, useForm, usePage } from "@inertiajs/vue3";
import { formatText } from "@/helpers/formatText";
import { formatTextFromSlug } from "@/helpers/formatTextFromSlug";
const props = defineProps({
    permissions: {
        type: Array,
        default: () => [{}]
    },
    role: Object
})
const form = useForm({
    name: formatTextFromSlug(props.role?.name) || "",
    permissions: props.role?.permissions?.map(p => p.name) || [],
});
const isSubmit = () => {
    if (props.role?.id) {
        form.put(route('roles.update', props.role.id), {
            onSuccess: () => {
                form.reset();
            },
            preserveScroll: true,
        })
    } else {
        // Create
        form.post(route('roles.store'), {
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
    if (props.role && props.role.id) {
        title.value = "Ubah Data Role"
        icon.value = "fas fa-edit"
        url.value = route('roles')
    } else {
        title.value = "Buat Data Role"
        icon.value = "fas fa-plus-square"
        url.value = route('roles')
    }
})

const breadcrumbItems = computed(() => {
    if (props.role && props.role.id) {
        return [
            { text: "Daftar Role", url: route("roles") },
            { text: "Buat Data Role", url: route("roles.create") },
            { text: title.value }
        ]
    }
    return [
        { text: "Daftar Role", url: route("roles") },
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
                    Form Role
                </h5>
                <div class="card-body">
                    <form-wrapper @submit="isSubmit">
                        <div class="mb-3">
                            <input-label class="fw-bold" for="name" value="Nama Role" />
                            <text-input autofocus v-model="form.name" name="name" />
                            <input-error :message="form.errors.name" />
                        </div>
                        <div class="mb-3">
                            <input-label class="fw-bold" value="Permission" />
                            <div :class="[{ 'text-bg-light border-success': form.permissions.length > 0, 'text-bg-danger': form.permissions.errors }]"
                                class="border p-3 rounded-3 d-flex gap-2 flex-wrap">
                                <label class="form-check-label gap-2 d-flex" v-for="perm in permissions" :key="perm.id">
                                    <input multiple class="form-check-input" type="checkbox" :value="perm.name"
                                        v-model="form.permissions" />
                                    <span class="fw-semibold">{{ formatTextFromSlug(perm.name) }}</span>
                                </label>
                            </div>
                            <input-error :message="form.errors.permissions" />
                        </div>
                        <div class="d-grid d-xl-block">
                            <base-button :loading="form.processing" class="rounded-3 bg-gradient px-5"
                                :icon="props.role?.id ? 'fas fa-edit' : 'fas fa-paper-plane'"
                                :variant="props.role?.id ? 'success' : 'primary'" type="submit"
                                :name="props.role?.id ? 'ubah' : 'simpan'"
                                :label="props.role?.id ? 'Ubah' : 'Simpan'" />
                        </div>
                    </form-wrapper>
                </div>
            </div>
        </template>
    </app-layout>

</template>
