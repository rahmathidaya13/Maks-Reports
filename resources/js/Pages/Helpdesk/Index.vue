<script setup>
import { computed, ref } from "vue";
import { Head, usePage, router, Link, useForm } from '@inertiajs/vue3';
import { debounce } from "lodash";
import ModalFormRequest from "./ModalFormRequest.vue";
const props = defineProps({
    tickets: Object,
    baseProduct: Array,
    productRequest: Array
})
console.log(props.tickets);

const showRequestModal = ref(false);
const showNewTicketModal = ref(false);

const form = useForm({
    subject: '',
    category: 'general',
    message: ''
});

const submitTicket = () => {
    form.post(route('helpdesk.store'), {
        onSuccess: () => {
            showNewTicketModal.value = false;
            form.reset();
        }
    });
};
</script>
<template>

    <Head title="Halaman Pusat Bantuan" />
    <app-layout>
        <template #content>
            <callout />
            <div class="row mb-4">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="fw-bold mb-1">Pusat Bantuan</h4>
                        <p class="text-muted small mb-0">Hubungi admin atau ajukan permintaan.</p>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">

                <div class="col-6">
                    <div @click.prevent="showNewTicketModal = true"
                        class="card bg-primary text-white shadow-sm border-0 rounded-4 h-100 cursor-pointer hover-scale">
                        <div class="card-body d-flex align-items-center p-4">
                            <div class="bg-white bg-opacity-25 rounded-circle p-3 me-3">
                                <i class="fas fa-comments fs-3"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1">Chat Admin / Lapor Masalah</h5>
                                <small class="text-white text-opacity-75">Tanya kendala teknis atau umum</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div @click.prevent="showRequestModal = true"
                        class="card bg-warning text-dark shadow-sm border-0 rounded-4 h-100 cursor-pointer hover-scale">
                        <div class="card-body d-flex align-items-center p-4">
                            <div class="bg-white bg-opacity-25 rounded-circle p-3 me-3">
                                <i class="fas fa-hand-holding-usd fs-3"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1">Buat Permintaan / Pengajuan</h5>
                                <small class="text-dark text-opacity-75">Request diskon atau stok khusus</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="fw-bold mb-0">Riwayat Percakapan</h6>
                    </div>
                    <div class="list-group list-group-flush">
                        <Link v-for="ticket in tickets?.data" :key="ticket.ticket_id"
                            :href="route('helpdesk.show', ticket.ticket_id)"
                            class="list-group-item list-group-item-action p-3 d-flex align-items-center">

                            <div class="bg-light rounded-circle p-2 me-3 d-flex align-items-center justify-content-center"
                                style="width:45px; height:45px">
                                <i class="fas fa-hashtag text-muted"></i>
                            </div>

                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="fw-bold text-dark">{{ ticket.subject }}</span>
                                    <small class="text-muted">{{ new Date(ticket.updated_at).toLocaleDateString()
                                    }}</small>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted text-truncate" style="max-width: 250px;">
                                        {{ ticket.latest_message?.message ?? 'Belum ada pesan' }}
                                    </small>
                                    <span class="badge rounded-pill"
                                        :class="ticket.status === 'open' ? 'bg-success' : 'bg-secondary'">
                                        {{ ticket.status }}
                                    </span>
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>

                <div v-if="showNewTicketModal" class="modal fade show d-block" style="background: rgba(0,0,0,0.5)">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content rounded-4 border-0">
                            <div class="modal-header border-0">
                                <h5 class="fw-bold">Mulai Percakapan Baru</h5>
                                <button @click="showNewTicketModal = false" class="btn-close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="small fw-bold text-muted">Judul Masalah</label>
                                    <input v-model="form.subject" type="text" class="form-control"
                                        placeholder="Contoh: Pengiriman Lambat">
                                </div>
                                <div class="mb-3">
                                    <label class="small fw-bold text-muted">Pesan Pertama</label>
                                    <textarea v-model="form.message" class="form-control" rows="3"
                                        placeholder="Jelaskan masalah Anda..."></textarea>
                                </div>
                                <button @click="submitTicket" :disabled="form.processing"
                                    class="btn btn-primary w-100 fw-bold">Mulai Chat</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <ModalFormRequest :productRequest="productRequest" :products="baseProduct" :show="showRequestModal"
                @update:show="showRequestModal = $event" />
        </template>
    </app-layout>
</template>
<style scoped>
.hover-scale:hover {
    transform: translateY(-3px);
    transition: all 0.2s;
}
</style>
