import api from "./api.js";

export default {
  list() {
    return api.get("translations");
  },
  get(locale) {
    return api.get("translations/" + locale);
  },
  options() {
    let options = [];

    return this.list()
      .then(translations => {
        options = translations.data.map(translation => ({
          value: translation.id,
          text: translation.name
        }));

        return options;
      });
  }
};
