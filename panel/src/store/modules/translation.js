
/**
 * @todo Remove in 3.7.0
 */
export default {
  namespaced: true,
  getters: {
    string: state => key => {
      window.panel.deprecated("The $store.translation module has been deprecated and removed. Use this.$translation instead.");
      return window.panel.$translation.data[key] || key;
    }
  },
  actions: {
    async activate() {
      window.panel.deprecated("The $store.translation module has been deprecated and removed. Use this.$translation instead.");
    },
    install() {
      window.panel.deprecated("The $store.translation module has been deprecated and removed. Use this.$translation instead.");
    },
    load() {
      window.panel.deprecated("The $store.translation module has been deprecated and removed. Use this.$translation instead.");
      window.panel.$reload("$translation")
    }
  }
}; 