<script setup>
import { computed } from "vue";
import { Head, usePage } from '@inertiajs/vue3';
const page = usePage();
const message = computed(() => {
    return page.props.flash.message || page.props.flash.error
});

const props = defineProps({
    summaryStatusReport: Object
})
console.log(page.props.auth.user.permissions);
</script>
<template>

    <Head title="Dashboard" />
    <app-layout>
        <template #content>
            <bread-crumbs :home="false" icon="fas fa-tachometer-alt" title="Dashboard"
                :items="[{ text: 'Dashboard', url: route('home') }]" />
            <callout :type="page.props.flash.message ? 'success' : 'danger'" :duration="10" :message="message" />

            <div class="row g-2">
                <!-- <div class="border-bottom border-1">
                    <h5 class="fw-bold"><i class="fas fa-info-circle"></i> Statistik Laporan Update Status</h5>
                </div> -->


                <!-- Test Page Home/Dashboard -->
                <div class="card">
                    <div class="card-body">
                        <chart-bar :options="{
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top'
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        precision: 0
                                    }
                                }
                            }
                        }" :data="[12, 13, 8, 9, 7, 4, 9, 20, 8, 9, 7, 4]" label="Test Char Bar"
                            :labels="['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']" />
                    </div>
                </div>

            </div>
        </template>
    </app-layout>
</template>
<style scoped></style>
