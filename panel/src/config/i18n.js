import { template } from "../helpers/string.js";

export class TranslationString extends String {
  toString() {
    const Vue = window.panel.$vue;
    const value = this.valueOf();
    return Vue ? Vue.$t(value) : value;
  }
}

export default {
  install(app) {
    app.$t = app.prototype.$t = window.panel.$t = (key, data) => {
      if (typeof key !== "string") {
        return;
      }

      const string = window.panel.$translation.data[key] || key;
      return template(string, data);
    };
  }
};
