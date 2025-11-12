<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { Head, Link, router, useForm, usePage } from "@inertiajs/vue3";
import { formatText } from "../../../helpers/formatText";
import { formatTextFromSlug } from "../../../helpers/formatTextFromSlug";
const props = defineProps({
    users: Object,
    permissions: Array,
    roles: Array
})
const form = useForm({
    name: props.users.name || "",
    email: props.users.email || "",
    status: props.users.status || "",
    roles: props.users.roles ? props.users.roles.join(",") : "",
    permissions: props.users.permissions || [],
})

const isSubmit = () => {
    form.put(route('users.update', props.users.id), {
        onSuccess: () => {
            form.reset();
        },
        preserveScroll: true,
    })
};
const title = ref("");
const icon = ref("");
const url = ref("")
onMounted(() => {
    if (props.users && props.users.id) {
        title.value = "Ubah Data User"
        icon.value = "fas fa-edit"
        url.value = route('users')
    }
})

const breadcrumbItems = computed(() => {
    if (props.users && props.users.id) {
        return [
            { text: "Daftar User", url: route("users") },
            { text: title.value }
        ]
    }
})

const roleOptions = computed(() => {
    return props.roles?.map((role) => ({
        value: role.name,
        label: formatTextFromSlug(role.name)
    }))
})
const defaultPermissionByRole = computed(() => {
    const selectedRole = props.roles.find(role => role.name === form.roles)
    return selectedRole ? selectedRole.permissions.map(p => p.name) : []
})


const userOptions = computed(() =>
    props.users.map(user => ({
        label: user.name,
        value: user.name,
    }))
)

const getBadgeClass = (permName) => {
    switch (true) {
        case /create|handle/i.test(permName):
            return 'bg-success'
        case /edit|update|download/i.test(permName):
            return 'bg-warning text-dark'
        case /delete|remove|cancel/i.test(permName):
            return 'bg-danger'
        case /read|share|export|import/i.test(permName):
            return 'bg-info text-white'
        case /manage|access|assign/i.test(permName):
            return 'bg-primary'
        default:
            return 'bg-secondary'
    }
}
// otomatis set permission form ke permission default saat role berubah
watch(
    () => form.roles,
    (newRole) => {
        const selectedRole = props.roles.find(role => role.name === newRole)
        form.permissions = selectedRole
            ? selectedRole.permissions.map(p => p.name)
            : []
    }
)
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
            <div class="row">
                <div class="col-xl-12 col-sm-12 pb-3">
                    <div class="card overflow-hidden rounded-4 bg-light">
                        <h5 class="card-header fw-bold text-uppercase p-3 text-bg-secondary">
                            <i class="fas fa-info-circle me-1 text-light"></i>
                            Form Role
                        </h5>
                        <div class="card-body">
                            <form-wrapper @submit="isSubmit">
                                <div class="mb-3">
                                    <div class="mb-2 fw-bold">Akun</div>
                                    <div
                                        :class="[{ 'text-bg-light border-success text-bg-light border rounded-3 p-3': props.users.name && props.users.email }]">
                                        <table class="table table-borderless align-middle mb-0">
                                            <tbody>
                                                <tr>
                                                    <th scope="row" style="width: 100px;">Google ID</th>
                                                    <td>{{ props.users.google_id ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row" style="width: 100px;">Name</th>
                                                    <td>{{ props.users.name }}</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row" style="width: 100px;">Email</th>
                                                    <td>{{ props.users.email ?? 'Not Found' }}</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row" style="width: 100px;">Roles</th>
                                                    <td>{{props.users.roles.map(role =>
                                                        formatTextFromSlug(role)).join(', ')}}</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row" style="width: 100px;">Status</th>
                                                    <td>
                                                        <span :class="[
                                                            'badge text-capitalize px-3 py-2',
                                                            props.users.status == 'active' ? 'bg-info' : 'bg-danger',
                                                        ]">
                                                            {{ formatTextFromSlug(props.users.status) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th scope="row" style="width: 100px;">Online</th>
                                                    <td>
                                                        <span :class="[
                                                            'badge text-capitalize px-3 py-2',
                                                            props.users.is_active == true ? 'bg-success' : 'bg-secondary',
                                                        ]">
                                                            {{ formatTextFromSlug(props.users.is_active == true ?
                                                                'Available' :
                                                                'Unavailable') }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <input-label class="fw-bold" for="status" value="Status" />
                                    <select-input name="status" text="Select Status"
                                        :options="[{ value: 'active', label: 'Active' }, { value: 'inactive', label: 'Inactive' }]"
                                        v-model="form.status" />
                                    <input-error :message="form.errors.status" />
                                </div>

                                <div
                                    :class="[{ 'text-bg-light border-success text-bg-light border mb-3 rounded-3 p-3': form.roles.length > 0, 'text-bg-danger': form.permissions.roles }]">
                                    <div class="mb-2">
                                        <input-label class="fw-bold" for="roles" value="Peran" />
                                        <select-input text="Select Role" name="role" :options="roleOptions"
                                            v-model="form.roles" />
                                        <input-error :message="form.errors.roles" />
                                    </div>
                                    <div class="mb-2 fw-bold">By Default</div>
                                    <div v-if="defaultPermissionByRole.length">
                                        <ul class="list-unstyled mb-0 d-flex flex-wrap gap-2">
                                            <li v-for="perm in defaultPermissionByRole" :key="perm">
                                                <span :class="[
                                                    'badge text-capitalize px-3 py-2',
                                                    getBadgeClass(perm)
                                                ]">
                                                    {{ formatTextFromSlug(perm) }}
                                                </span>
                                            </li>
                                        </ul>

                                    </div>
                                </div>

                                <div class="mb-3">
                                    <input-label class="fw-bold" value="Izin Otoritas" />
                                    <div :class="[{ 'text-bg-light border-success': form.permissions.length > 0, 'text-bg-danger': form.permissions.errors }]"
                                        class="border p-3 rounded-3 d-flex gap-2 flex-wrap">
                                        <label class="form-check-label gap-2 d-flex" v-for="perm in permissions"
                                            :key="perm.id">
                                            <input multiple class="form-check-input" type="checkbox" :value="perm.name"
                                                v-model="form.permissions" />
                                            <span class="fw-semibold">{{ formatTextFromSlug(perm.name) }}</span>
                                        </label>
                                    </div>
                                    <input-error :message="form.errors.permissions" />
                                </div>
                                <div class="d-grid d-xl-block">
                                    <base-button :loading="form.processing" class="rounded-3 bg-gradient px-5"
                                        :icon="props.users?.id ? 'fas fa-edit' : 'fas fa-paper-plane'"
                                        :variant="props.users?.id ? 'success' : 'primary'" type="submit"
                                        :name="props.users?.id ? 'ubah' : 'simpan'"
                                        :label="props.users?.id ? 'Ubah' : 'Simpan'" />
                                </div>
                            </form-wrapper>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </app-layout>

</template>
