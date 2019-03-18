export default ratio => {
  ratio = ratio || "3/2";
  const parts = ratio.split("/");

  if (parts.length !== 2) {
    return "100%";
  }

  const a = Number(parts[0]);
  const b = Number(parts[1]);
  const padding = 100 / a * b;
  return padding + '%';
};
