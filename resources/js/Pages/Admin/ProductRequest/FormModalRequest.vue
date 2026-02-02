<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { Head, Link, router, usePage, useForm } from "@inertiajs/vue3";
import { formatDate } from "@/helpers/formatDate";
const props = defineProps({
    products: Array,
    show: Boolean,
    selected: Object
});

const emit = defineEmits(["update:show"]);
const close = () => {
    emit("update:show", false);
}
const form = useForm({
    status: '',
    admin_note: '',
});
const submit = () => {
    if (!props.selected) return;
    form.put(route('admin.request.update', props.selected.product_request_id), {
        onSuccess: () => {
            form.reset();
            close();
        }
    });
};
</script>
<template>
    <div class="row" v-if="props.show">
        <div class="col-xl-12 col-sm-12">
            <modal size="modal-lg" :footer="false" icon="fas fa-invoice" :show="props.show" title="Proses Permintaan"
                @closed="close">
                <template #body>
                    <p class="text-muted small mb-3">
                        Anda sedang memproses permintaan untuk produk
                        <strong class="text-dark">{{ selected?.product?.name }}</strong>
                        dari <strong class="text-dark">{{ selected?.user?.name }}</strong>
                        Cabang <strong class="text-dark text-capitalize">{{ selected?.user?.profile?.branch?.name
                            }}</strong>
                    </p>

                    <form-wrapper @submit="submit">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-dark">Konfirmasi</label>
                            <div class="d-flex gap-2">
                                <div class="flex-grow-1">
                                    <input type="radio" class="btn-check" name="status_opt" id="opt_approve"
                                        value="approved" v-model="form.status">
                                    <label
                                        class="btn w-100 rounded-3 border fw-bold d-flex align-items-center justify-content-center gap-2"
                                        :class="form.status === 'approved' ? 'btn-success text-white' : 'btn-outline-light text-muted'"
                                        for="opt_approve">
                                        <i class="fas fa-check-circle"></i> SETUJUI
                                    </label>
                                </div>
                                <div class="flex-grow-1">
                                    <input type="radio" class="btn-check" name="status_opt" id="opt_reject"
                                        value="rejected" v-model="form.status">
                                    <label
                                        class="btn w-100 rounded-3 border fw-bold d-flex align-items-center justify-content-center gap-2"
                                        :class="form.status === 'rejected' ? 'btn-danger text-white' : 'btn-outline-light text-muted'"
                                        for="opt_reject">
                                        <i class="fas fa-times-circle"></i> TOLAK
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-dark">Catatan (Opsional)</label>

                            <text-area :maxChar="300" name="admin_note" v-model="form.admin_note" :rows="5"
                                placeholder="Masukan catatan (Opsional)" />
                        </div>
                    </form-wrapper>
                    <div class="d-grid">
                        <button type="button" class="btn fw-bold px-4"
                            :class="form.status === 'approved' ? 'btn-success' : 'btn-danger'"
                            :disabled="form.processing" @click="submit">
                            <span v-if="form.processing" class="spinner-border spinner-border-sm me-2"></span>
                            Konfirmasi Keputusan
                        </button>
                    </div>
                </template>
            </modal>
        </div>
    </div>
</template>
