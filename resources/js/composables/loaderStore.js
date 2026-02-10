import { reactive } from "vue";

export const loaderStore = reactive({
    show: false,
    text: "Memuat halaman...",

    // Method untuk mengaktifkan
    display(message = "Memuat halaman...") {
        this.show = true;
        this.text = message;
    },

    // Method untuk mematikan
    hide() {
        this.show = false;
    },
});
