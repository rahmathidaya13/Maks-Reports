<script setup>
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    users: Object
})
console.log(props.users);
</script>
<template>
    <Head title="Halaman Detail Pengguna" />
    <app-layout>
        <template #content>
            <!-- <bread-crumbs :home="false" icon="fas fa-briefcase" title="Daftar User"
                :items="[{ text: 'Daftar User' }]" /> -->
            <!-- <alert :duration="10" :message="message" /> -->
            <div class="row">
                <div class="col-xl-12 col-sm-12">



                </div>
            </div>
        </template>
    </app-layout>
</template>
