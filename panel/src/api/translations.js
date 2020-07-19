export default (api) => {
  return {
    async list() {
      return api.get("translations");
    },
    async get(locale) {
      return api.get("translations/" + locale);
    },
    async options() {
      const translations = await this.list();
      const options = translations.data.map(translation => ({
        value: translation.id,
        text: translation.name
      }));

      return options;
    }
  }
};
