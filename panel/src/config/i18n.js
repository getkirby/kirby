
import str from "../helpers/string.js";

export default {
  install(app) {
    app.config.globalProperties.$t = (key, data) => {
      const store  = app.config.globalProperties.$store;
      const string = store.getters["translation/string"](key);
      return str.template(string, data);
    }
  }
};