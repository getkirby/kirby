export default {
  install(Vue) {
    Vue.$caniuse = Vue.prototype.$caniuse = {
      grid() {
        if (window.CSS && window.CSS.supports("display", "grid")) {
          return true;
        }
        return false;
      },
      fetch() {
        return window.fetch !== undefined;
      },
      all() {
        return this.fetch() && this.grid();
      }
    };
  }
};
