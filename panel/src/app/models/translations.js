
export default function (Vue) {
  return {
    async options() {
      const translations = await Vue.prototype.$api.translations.list();
      return translations.data.map(translation => ({
        value: translation.id,
        text: translation.name
      }));
    }
  };
}
