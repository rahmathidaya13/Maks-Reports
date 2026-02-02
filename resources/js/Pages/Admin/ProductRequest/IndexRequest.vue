<script setup>
import { computed, reactive, ref, watch } from "vue";
import { Head, Link, router, usePage, useForm } from "@inertiajs/vue3";
import { debounce } from "lodash";
import { highlight } from "@/helpers/highlight";
import { formatText } from "@/helpers/formatText";
import { hasRole, hasPermission } from "@/composables/useAuth";
import { useConfirm } from "@/helpers/useConfirm.js"
const confirm = useConfirm(); // Memanggil fungsi confirm untuk alert delete

import moment from "moment";
import FormModalRequest from "./FormModalRequest.vue";
moment.locale('id');

const props = defineProps({
    products: Object,
    filters: Object,
});
// console.log(props.products.data);

// === UTILS ===
function formatCurrency(value) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
}

function formatDate(dateString) {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
    });
}
// Hitung persentase selisih harga
const getPercentage = (current, requested) => {
    if (current == 0) return 100;
    const diff = requested - current;
    const percent = (diff / current) * 100;
    return Math.round(percent);
};

const selectedReq = ref(null); // Data request yang sedang diklik
const showModal = ref(false);

const openModal = (req) => {
    selectedReq.value = req;
    // form.status = 'approved'; // Default pilih setuju
    // form.admin_note = ''; // Reset note
    showModal.value = true; // Trigger modal (bisa pakai bootstrap JS manual atau v-if)
};
// === LOGIC MODAL EKSEKUSI ===

// const filters = reactive({
//     keyword: props.filters.keyword ?? '',
//     limit: props.filters.limit ?? 10,
//     order_by: props.filters.order_by ?? "desc",
//     page: props.filters?.page ?? 1,
// })

const isLoading = ref(false)
// const liveSearch = debounce((e) => {
//     isLoading.value = true
//     router.get(route("products"), filters, {
//         preserveScroll: true,
//         replace: true,
//         preserveState: true,
//         only: ["products", "filters"],
//         onFinish: () => isLoading.value = false
//     });
// }, 1000);


const header = [
    {
        label: "No",
        key: "__index",
        attrs: {
            class: "text-center",
            style: "width:50px"
        }
    },
    {
        label: "Tanggal / User",
        key: "user",
        attrs: {
            class: "text-center",
        }
    },
    {
        label: "Produk",
        key: "products",
        attrs: {
            class: "text-center",
        }
    },
    {
        label: "Harga Saat Ini",
        key: "current_price",
        attrs: {
            class: "text-start",
        }
    },
    {
        label: "Harga Pengajuan",
        key: "requested_price",
        attrs: {
            class: "text-start",
        }
    },
    {
        label: "Alasan",
        key: "reason",
        attrs: {
            class: "text-center",
            style: "width:25%"
        }
    },
    {
        label: "Status",
        key: "status",
        attrs: {
            class: "text-center",
        }
    },

    {
        label: "Aksi",
        key: "-",
        visible: hasRole(["admin", "developer"]),
        attrs: {
            class: "text-center"
        }
    },
];
// watch(
//     () => [
//         filters.keyword,
//         filters.limit,
//         filters.order_by,],
//     () => {
//         liveSearch();
//     }
// );


// CRUD OPERATION
// const loaderActive = ref(null)
// const create = () => {
//     loaderActive.value?.show("Memproses...");
//     router.get(route("products.create"), {}, {
//         onFinish: () => {
//             loaderActive.value?.hide()
//         }
//     });
// }

// const edit = (id) => {
//     loaderActive.value?.show("Sedang memuat data...");
//     router.get(route("products.edit", id), {}, {
//         onFinish: () => loaderActive.value?.hide()
//     });
// }
// const deleted = async (nameRoute, data) => {
//     const setConfirm = await confirm.ask({
//         title: 'Hapus',
//         message: `Kamu ingin menghapus Cabang ${formatText(data.name)} ?`,
//         confirmText: 'Ya, Hapus',
//         variant: 'danger' // Memberikan warna merah pada tombol konfirmasi
//     });

//     if (setConfirm) {
//         loaderActive.value?.show("Sedang menghapus data...");
//         router.delete(route(nameRoute, data.productses_id), {
//             onFinish: () => loaderActive.value?.hide(),
//             preserveScroll: false,
//             replace: true
//         });
//     }
// }
// end CRUD OPERATION

