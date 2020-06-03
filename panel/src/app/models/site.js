
export default (Vue, store) => ({
  async changeTitle(title) {
    const site = await Vue.$api.site.changeTitle(title);
    store.dispatch("system/title", title);
    Vue.$events.$emit("site.changeTitle", data);
    store.dispatch("notification/success");
    return site;
  },
  dropdown(options = {}) {
    return [{
      click: "rename",
      icon: "title",
      text: Vue.$t("rename"),
      disabled: !options.changeTitle,
    }];
  },
  async options() {
    const site = await Vue.$api.get("site", { select: "options" });
    return this.dropdown(site.options);
  },
  storeId() {
    return store.getters["content/id"]("site");
  },
  async update(id) {
    // get values
    const storeId = this.storeId(id);
    const data = store.getters["content/values"](storeId);

    // send updates to API and store
    const site = await Vue.$api.site.update(data);
    store.dispatch("content/update", { id: storeId, values: data });

    // emit events
    Vue.$events.$emit("site.update", data);
    store.dispatch("notification/success");
    return site;
  },
});
