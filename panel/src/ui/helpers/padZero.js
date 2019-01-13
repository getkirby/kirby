export default (value, length) => {
  value = String(value);
  let string = "";

  length = (length || 2) - value.length;

  while (string.length < length) {
    string += "0";
  }

  return string + value;
};
