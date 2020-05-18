
export default (Vue, store) => ({
  async options() {
    const translations = await Vue.$api.translations.list();
    return translations.data.map(translation => ({
      value: translation.id,
      text: translation.name
    }));
  }
});
