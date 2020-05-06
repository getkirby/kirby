export default {
  install(Vue) {

    /** Auto-load components */
    const req = require.context('./components/', true, /\.vue$/i);
    req.keys().map(key => {
      let name = key.match(/\w+/)[0];
          name = "k-" + Vue.prototype.$helper.string.camelToKebab(name);
      return Vue.component(name, req(key).default);
    });

  }
};
