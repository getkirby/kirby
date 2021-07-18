import { template } from "../helpers/string.js";

export default {
  install(app) {
    app.$t = app.prototype.$t = window.panel.$t = (key, data, fallback = null) => {
      if (typeof key !== "string") {
        return;
      }

      const string = window.panel.$translation.data[key] || fallback;

      if (typeof string !== "string") {
        return string;
      }

      return template(string, data);
    };
  }
};
