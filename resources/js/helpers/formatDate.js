
export function formatDate(dateString) {
    const date = new Date(dateString);
    const options = {
        weekday: "long",
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
    };
    return date.toLocaleDateString("id-ID", options);
}
