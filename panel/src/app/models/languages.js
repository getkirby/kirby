
export default (Vue, store) => ({
  async create(values) {
    const language = await Vue.$api.languages.create(values);
    await store.dispatch("languages/load");
    this.onUpdate("create", language);
  },
  onUpdate(event, data) {
    Vue.$events.$emit("language." + event, data);
    store.dispatch("notification/success");
  },
});
