<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { Head, Link, router, useForm, usePage } from "@inertiajs/vue3";

import { useConfirm } from "@/helpers/useConfirm.js"
const confirm = useConfirm(); // Memanggil fungsi confirm untuk alert

const props = defineProps({
    transaction: Object,
    customer: Object,
    product: Object
})

// form fields
const form = useForm({
    invoice: props.transaction?.invoice ?? '',
    customer_id: props.transaction?.customer?.customer_id ?? '',
    items: props.transaction?.items?.map((item) => {
        const gross = Number(item.price_unit) * Number(item.quantity); // Harga Bruto/ Kotor
        const nominalDisc = Number(item.discount_amount); // Nominal Diskon
        const percentage = gross > 0 ? (nominalDisc / gross) * 100 : 0; // Persentase Diskon

        return {
            product_id: item.product_id,
            price_original: Number(item.price_unit),
            discount_percentage: Number(percentage),
            quantity: Number(item.quantity),
            product_name: item.product?.name,
        }
    }) ?? [],

    payment_type: props.transaction?.payments[0].payment_type ?? null,
    payment_method: props.transaction?.payments[0].payment_method ?? null,
    amount: Number(props.transaction?.payments[0].amount ?? 0),
    tax_percentage: Number(props.transaction?.tax_percentage ?? 0),
});

// STATE UNTUK INPUT BARANG SEMENTARA

const tempItem = reactive({
    product_id: '',
    price_original: 0,
    discount_percentage: 0,
    quantity: 1
})

watch(() => tempItem.product_id, () => {
    tempItem.price_original = 0;
    tempItem.quantity = 1;
    tempItem.discount_percentage = 0
})

const addItem = () => {
    if (!tempItem.product_id) {
        alert("Pilih produk terlebih dahulu.");
        return;
    }

    // VALIDASI TAMBAHAN: Harga tidak boleh 0 atau kosong
    if (!tempItem.price_original || tempItem.price_original <= 0) {
        alert("Mohon isi Harga Satuan produk ini secara manual.");
        return;
    }

    const existingIndex = form.items.findIndex(i => i.product_id === tempItem.product_id);
    if (existingIndex !== -1) {
        form.items[existingIndex].quantity += parseInt(tempItem.quantity);
        form.items[existingIndex].price_original = tempItem.price_original;
        form.items[existingIndex].discount_percentage = tempItem.discount_percentage;
    } else {
        const selected = props.product.find(p => p.product_id === tempItem.product_id);
        form.items.push({
            product_id: tempItem.product_id,
            price_original: tempItem.price_original, // Ambil dari input manual
            quantity: tempItem.quantity,
            discount_percentage: tempItem.discount_percentage,
            product_name: selected?.name || 'Unknown Product',
        });
    }

    // Reset input sementara
    tempItem.product_id = '';
    tempItem.price_original = 0;
    tempItem.discount_percentage = 0
    tempItem.quantity = 1;
};

// Function Hapus Item
const removeItem = (index) => {
    form.items.splice(index, 1);
};

// kalkulasi uang
const subTotal = computed(() => {
    return form.items.reduce((total, item) => {
        const gross = item.price_original * item.quantity;
        const nominalDisc = gross * (Number(item.discount_percentage) / 100);
        return total + (gross - nominalDisc);
    }, 0);
});
// Hitung Pratinjau Nominal Pajak
const taxAmountPreview = computed(() => {
    const percentage = Number(form.tax_percentage) || 0;
    return subTotal.value * (percentage / 100);
});
// Hitung Grand Total (Sub Total + Pajak)
const grandTotal = computed(() => {
    return subTotal.value + taxAmountPreview.value;
});
// minimal dana pertama
const dpAmount = computed(() => {
    return grandTotal.value * 0.5
})
const isEditMode = computed(() => {
    return !!props.transaction?.transaction_id;
});
const isSubmit = async () => {
    const isEdit = !!props.transaction?.transaction_id; // mode edit
    const isGoToRepayment = form.payment_type === 'repayment';  // jika dipilih lunas
    const settConfirm = !isEdit || isGoToRepayment;
    if (settConfirm) {
        const ok = await confirm.ask({
            title: isEdit ? 'Konfirmasi Pelunasan' : 'Konfirmasi Transaksi Baru',
            message: isGoToRepayment
                ? 'Transaksi yang ditandai LUNAS tidak akan bisa diubah lagi. Pastikan data yang dibuat sudah benar sebelum diproses.'
                : 'Mohon periksa kembali nominal dan data pelanggan. Pastikan data yang dibuat sudah benar sebelum diproses.',
            confirmText: isEdit ? 'Proses Perubahan' : 'Proses Sekarang',
            variantIcon: isEdit ? 'success' : 'primary',
            variantButton: isEdit ? 'success' : 'primary',
            requireCheckbox: true,
            icon: isEdit ? 'fas fa-check-circle' : 'fas fa-check-circle',
            checkboxText: 'Saya menyatakan data sudah benar & siap diproses.'
        });
        if (!ok) return;
    }
    executeSubmit()
};
const executeSubmit = () => {
    const method = props.transaction?.transaction_id ? 'put' : 'post';
    const actionRoute = props.transaction?.transaction_id
        ? route('transaction.update', props.transaction?.transaction_id)
        : route('transaction.store');
    form[method](actionRoute, {
        onSuccess: () => form.reset(),
    });
}
const customerOptions = computed(() => {
    return props.customer?.map((value) => ({
        id: value.customer_id,
        text: value.customer_name
    }))
})
const productOptions = computed(() => {
    return props.product?.map((value) => ({
        id: value.product_id,
        text: value.name
    }))
})
// end form fields

