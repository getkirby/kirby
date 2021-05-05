
import str from "../helpers/string.js";

export default {
  install(Vue, store) {
    Vue.$t = Vue.prototype.$t = (key, data) => {
      const string = store.getters["translation/string"](key);
      return str.template(string, data);
    }
  }
};