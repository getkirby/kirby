/**
 * @todo Remove in 3.7.0
 */
export default {
  namespaced: true,
  actions: {
    current() {
      window.panel.deprecated(
        "The $store.user module has been deprecated and removed. Use this.$user instead."
      );
    },
    email() {
      window.panel.deprecated(
        "The $store.user module has been deprecated and removed. Use this.$user instead."
      );
    },
    language() {
      window.panel.deprecated(
        "The $store.user module has been deprecated and removed. Use this.$user and this.$language instead."
      );
    },
    async load() {
      window.panel.deprecated(
        "The $store.user module has been deprecated and removed. Use this.$reload('$user') instead."
      );
      window.panel.$reload("$user");
    },
    login() {
      window.panel.deprecated(
        "The $store.user module has been deprecated and removed. Use this.$user instead."
      );
    },
    async logout() {
      window.panel.deprecated(
        "The $store.user module has been deprecated and removed. Use this.$go('logout') instead."
      );
      window.panel.$go("logout");
    },
    name() {
      window.panel.deprecated(
        "The $store.user module has been deprecated and removed. Use this.$user instead."
      );
    },
    pending() {
      window.panel.deprecated(
        "The $store.user module has been deprecated and removed. Use this.$user instead."
      );
    },
    visit() {
      window.panel.deprecated(
        "The $store.user module has been deprecated and removed. Use this.$user instead."
      );
    },
  },
};
