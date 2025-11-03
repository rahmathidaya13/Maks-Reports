export function formatText(text, mode = "capitalize") {
  if (!text) return "";
  const str = String(text).trim();
  switch (mode) {
    case "capitalize":
      return str.replace(/\b\w/g, l => l.toUpperCase());

    case "lowercase":
      return str.toLowerCase();

    case "uppercase":
      return str.toUpperCase();

    default:
      return str;
  }
}
