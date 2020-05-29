
export default (Vue, store) => ({
  async create(values) {
    const language = await Vue.$api.languages.create(values);
    await this.load();
    Vue.$events.$emit("language.create", language);
    store.dispatch("notification/success");
    return language;
  },
  defaultLanguageCode() {
    if (store.state.languages && store.state.languages.default) {
      return store.state.languages.default.code;
    }

    return "en";
  },
  async delete(code) {
    // send API request to delete page
    await Vue.$api.languages.delete(code);
    await this.load();
    Vue.$events.$emit("language.delete", code);
    store.dispatch("notification/success");
  },
  async load() {
    const response = await Vue.$api.get("languages");
    store.dispatch("languages/install", response.data);
  },
  async update(code, values) {
    const language = await Vue.$api.languages.update(code, {
      name: values.name,
      direction: values.direction,
      locale: values.locale,
    });
    await this.load();
    Vue.$events.$emit("language.update", language);
    store.dispatch("notification/success");
  },
});
