export default {
  camelToKebab(string) {
    return string.replace(/([a-z0-9])([A-Z])/g, "$1-$2").toLowerCase();
  },
  lcfirst(string) {
    const str = String(string);
    return str.charAt(0).toLowerCase() + str.substr(1);
  },
  ucfirst(string) {
    const str = String(string);
    return str.charAt(0).toUpperCase() + str.substr(1);
  }
};
