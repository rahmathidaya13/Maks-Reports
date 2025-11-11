export function formatTextFromSlug(text) {
    if (!text || typeof text !== 'string') {
        return text; // Kembalikan apa adanya jika bukan string valid
    }

    // 1. Ganti semua hyphen (-) dengan spasi
    const withSpaces = text.replace(/-/g, ' ');


    // 2. Ubah setiap kata agar huruf pertamanya kapital (Title Case)
    const titleCase = withSpaces.toLowerCase().split(' ').map(word => {
        // Pastikan kata tidak kosong sebelum mengkapitalkan huruf pertama
        if (word.length > 0) {
            return word.charAt(0).toUpperCase() + word.slice(1);
        }
        return ''; // Mengatasi kasus spasi ganda (walaupun 'replace' di atas harusnya mencegah ini)
    }).join(' ');

    return titleCase;
}
