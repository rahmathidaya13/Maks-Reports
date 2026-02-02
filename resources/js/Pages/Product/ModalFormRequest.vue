<script setup>
import { useForm, router, usePage } from "@inertiajs/vue3";
import { computed, reactive, watch, ref } from "vue";
import { useConfirm } from "@/helpers/useConfirm.js";

const confirm = useConfirm();
const props = defineProps({
    show: Boolean,
    products: Array,
    productRequest: Array,
});
// State untuk Mode Edit
const isEdit = ref(false);
const editById = ref(null);
const emit = defineEmits(["update:show"]);
const form = useForm({
    product_id: null,
    current_price: 0,
    requested_price: 0,
    reason: '',
});
console.log(usePage().props.errors);
const selectedProduct = ref(null);
watch(() => form.product_id, (newId) => {
    if (newId) {
        selectedProduct.value = props.products.find(p => p.product_id === newId);
    } else {
        selectedProduct.value = null;
    }
});

const submit = () => {
    if (isEdit.value) {
        form.put(route('product.request.update', editById.value), {
            onSuccess: () => {
                form.reset(),
                    close()
            },
            preserveScroll: true
        });
    } else {
        form.post(route('product.request.store'), {
            onSuccess: () => {
                form.reset(),
                    close()
            },
            preserveScroll: true
        });
    }
}
const close = () => {
    resetForm();
    usePage().props.errors = {}
    emit("update:show", false);
}
const resetForm = () => {
    form.reset();
    form.clearErrors();
    isEdit.value = false;
    editById.value = null;
};
function formatCurrency(value) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
}
const productOptions = computed(() => {
    return props.products?.map((value) => ({
        id: value.product_id,
        text: value.name
    }))
})
const editRequest = (req) => {
    isEdit.value = true;
    editById.value = req.product_request_id;

    // Isi form dengan data yang mau diedit
    form.product_id = req.product_id;
    form.requested_price = req.requested_price;
    form.reason = req.reason;
    form.current_price = req.current_price; // Harga saat request dibuat
};

