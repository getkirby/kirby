import I18n from "vuex-i18n";

export default {
  install(Vue, store) {
    Vue.use(I18n.plugin, store);
    Vue.$t = Vue.prototype.$t;
  }
};
