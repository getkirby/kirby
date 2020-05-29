
export default (Vue, store) => ({
  async load(id) {
    return Vue.$api.translations.get(id);
  },
  async options() {
    const translations = await Vue.$api.translations.list();
    return translations.data.map(translation => ({
      value: translation.id,
      text: translation.name
    }));
  }
});
