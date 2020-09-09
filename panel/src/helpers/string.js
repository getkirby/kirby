export default {
  camelToKebab(string) {
    return string.replace(/([a-z0-9])([A-Z])/g, "$1-$2").toLowerCase();
  },
  lcfirst(string) {
    const str = String(string);
    return str.charAt(0).toLowerCase() + str.substr(1);
  },
  random(length) {
    let result = '';
    const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    const charactersLength = characters.length;
    for (var i = 0; i < length; i++) {
      result += characters.charAt(Math.floor(Math.random() * charactersLength));
    }
    return result;
  },
  ucfirst(string) {
    const str = String(string);
    return str.charAt(0).toUpperCase() + str.substr(1);
  }
};
