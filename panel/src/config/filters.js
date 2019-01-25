export default {
  install(Vue) {

    // default translate filter for Ui components
    Vue.filter("t", function (fallback) {
      return fallback;
    });

  }
};
