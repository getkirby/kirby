
import str from "../helpers/string.js";

/**
 * TranslationString prop type
 */
export class TranslationString {
  constructor(key) {
    this.key = key;
  }
  toString(app) {
    return app.$t(this.key);
  }
}

/**
 * Vue plugin
 */

export default {
  install(app, store) {
    app.$t = app.prototype.$t = (key, data) => {
      if (typeof key !== 'string') {
        return;
      }

      const string = store.getters["translation/string"](key);
      return str.template(string, data);
    }
  }
};