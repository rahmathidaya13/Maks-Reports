<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { Head, useForm, Link, usePage } from '@inertiajs/vue3';

const props = defineProps({ ticket: Object });
const user = usePage().props.auth.user; // Ambil data user login
console.log(user);
const form = useForm({ message: '' });
const chatContainer = ref(null);

// Auto scroll ke bawah saat load / kirim pesan
const scrollToBottom = async () => {
    await nextTick();
    if (chatContainer.value) {
        chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
    }
}

onMounted(() => { scrollToBottom() });

const textareaRef = ref(null); // Ref untuk akses elemen DOM


// Fungsi agar tinggi textarea menyesuaikan isi teks
const adjustHeight = () => {
    const el = textareaRef.value;
    if (el) {
        el.style.height = 'auto'; // Reset dulu biar bisa mengecil kalau teks dihapus
        el.style.height = el.scrollHeight + 'px'; // Set tinggi sesuai konten
    }
}
const sendMessage = () => {
    if (!form.message.trim()) return;
    form.post(route('helpdesk.reply', props.ticket.ticket_id), {
        onSuccess: () => {
            form.reset();
            if (textareaRef.value) textareaRef.value.style.height = 'auto';
            scrollToBottom();
        }
    });
};
</script>

<template>

    <Head :title="ticket.subject" />
    <app-layout>
        <template #content>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100"
                style="min-height: 80vh; max-height: 85vh;">

                <div class="card-header bg-white border-bottom py-3 d-flex align-items-center">
                    <Link :href="route('helpdesk.index')" class="btn btn-sm btn-light rounded-circle me-3 border">
                        <i class="fas fa-arrow-left"></i>
                    </Link>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">#{{ ticket.creator.name }} - {{ ticket.subject }}</h6>
                        <small class="text-muted">
                            <span class="badge bg-light text-dark border me-1">{{ ticket.category }}</span>
                            {{ ticket.status === 'open' ? 'Menunggu Respon' : 'Selesai' }}
                        </small>
                    </div>
                </div>

                <div class="card-body bg-light overflow-auto p-4" ref="chatContainer">
                    <div v-for="msg in ticket.messages" :key="msg.message_id" class="d-flex mb-3"
                        :class="msg.created_by === user.id ? 'justify-content-end' : 'justify-content-start'">

                        <div class="p-3 shadow-sm" style="max-width: 70%;" :class="msg.created_by === user.id
                            ? 'bg-primary text-white rounded-top-left-3 rounded-bottom-3'
                            : 'bg-white text-dark rounded-top-right-3 rounded-bottom-3'">

                            <div class="small fw-bold mb-1 opacity-75" v-if="msg.created_by !== user.id">
                                Admin Support
                            </div>

                            <div style="white-space: pre-wrap;">{{ msg.message }}</div>

                            <div class="text-end mt-1" style="font-size: 0.7rem; opacity: 0.7;">
                                {{ new Date(msg.created_at).toLocaleTimeString([], {
                                    hour: '2-digit', minute: '2-digit'
                                })
                                }}
                            </div>
                        </div>

                    </div>

                    <div v-if="ticket.messages.length === 0" class="text-center text-muted mt-5">
                        <small>Mulai percakapan dengan admin...</small>
                    </div>
                </div>

                <div class="card-footer bg-white py-3">
                    <form @submit.prevent="sendMessage" class="d-flex gap-2">

                        <textarea ref="textareaRef" v-model="form.message" rows="2"
                            class="form-control bg-light border-0 px-4 py-2 shadow-none"
                            placeholder="Ketik pesan Anda..." :disabled="form.processing" @input="adjustHeight"
                            style="resize: none; overflow-y: hidden; min-height: 45px; max-height: 150px; border-radius: 25px;"
                            @keydown.enter.exact.prevent="sendMessage"></textarea>
                        <input-error :message="form.errors.message" />

                        <button type="submit"
                            class="btn btn-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                            style="width: 45px; height: 45px;" :disabled="form.processing">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>

            </div>

        </template>
    </app-layout>
</template>

<style scoped>
/* CSS Helper untuk bubble chat lengkung */
.rounded-top-left-3 {
    border-top-left-radius: 1rem !important;
    border-top-right-radius: 1rem !important;
    border-bottom-left-radius: 1rem !important;
    border-bottom-right-radius: 0 !important;
}

.rounded-top-right-3 {
    border-top-left-radius: 1rem !important;
    border-top-right-radius: 1rem !important;
    border-bottom-right-radius: 1rem !important;
    border-bottom-left-radius: 0 !important;
}
</style>
