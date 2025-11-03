export function highlight(text, keyword) {
  if (!keyword || !text) return text;
  const regex = new RegExp(`(${keyword})`, "gi");
  return text.toString().replace(regex, `<mark class="highlight">$1</mark>`);
}
