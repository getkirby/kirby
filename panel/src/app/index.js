import Api from "./plugins/api.js";
import Config from "./plugins/config.js";
import ErrorHandling from "./plugins/errors.js";
import I18n from "vuex-i18n";
import Models from "./plugins/models.js";
import Plugins from "./plugins/plugins.js";
import Router from "./plugins/router.js";
import Store from "./store/store.js";

export default {
  install(Vue) {

    /** Auto-load components */
    const req = require.context('./components/', true, /\.vue$/i);
    req.keys().map(key => {
      let name = key.match(/\w+/)[0];
          name = "k-" + Vue.prototype.$helper.string.camelToKebab(name);
      return Vue.component(name, req(key).default);
    });

    /** Register plugins & store */
    Vue.use(Config);
    Vue.use(I18n.plugin, Store);
    Vue.use(Api, Store);
    Vue.use(ErrorHandling);
    Vue.use(Models, Api, Store);
    Vue.use(Plugins, Store);
  }
};

export const Store = Store;
export const Router = Router(Vue, Store);
