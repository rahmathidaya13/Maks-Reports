import Swal from "sweetalert2";
/**
 * Tampilkan SweetAlert konfirmasi hapus
 * @param {Object} options
 * @param {string} options.title - Judul alert
 * @param {string} options.text - Isi pesan
 * @param {string} [options.icon='warning'] - Jenis icon (success, error, warning, info)
 * @param {string} [options.confirmText='Ya, Hapus!'] - Teks tombol konfirmasi
 * @param {string} [options.cancelText='Batal'] - Teks tombol batal
 * @param {function} options.onConfirm - Callback jika user menekan konfirmasi
 * @param {function} [options.onCancel] - Callback jika user menekan batal
 */
export function swalConfirmDelete({
    title = "Konfirmasi",
    text = "Apakah Anda yakin?",
    icon = "warning",
    confirmText = "Ya, Hapus!",
    cancelText = "Batal",
    onConfirm,
    onCancel,
}) {
    return Swal.fire({
        title,
        text,
        icon,
        showCancelButton: true,
        confirmButtonText: confirmText,
        cancelButtonText: cancelText,
        customClass: {
            confirmButton: "btn btn-success",
            cancelButton: "btn btn-outline-danger",
        },
    }).then((result) => {
        if (result.isConfirmed && typeof onConfirm === "function") {
            onConfirm();
        } else if (result.isDismissed && typeof onCancel === "function") {
            onCancel();
        }
    });
}

/**
 * Alert sederhana (tanpa konfirmasi)
 * @param {string} title - Judul alert
 * @param {string} text - Isi pesan
 * @param {'success' | 'error' | 'warning' | 'info'} [icon='info']
 */
export function swalAlert(title, text, icon = "info") {
    return Swal.fire(title, text, icon);
}