</script>
<template>
    <div class="row" v-if="props.show">
        <div class="col-12">
            <modal size="modal-lg" width="900px" :footer="false" icon="fas fa-hand-holding-usd text-primary"
                :show="props.show" title="Form Permintaan & Pengajuan" @closed="close">
                <template #body>
                    <div class="px-2 p-3">
                        <div class="bg-light border border-dashed rounded-3 p-3 mb-4 position-relative">
                            <div class="position-absolute top-0 start-0 translate-middle-y ms-3">
                                <span class="badge rounded-pill shadow-sm"
                                    :class="isEdit ? 'bg-warning text-dark' : 'bg-primary'">
                                    <i class="fas me-1" :class="isEdit ? 'fa-edit' : 'fa-plus'"></i>
                                    {{ isEdit ? 'Edit Pengajuan' : 'Buat Pengajuan Baru' }}
                                </span>
                            </div>

                            <form-wrapper @submit="submit">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="small fw-bold text-muted mb-1">Pilih Produk</label>
                                        <select-2
                                            :settings="{ width: '100%', placeholder: 'Cari Produk...', allowClear: true }"
                                            name="product_id" :options="productOptions" v-model="form.product_id" />
                                        <input-error :message="form.errors.product_id" />
                                    </div>
                                    <div class="col-6">
                                        <label class="small fw-bold text-muted mb-1">Harga Saat
                                            ini</label>
                                        <div class="input-group">
                                            <currency-input :disabled="!selectedProduct" :decimals="0"
                                                v-model="form.current_price" name="current_price" placeholder="0" />
                                        </div>
                                        <input-error :message="form.errors.current_price" />
                                    </div>
                                    <div class="col-6">
                                        <label class="small fw-bold text-muted mb-1">Harga Diajukan (Opsional)</label>
                                        <div class="input-group">
                                            <currency-input :disabled="!selectedProduct" :decimals="0"
                                                v-model="form.requested_price" name="requested_price" placeholder="0" />
                                        </div>
                                        <div v-if="form.current_price > 0" class="text-xs mt-1 text-muted">
                                            Harga normal: {{ formatCurrency(form.current_price) }}
                                        </div>
                                        <input-error :message="form.errors.requested_price" />
                                    </div>

                                    <div class="col-12">
                                        <label class="small fw-bold text-muted mb-1">Alasan / Keterangan</label>
                                        <text-area :maxChar="300" name="reason" v-model="form.reason" :rows="5"
                                            placeholder="Contoh: Minta diskon spesial atau inputkan produk dll..." />
                                        <input-error :message="form.errors.reason" />
                                    </div>

                                </div>
                            </form-wrapper>
                            <div class="d-grid mt-3">

                                <button @click.prevent="submit" type="submit" class="btn px-4 fw-bold"
                                    :class="isEdit ? 'btn-warning text-dark' : 'btn-primary'">
                                    <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                                    {{ isEdit ? 'Simpan Perubahan' : 'Kirim Pengajuan' }}
                                </button>
                                <button v-if="isEdit" type="button" @click="resetForm" class="btn  border-0">
                                    Batal Edit
                                </button>
                            </div>
                        </div>

                        <div class="d-flex align-items-center mb-3">
                            <hr class="flex-grow-1 border-secondary opacity-25">
                            <span class="mx-3 text-muted text-xs text-uppercase fw-bold ls-1">Daftar Pengajuan
                                Aktif</span>
                            <hr class="flex-grow-1 border-secondary opacity-25">
                        </div>

                        <div class="card border shadow-none">
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0 table-hover">
                                    <thead class="bg-light text-muted text-xs fw-bold text-uppercase">
                                        <tr>
                                            <th class="ps-3 py-2">Produk</th>
                                            <th class="py-2">Pengajuan</th>
                                            <th class="py-2">Alasan</th>
                                            <th class="py-2 text-center">Status</th>
                                            <th class="pe-3 py-2 text-end">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="req in productRequest" :key="req.product_request_id"
                                            :class="{ 'table-warning': editById === req.product_request_id }">
                                            <td class="ps-3 py-2">
                                                <div class="fw-bold text-dark text-xs">{{ req.product?.name }}</div>
                                            </td>

                                            <td class="py-2">
                                                <div class="fw-bold text-primary text-xs">{{
                                                    formatCurrency(req.requested_price) }}</div>
                                            </td>

                                            <td class="py-2">
                                                <div class="text-xs text-muted text-truncate" style="max-width: 150px;"
                                                    :title="req.reason">
                                                    {{ req.reason }}
                                                </div>
                                                <div v-if="req.admin_note" class="mt-1 text-xs text-info fst-italic">
                                                    <i class="fas fa-reply me-1"></i> Admin: {{ req.admin_note }}
                                                </div>
                                            </td>

                                            <td class="py-2 text-center">
                                                <span v-if="req.status === 'pending'"
                                                    class="badge bg-warning text-dark text-xxs">Pending</span>
                                                <span v-else-if="req.status === 'approved'"
                                                    class="badge bg-success text-xxs">Disetujui</span>
                                                <span v-else class="badge bg-danger text-xxs">Ditolak</span>
                                            </td>

                                            <td class="py-2 text-end">
                                                <div v-if="req.status === 'pending'" class="btn-group">
                                                    <button @click="editRequest(req)"
                                                        class="btn btn-icon btn-sm btn-light text-primary hover-scale"
                                                        title="Edit">
                                                        Ubah
                                                    </button>
                                                </div>
                                                <span v-else class="text-xs text-muted fst-italic">Terkunci</span>
                                            </td>
                                        </tr>

                                        <tr v-if="productRequest.length === 0">
                                            <td colspan="5" class="text-center py-4 text-muted text-xs">
                                                Tidak ada pengajuan aktif saat ini.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </template>
            </modal>
        </div>
    </div>
</template>
