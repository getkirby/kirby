import { template } from "../helpers/string.js";

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
