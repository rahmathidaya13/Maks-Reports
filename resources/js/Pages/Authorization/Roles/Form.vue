<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { Head, Link, router, useForm, usePage } from "@inertiajs/vue3";
import { formatText } from "../../../helpers/formatText";
const props = defineProps({
    permissions: {
        type: Array,
        default: () => [{}]
    },
    role: {
        type: Object,
        default: () => ({}),
    },
})
const form = useForm({
    name: props.role?.name || "",
    permissions: [],
});
const isSubmit = () => {
    if (props.role?.id) {
        form.put(route('roles.update', props.role.id), {
            onSuccess: () => {
                form.reset();
            },
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


const togglePermission = (perm) => {
    if (form.permissions.includes(perm)) {
        form.permissions = form.permissions.filter(p => p !== perm)
    } else {
        form.permissions.push(perm)
    }
}

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
                            <input-label class="fw-bold" for="permissions" value="Izin Akses" />
                            <label class="form-check-label gap-2 d-flex" v-for="perm in permissions" :key="perm.id">
                                <input class="form-check-input" type="checkbox" :value="perm.name"
                                    :checked="form.permissions.includes(perm.name)"
                                    @change="togglePermission(perm.name)" />
                                <span>{{ formatText(perm.name) }}</span>
                            </label>
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
