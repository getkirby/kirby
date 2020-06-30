export default (ratio = "3/2") => {
  const parts = String(ratio).split("/");

  if (parts.length !== 2) {
    return "100%";
  }

  const a = Number(parts[0]);
  const b = Number(parts[1]);
  let padding = 100;

  if (a !== 0 && b !== 0) {
    padding = 100 / a * b;
  }

  return padding + '%';
};
