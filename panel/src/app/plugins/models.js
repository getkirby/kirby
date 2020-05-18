export default {
  install(Vue, store) {
    Vue.prototype.$model = {};

    /** Auto-load models */
    const req = require.context('@/app/models/', true, /\.js$/i);
    req.keys().map(key => {
      let name = key.match(/\w+/)[0];
      Vue.prototype.$model[name] = req(key).default(Vue, store);
    });
  }
};
