
export function ucfirst (string) {
  const str = String(string);
  return str.charAt(0).toUpperCase() + str.substr(1);
}

export function lcfirst (string) {
  const str = String(string);
  return str.charAt(0).toLowerCase() + str.substr(1);
}
