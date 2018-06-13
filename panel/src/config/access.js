import store from "./store.js";

export default {
  install(Vue) {
    Vue.prototype.$access = {
      user() {
        return store.state.user.current;
      },
      toLogin() {
        return this.user().permissions.access;
      },
      to() {}
    };
  }
};
