export default function (string) {
  if (typeof string !== "string") {
    return;
  }

  if (string === "pattern") {
    return `var(--color-gray-800) var(--bg-pattern)`;
  }

  const vars = `/^${[
    "black",
    "white",
    "light",
    "gray",
    "red",
    "orange",
    "yellow",
    "green",
    "aqua",
    "blue",
    "purple"
  ].join("|")}/`;

  if (string.match(vars) === null) {
    return string;
  }

  if (string.endsWith("0") === false) {
    string += "-400";
  }

  return `var(--color-${string})`;
}
