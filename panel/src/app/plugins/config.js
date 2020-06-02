export default {
  install(Vue) {

    const defaults = {
      assets: "@/assets",
      api: "/api",
      site: process.env.VUE_APP_DEV_SERVER,
      url: "/",
      debug: true,
      translation: "en",
      search: {
        limit: 10,
      },
    };

    const panel = window.panel || {};

    Vue.prototype.$config = Vue.$config = {
      ...defaults,
      ...panel
    };

  }
};
