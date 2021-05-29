
/**
 * @todo Remove in 3.7.0
 */
export default {
  namespaced: true,
  actions: {
    async load() {
      window.panel.deprecated("The $store.system module has been deprecated and removed. Use this.$reload('$sysytem') instead.");
      window.panel.$reload("$system");
    },
    register() {
      window.panel.deprecated("The $store.system module has been deprecated and removed.");
    },
    title() {
      window.panel.deprecated("The $store.system module has been deprecated and removed.");
    }
  }
};