// MULTIPLE DELETE
// const selectedRow = ref([]);
// const deleteSelected = async () => {
//     // 1. Kondisi Tidak Ada Data (Berfungsi sebagai Alert)
//     if (!selectedRow.value.length) {
//         return await confirm.ask({
//             title: 'Perhatian',
//             message: 'Silakan pilih minimal satu data untuk dihapus.',
//             cancelText: 'Mengerti', // Ubah teks tombol tutup
//             showButtonConfirm: false,
//             variant: 'warning' // Gunakan warna kuning/orange untuk warning
//         });
//     }

//     // 2. Kondisi Konfirmasi Hapus
//     const setConfirm = await confirm.ask({
//         title: 'Konfirmasi Hapus',
//         message: `Apakah Anda yakin ingin menghapus ${selectedRow.value.length} data terpilih?`,
//         confirmText: 'Ya, Hapus',
//         cancelText: 'Batal',
//         variant: 'danger'
//     });

//     // 3. Eksekusi
//     if (setConfirm) {
//         loaderActive.value?.show("Sedang menghapus data...");
//         router.post(route('products.destroy_all'), {
//             all_id: selectedRow.value
//         }, {
//             onFinish: () => {
//                 loaderActive.value?.hide();
//                 selectedRow.value = []; // Bersihkan pilihan setelah sukses
//             },
//             preserveScroll: true,
//             preserveState: false,
//         });
//     }
// }
// end MULTIPLE DELETE





