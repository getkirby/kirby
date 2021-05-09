
import str from "../helpers/string.js";

/**
 * TranslationString prop type
 */
 export class TranslationString extends String {
  toString() {
    const app   = window.panel.app;
    const value = this.valueOf();
    return app ? app.$t(value) : value;
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