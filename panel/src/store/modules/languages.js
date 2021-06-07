
/**
 * @todo Remove in 3.7.0
 */
export default {
  namespaced: true,
  actions: {
    current() {
      window.panel.deprecated("The $store.languages module has been deprecated and removed. Use this.$language and this.$languages instead.");
    },
    install() {
      window.panel.deprecated("The $store.languages module has been deprecated and removed. Use this.$language and this.$languages instead.");
    },
    async load() {
      window.panel.deprecated("The $store.languages module has been deprecated and removed. To reload languages, use this.$reload(['$language', '$languages']) instead.");
      window.panel.$reload(['$language', '$languages']);
    }
  }
};