const pageMeta = computed(() => {
    if (isEditMode.value) {
        return {
            title: "Ubah Data Transaksi - " + props.transaction?.customer.customer_name,
            icon: "fas fa-edit",
            url: route('transaction')
        }
    } else {
        return {
            title: "Buat Transaksi Baru",
            icon: "fas fa-plus-square",
            url: route('transaction')
        }
    }
})
const breadcrumbItems = computed(() => {
    const items = [
        { text: "Daftar Transaksi", url: route("transaction") },
    ];
    items.push({
        text: pageMeta.value.title,
        url: null
    })
    return items;
})
// breadcrumb fields edn

// splash loader screen
const loaderActive = ref(null);
const goBack = () => {
    loaderActive.value?.show("Memproses...");
    router.get(pageMeta.value.url, {}, {
        onFinish: () => loaderActive.value?.hide()
    });
}
// splash loader screen end
function formatCurrency(value) {
    if (!value) return "0";
    return new Intl.NumberFormat('id-ID', {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value)
}

</script>
<template>

    <Head :title="pageMeta.title" />
    <app-layout>
        <template #content>
            <loader-page ref="loaderActive" />
            <bread-crumbs :icon="pageMeta.icon" :title="pageMeta.title" :items="breadcrumbItems" />
            <div class="row g-2 pb-3">

                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                        <div
                            class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">

                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3 me-3">
                                    <i class="fas fa-file-invoice fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-uppercase ls-1">Form Transaksi</h6>
                                    <small class="text-muted">Isi detail transaksi pelanggan di bawah ini</small>
                                </div>
                            </div>
                            <Link @click.prevent="goBack" :href="pageMeta.url" class="btn btn-sm btn-danger mb-3">
                                <i class="fas fa-arrow-left"></i>
                                Kembali
                            </Link>
                        </div>

                        <div class="card-body p-4 position-relative">
                            <loading-overlay :show="form.processing"
                                :text="isEditMode ? 'Memperbarui Transaksi...' : 'Menyimpan Transaksi...'" />

                            <form-wrapper @submit="isSubmit">

                                <h6 class="text-primary fw-bold mb-3 small text-uppercase">
                                    <i class="fas fa-user-tag me-1"></i> Informasi Dasar
                                </h6>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <input-label class="fw-bold small" for="invoice" value="Nomor Invoice" />
                                        <div class="position-relative">
                                            <i class="fas fa-hashtag input-icon-left"></i>
                                            <text-input input-class="input-fixed-height" name="invoice"
                                                v-model="form.invoice" />
                                        </div>
                                        <input-error :message="form.errors.invoice" />
                                    </div>
                                    <div class="col-md-6">
                                        <input-label class="fw-bold small" for="customer_id" value="Pelanggan" />
                                        <select-2 :settings="{
                                            width: '100%',
                                            placeholder: 'Cari Pelanggan...',
                                            allowClear: true,
                                        }" name="customer_id" :options="customerOptions" v-model="form.customer_id" />
                                        <input-error :message="form.errors.customer_id" />
                                    </div>
                                </div>

                                <hr class="border-dashed my-4 opacity-50">

                                <h6
                                    class="text-primary fw-bold mb-3 small text-uppercase d-flex justify-content-between">
                                    <span><i class="fas fa-box-open me-1"></i> Detail Produk & Pembayaran</span>
                                </h6>

                                <div class="bg-light p-3 rounded-3 border mb-3">
                                    <div class="row g-2 align-items-end">
                                        <div class="col-12">
                                            <label class="small fw-bold mb-1" for="product_id">Pilih Produk</label>
                                            <select-2 :settings="{ width: '100%', placeholder: 'Cari Produk...' }"
                                                :options="productOptions" v-model="tempItem.product_id" />
                                        </div>
                                        <div class="col-5">
                                            <label class="small fw-bold mb-1" for="price_original">Harga Satuan
                                                (Asli)</label>
                                            <currency-input :isValid="false" :decimals="0"
                                                v-model="tempItem.price_original" />
                                        </div>
                                        <div class="col-5">
                                            <label class="small fw-bold mb-1" for="quantity">Jumlah (Qty)</label>
                                            <input-number placeholder="0" :isValid="false" v-model="tempItem.quantity"
                                                name="quantity" />
                                        </div>
                                        <div class="col-2">
                                            <button type="button" @click="addItem" class="btn btn-primary w-100 fw-bold"
                                                :disabled="!tempItem.product_id || !tempItem.price_original">
                                                <i class="fas fa-plus me-1"></i> Tambah
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm align-middle">
                                        <thead class="table-light text-center small text-uppercase text-muted">
                                            <tr>
                                                <th style="width: 30%">Produk</th>
                                                <th style="width: 10%">Qty</th>
                                                <th style="width: 20%">Harga Satuan</th>
                                                <th style="width: 15%">Diskon(%)</th>
                                                <th style="width: 20%">Subtotal</th>
                                                <th style="width: 5%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-if="form.items.length === 0">
                                                <td colspan="6" class="text-center py-3 text-muted small fst-italic">
                                                    Belum ada produk yang ditambahkan.
                                                </td>
                                            </tr>
                                            <tr v-for="(item, index) in form.items" :key="index">
                                                <td>
                                                    <span class="fw-bold text-dark small">{{ item.product_name }}</span>
                                                </td>
                                                <td>
                                                    <input type="number" v-model="item.quantity" min="1"
                                                        class="form-control form-control-sm">
                                                </td>
                                                <td>
                                                    <currency-input :isValid="false" :decimals="0"
                                                        v-model="item.price_original" class="form-control-sm" />
                                                </td>
                                                <td>
                                                    <input-number input-class="form-control-sm" :isValid="false"
                                                        placeholder="0" v-model="item.discount_percentage"
                                                        name="discount_percentage" />
                                                </td>
                                                <td class="text-end fw-bold text-dark pe-3">
                                                    {{ formatCurrency((item.price_original * item.quantity) -
                                                        ((item.price_original * item.quantity) * (item.discount_percentage /
                                                            100)))
                                                    }}
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" @click="removeItem(index)"
                                                        class="btn btn-link text-danger btn-sm p-0">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot v-if="form.items.length > 0">
                                            <tr>
                                                <td colspan="4" class="text-end fw-bold small text-uppercase pe-3">Total
                                                    Tagihan</td>
                                                <td class="text-end fw-bolder text-primary pe-3 fs-6">
                                                    {{ formatCurrency(grandTotal) }}
                                                </td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <hr class="border-dashed my-3 opacity-50">
                                <h6 class="text-primary fw-bold mb-3 small text-uppercase">
                                    <i class="fas fa-wallet me-1"></i> Pembayaran & Pajak
                                </h6>

                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <input-label class="fw-bold small" value="Jenis Pembayaran" />
                                        <select-input :disabled="props.transaction?.transaction_id" name="payment_type"
                                            :options="[
                                                { value: null, label: '— Pilih Opsi —' },
                                                { value: 'payment', label: 'DP / Cicilan (50%)' },
                                                { value: 'repayment', label: 'Lunas Langsung' },
                                            ]" v-model="form.payment_type" />
                                        <input v-if="props.transaction?.transaction_id" type="hidden"
                                            v-model="form.payment_type">
                                        <input-error :message="form.errors.payment_type" />
                                    </div>
                                    <div class="col-md-4">
                                        <input-label class="fw-bold small" value="Metode" />
                                        <select-input name="payment_method" :options="[
                                            { value: null, label: '— Pilih Metode —' },
                                            { value: 'cash', label: '💵 Tunai (Cash)' },
                                            { value: 'transfer', label: '🏦 Transfer Bank' },
                                            { value: 'debit', label: '💳 Kartu Debit' },
                                            { value: 'qris', label: '𝄃𝄂𝄂𝄀𝄁𝄃𝄂𝄂𝄃 QRIS' },
                                        ]" v-model="form.payment_method" />
                                        <input-error :message="form.errors.payment_method" />
                                    </div>

                                    <div class="col-md-4">
                                        <input-label class="fw-bold small" value="Pajak PPN (%)" />
                                        <input-number placeholder="0" v-model="form.tax_percentage"
                                            name="tax_percentage" />
                                        <input-error :message="form.errors.tax_percentage" />
                                    </div>

                                    <div class="col-12" v-if="form.payment_type === 'payment'">
                                        <div
                                            class="bg-warning bg-opacity-10 p-3 rounded-3 border border-warning border-opacity-25 mt-2">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <label class="fw-bold text-warning-emphasis mb-0">Nominal DP (Down
                                                    Payment)</label>
                                                <span class="badge bg-warning text-dark">Wajib Min. 50%</span>
                                            </div>
                                            <currency-input :decimals="0" v-model="form.amount" name="amount"
                                                placeholder="Input nominal DP..." />
                                            <input-error :message="form.errors.amount" />

                                            <div class="mt-2 small text-muted">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Minimal DP yang disarankan: <strong>{{ formatCurrency(dpAmount)
                                                    }}</strong>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </form-wrapper>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="sticky-top" style="top: 4.5rem; z-index: 1;">
                        <div class="card border-0 shadow-sm rounded-4 bg-light bg-gradient">
                            <div class="card-body p-4">
                                <h6 class="fw-bold text-dark mb-4 text-uppercase ls-1 border-bottom pb-2">
                                    Ringkasan Biaya
                                </h6>

                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted small fw-bold">Subtotal Barang</span>
                                    <span class="text-dark fw-bold">{{ formatCurrency(subTotal) }}</span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-muted small fw-bold">PPN ({{ form.tax_percentage || 0 }}%)</span>
                                    <span class="text-danger fw-bold">+ {{ formatCurrency(taxAmountPreview) }}</span>
                                </div>

                                <div class="bg-white p-3 rounded-3 shadow-sm border mb-4">
                                    <div class="text-center">
                                        <small class="text-uppercase text-muted fw-bold ls-1"
                                            style="font-size: 0.7rem;">Total Tagihan Akhir</small>
                                        <h2 class="text-primary fw-bolder mb-0 mt-1">
                                            {{ formatCurrency(grandTotal) }}
                                        </h2>
                                    </div>
                                </div>

                                <div v-if="form.payment_type" class="alert alert-light border shadow-sm mb-4">
                                    <div v-if="form.payment_type === 'payment'">
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span>Status:</span>
                                            <span class="badge bg-warning text-dark">DP/ Cicilan</span>
                                        </div>
                                        <div class="d-flex justify-content-between small fw-bold">
                                            <span>Bayar Sekarang (DP):</span>
                                            <span class="text-dark">{{ formatCurrency(form.amount || 0) }}</span>
                                        </div>
                                        <div
                                            class="d-flex justify-content-between small mt-1 text-muted border-top pt-1">
                                            <span>Sisa Pelunasan:</span>
                                            <span>{{ formatCurrency(grandTotal - (form.amount || 0)) }}</span>
                                        </div>
                                    </div>
                                    <div v-else-if="form.payment_type === 'repayment'">
                                        <div class="d-flex justify-content-between small">
                                            <span>Status:</span>
                                            <span class="badge bg-success">Lunas</span>
                                        </div>
                                        <div class="mt-1 text-center small text-success fw-bold">
                                            Pembayaran Penuh
                                        </div>
                                    </div>
                                </div>

                                <div class="d-xl-flex d-grid align-items-center justify-content-xl-end gap-2">
                                    <button @click.prevent="goBack" type="button"
                                        class="btn btn-link text-decoration-none text-muted btn-sm order-last order-xl-0">
                                        Batal & Kembali
                                    </button>
                                    <base-button :loading="form.processing" waiting="Memproses..."
                                        class="rounded-3 btn-height-1 px-3 fw-bold shadow-sm"
                                        :class="props.transaction?.transaction_id ? 'btn-success' : 'btn-primary'"
                                        type="button" @click="isSubmit"
                                        :label="props.transaction?.transaction_id ? 'Simpan Perubahan' : 'Proses Transaksi'" />

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </app-layout>

</template>
