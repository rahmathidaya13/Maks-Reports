export function cleanTextFormat(str) {
    if (!str) return "";

    // 1. Hapus semua titik
    const noDots = str.replaceAll(".", " "); // Hasil: "Dailyreportmodelview"

    // 2. Sisipkan spasi sebelum setiap huruf kapital (kecuali yang pertama)
    // Regex: /(?=[A-Z])/
    const spacedString = noDots.replace(/([A-Z])/g, " $1").trim();
    // Hasil: "Daily Report Model View"

    // 3. (Opsional, untuk keamanan) Kapitalisasi setiap kata.
    const words = spacedString.toLowerCase().split(" ");
    const result = words
        .map((word) => {
            if (!word) return "";
            return word.charAt(0).toUpperCase() + word.slice(1);
        })
        .join(" ");

    return result;
}
