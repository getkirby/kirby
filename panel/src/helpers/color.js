export default function (string) {
  if (typeof string !== "string") {
    return;
  }

  const isHex = string.substring(0, 1) === "#";

  if (isHex || string.startsWith("var(")) {
    return string;
  }

  if (string === "pattern") {
    return `var(--color-gray-800) var(--bg-pattern)`;
  }

  return `var(--color-${string})`;
}
