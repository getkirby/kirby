
export default (Vue, store) => ({
  async create(values) {
    const language = await Vue.$api.languages.create(values);
    await store.dispatch("languages/load");
    this.onUpdate("create", language);
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
});
