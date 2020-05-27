
export default (Vue, store) => ({
  async changeTitle(title) {
    const site = await Vue.$api.site.changeTitle(title);
    store.dispatch("system/title", title);
    this.onUpdate("changeTitle", site);
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
  onUpdate(event, data) {
    Vue.$events.$emit("file." + event, data);
    store.dispatch("notification/success");
  },
  async options() {
    const site = await Vue.$api.get("site", { select: "options" });
    return this.dropdown(site.options);
  },
});
