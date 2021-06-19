export default function (string) {
  if (typeof string !== "string") {
    return;
  }

  if (string === "pattern") {
    return `var(--color-gray-800) var(--bg-pattern)`;
  }

  if (
    string.match(
      /^(black|white|light|gray|red|orange|yellow|green|aqua|blue|purple})/i
    ) !== null
  ) {
    return `var(--color-${string})`;
  }

  return string;
}
