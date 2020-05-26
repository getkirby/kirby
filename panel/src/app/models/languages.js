
export default (Vue, store) => ({
  async create(values) {
    const language = await Vue.$api.languages.create(values);
    await store.dispatch("languages/load");
    this.onUpdate("create", language);
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
    await store.dispatch("languages/load");
    this.onUpdate("delete", code);
  },
  onUpdate(event, data) {
    Vue.$events.$emit("language." + event, data);
    store.dispatch("notification/success");
  },
  async update(code, values) {
    const language = await Vue.$api.languages.update(code, {
      name: values.name,
      direction: values.direction,
      locale: values.locale,
    });
    await store.dispatch("languages/load");
    this.onUpdate("update", language);
  },
});
