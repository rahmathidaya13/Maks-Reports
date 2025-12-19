function escapeRegExp(string) {
    return string.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
}

export function highlight(text, keyword) {
    if (!keyword || !text) return text;

    const escapedKeyword = escapeRegExp(keyword);
    const regex = new RegExp(`(${escapedKeyword})`, "gi");

    return text.toString().replace(regex, `<mark class="highlight">$1</mark>`);
}
