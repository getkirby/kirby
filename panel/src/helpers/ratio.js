/**
 * Returns a percentage string fro the provided fraction
 *
 * @param {String} fraction fraction to convert to a percentage
 * @param {String} fallback default value if fraction cannot be parsed
 * @param {Boolean} vertical Whether the fraction is applied to
 *                           vertical or horizontal orientation
 */
export default (fraction = "3/2", fallback = "100%", vertical = true) => {
  const parts = String(fraction).split("/");

  if (parts.length !== 2) {
    return fallback;
  }

  const a = Number(parts[0]);
  const b = Number(parts[1]);
  let result = 100;

  if (a !== 0 && b !== 0) {
    if (vertical) {
      result = (result / a) * b;
    } else {
      result = (result / b) * a;
    }

    result = parseFloat(String(result)).toFixed(2);
  }

  return result + "%";
};
