export default {
  class(prop, prefix = "text-", fallback = false) {
    if (!prop) {
      return fallback;
    }

    if (this.isHex(prop)) {
      return false;
    }

    return prefix + prop;
  },
  style(prop, prefix = "") {
    if (!prop) {
      return false;
    }

    if (this.isHex(prop)) {
      return prefix + "color: " + prop;
    }
  },
  isHex(prop) {
    return prop.substring(0,1) === "#";
  }
}
