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
console.log(props.summaryStatusReport);
</script>
<template>

    <Head title="Dashboard" />
    <app-layout>
        <template #content>
            <bread-crumbs :home="false" icon="fas fa-tachometer-alt" title="Dashboard"
                :items="[{ text: 'Dashboard', url: route('home') }]" />
            <alert :variant="page.props.flash.message ? 'success' : 'danger'" :duration="10" :message="message" />

            <div class="row g-2">
                <div class="border-bottom border-1">
                    <h5 class="fw-bold"><i class="fas fa-info-circle"></i> Statistik Laporan Update Status</h5>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card text-bg-light bg-gradient mb-4 shadow">
                        <div class="card-body position-relative">
                            <i
                                class="fas fa-clipboard-list fa-4x text-success opacity-50 position-absolute top-50 end-0 translate-middle-y me-3"></i>

                            <div class="fw-bold text-uppercase mb-1 h5">
                                Total Laporan
                            </div>

                            <div>Periode: {{ props.summaryStatusReport?.periode }}</div>

                            <div class="h1 mb-0 fw-bold">
                                {{ props.summaryStatusReport?.totalCountStatus }}
                            </div>

                            <small class="text-muted">Update Status</small>
                        </div>
                    </div>
                </div>


                <div class="col-xl-3 col-md-6">
                    <div title="Update tidak lebih dari 15 menit dari status sebelumnya"
                        class="card text-bg-light bg-gradient mb-4 shadow">
                        <div class="card-body position-relative">
                            <i
                                class="fas fa-bolt fa-4x text-primary opacity-50 position-absolute top-50 end-0 translate-middle-y me-3"></i>

                            <div class="fw-bold text-uppercase mb-1 h5">AKTIF (&lt; 15 Menit)
                            </div>

                            <div>Periode: {{ props.summaryStatusReport?.periode }}</div>

                            <div class="h1 mb-0 fw-bold">
                                {{ props.summaryStatusReport?.activePeriods }}
                            </div>

                            <small class="text-muted">Efisiensi Cepat Update Status</small>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div title="Update tidak lebih dari 15 menit dari status sebelumnya"
                        class="card text-bg-light bg-gradient mb-4 shadow">
                        <div class="card-body position-relative">
                            <i
                                class="fas fa-hourglass-half fa-4x text-warning opacity-50 position-absolute top-50 end-0 translate-middle-y me-3"></i>

                            <div class="fw-bold text-uppercase mb-1 h5">Pasif (&gt; 15 Menit)
                            </div>

                            <div>Periode: {{ props.summaryStatusReport?.periode }}</div>

                            <div class="h1 mb-0 fw-bold">
                                {{ props.summaryStatusReport?.passivePeriods }}
                            </div>

                            <small class="text-muted">Potensi Lambat Update Status</small>
                        </div>
                    </div>
                </div>



            </div>
        </template>
    </app-layout>
</template>
<style scoped></style>