// const filterFields = computed(() => [
//     {
//         key: 'keyword',
//         label: 'Pencarian',
//         type: 'search',
//         col: 'col-xl-8 col-12',
//         autofocus: true,
//         icon: 'fas fa-search',
//         props: {
//             placeholder: 'Masukan Pencarian...',
//             inputClass: 'border-start-0 ps-2 shadow-none',
//             isValid: false,
//         }
//     },
//     {
//         key: 'limit',
//         label: 'Batas',
//         type: 'select',
//         col: 'col-xl-2 col-md-6 col-6',
//         icon: 'fas fa-list-ul',
//         props: {
//             selectClass: 'border-start-0 ps-2 shadow-none',
//             isValid: false,
//         },
//         options: [
//             { value: null, label: 'Pilih Batas Data' },
//             { value: 10, label: '10 Baris' },
//             { value: 20, label: '20 Baris' },
//             { value: 30, label: '30 Baris' },
//             { value: 50, label: '50 Baris' },
//             { value: 100, label: '100 Baris' },
//         ]
//     },
//     {
//         key: 'order_by',
//         label: 'Urutan',
//         type: 'select',
//         col: 'col-xl-2 col-md-6 col-6',
//         icon: 'fas fa-sort',
//         props: {
//             selectClass: 'border-start-0 ps-2 shadow-none',
//             isValid: false,
//         },
//         options: [
//             { value: null, label: 'Pilih Urutan' },
//             { value: 'desc', label: 'Terbaru' },
//             { value: 'asc', label: 'Terlama' },
//         ]
//     },
// ]);
// const reset = () => {
//     isLoading.value = true
//     router.get(route("products.reset"), {}, {
//         preserveScroll: false,
//         replace: true,
//         onFinish: () => isLoading.value = false
//     });
// }
const reset = () => {
    isLoading.value = true
    router.get(route('admin.request.reset'), {}, {
        preserveScroll: true,
        onSuccess: () => {
            router.reload({ only: ['products'] });
        },
        onFinish: () => {
            // Selesai apapun hasilnya → loader hilang
            isLoading.value = false
        }
    });
}
const toolbarActions = computed(() => [
    {
        label: 'Segarkan',
        icon: 'fas fa-redo-alt',
        iconColor: 'text-primary',
        isPrimary: true,
        loading: isLoading.value,
        click: reset
    }
]);
</script>
<template>

    <Head title="Halaman Permintaan" />
    <app-layout>
        <template #content>
            <loader-page ref="loaderActive" />
            <bread-crumbs :home="false" icon="fas fa-clipboard-check" title="Daftar Permintaan"
                :items="[{ text: 'Daftar Permintaan' }]" />
            <callout />

            <div class="row pb-3">

                <div class="col-xl-12 col-12">
                    <div class="card card-modern border-0 shadow-sm rounded-4 overflow-hidden">
                        <div
                            class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">

                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3 me-3">
                                    <i class="fas fa-clipboard-check fs-5"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0 text-dark">Data Permintaan</h5>
                                    <p class="text-muted small mb-0">
                                        informasi Permintaan dari user
                                    </p>
                                </div>
                            </div>

                            <action-toolbar :actions="toolbarActions" />

                        </div>


                        <div class="card-body p-0 position-relative">
                            <base-table :loader="isLoading" loaderText="Sedang memuat data..." :headers="header"
                                :items="products?.data" row-key="product_request_id">

                                <template #empty>
                                    <div class="text-muted opacity-50 mb-2">
                                        <i class="fas fa-clipboard-check display-1"></i>
                                    </div>
                                    <h6 class="fw-bold text-muted">Belum ada permintaan baru</h6>
                                </template>

                                <template #row="{ item, index }">

                                    <td class="ps-3 text-muted fw-semibold">{{ index + 1 + (products?.current_page
                                        - 1) * products?.per_page }}</td>

                                    <td class="text-start ps-3">
                                        <div class="fw-bold text-dark text-sm">{{ item.user?.name ?? 'Unknown' }}</div>
                                        <div class="fw-normal small text-capitalize text-muted text-sm">{{
                                            item.user?.profile.branch.name ?? 'Unknown' }}</div>
                                        <div class="text-xs text-muted small">
                                            {{ formatDate(item.created_at) }}
                                        </div>
                                    </td>

                                    <td class="py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="symbol symbol-35px me-2">
                                                <div class="bg-light rounded-2 d-flex align-items-center justify-content-center"
                                                    style="width:35px; height:35px;">
                                                    <i class="fas fa-box text-secondary"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark text-sm">{{ item.product?.name ?? '-' }}
                                                </div>
                                                <div class="text-xs text-muted">{{ item.product?.category ?? 'Umum' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <span class="text-muted text-sm">{{ formatCurrency(item.current_price) }}</span>
                                    </td>
                                    <td class="py-3">
                                        <div class="d-flex align-items-center">
                                            <span class="fw-bold text-dark me-2">{{ formatCurrency(item.requested_price)
                                            }}</span>

                                            <span class="badge rounded-pill text-xxs"
                                                :class="item.requested_price < item.current_price ? 'bg-danger bg-opacity-10 text-danger' : 'bg-success bg-opacity-10 text-success'">
                                                <i class="fas me-1"
                                                    :class="item.requested_price < item.current_price ? 'fa-arrow-down' : 'fa-arrow-up'"></i>
                                                {{ Math.abs(getPercentage(item.current_price, item.requested_price)) }}%
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <p class="text-sm text-dark mb-0 text-truncate" style="max-width: 200px;"
                                            :title="item.reason">
                                            "{{ item.reason }}"
                                        </p>
                                        <div v-if="item.admin_note" class="mt-1 text-xs text-info fst-italic text-truncate"  style="width: 450px;">
                                            <i class="fas fa-reply me-1"></i> Admin: {{ item.admin_note }}
                                        </div>
                                    </td>
                                    <td class="py-3 text-center">
                                        <span class="badge rounded-pill px-2 py-1" :class="{
                                            'bg-warning text-dark': item.status === 'pending',
                                            'bg-success': item.status === 'approved',
                                            'bg-danger': item.status === 'rejected'
                                        }">
                                            <i class="fas me-1" :class="{
                                                'fa-hourglass-half': item.status === 'pending',
                                                'fa-check-circle': item.status === 'approved',
                                                'fa-times-circle': item.status === 'rejected'
                                            }"></i>
                                            {{ item.status === 'pending' ? 'MENUNGGU' : (item.status === 'approved' ?
                                                'DISETUJUI' : 'DITOLAK') }}
                                        </span>
                                    </td>

                                    <td class="pe-4 py-3 text-end" v-if="hasRole(['admin', 'developer'])">
                                        <button v-if="item.status === 'pending'" @click.prevent="openModal(item)"
                                            class="btn btn-primary btn-sm rounded-3 shadow-sm px-3 fw-bold">
                                            Proses <i class="fas fa-arrow-right ms-1"></i>
                                        </button>
                                        <span v-else class="text-muted text-xs fst-italic">
                                            Selesai
                                        </span>
                                    </td>

                                </template>
                            </base-table>
                        </div>

                        <div
                            class="card-footer bg-white border-0 py-3 px-4 d-flex flex-column flex-md-row justify-content-between align-items-center">
                            <span class="text-muted mb-2 mb-md-0">
                                Menampilkan <strong>{{ props.products?.from ?? 0 }}</strong> - <strong>{{
                                    props.products?.to ?? 0 }}</strong> dari <strong>{{ props.products?.total ?? 0
                                    }}</strong>
                            </span>
                            <pagination size="pagination-sm" :links="props.products?.links"
                                routeName="admin.request.index" :additionalQuery="{
                                    order_by: filters.order_by,
                                    limit: filters.limit,
                                    keyword: filters.keyword,
                                }" />
                        </div>

                    </div>
                </div>
            </div>
            <FormModalRequest :selected="selectedReq" :show="showModal" :products="products?.data"
                @update:show="showModal = $event" />
        </template>
    </app-layout>
</template>
<style scoped>
/* Avatar Circle untuk kolom Creator */
.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
}
</style>
