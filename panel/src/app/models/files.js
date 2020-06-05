export default (Vue, store) => ({
  breadcrumb(file, parentType) {
    let parent = null;
    let breadcrumb = [];

    switch (parentType) {
      case "users":
        breadcrumb.push({
          label: file.parent.username,
          link: Vue.$model.users.link(file.parent.id),
        });
        parent = Vue.model.users.url(file.parent.id);
        break;
      case "site":
        parent = "site";
        break;
      case "pages":
        breadcrumb = file.parents.map((parent) => ({
          label: parent.title,
          link: Vue.$model.pages.link(parent.id),
        }));
        parent = Vue.$model.pages.url(file.parent.id);
        break;
    }

    breadcrumb.push({
      label: file.filename,
      link: this.link(parent, file.filename),
    });

    return breadcrumb;
  },
  async changeName(parent, filename, name) {
    const file = await Vue.$api.files.changeName(parent, filename, name);

    // move in content store
    await store.dispatch("content/move", [
      "files/" + this.id(parent, filename),
      "files/" + this.id(parent, file.filename),
    ]);

    Vue.$events.$emit("file.changeName", file);
    store.dispatch("notification/success");
    return file;
  },
  async delete(parent, filename) {
    const id = this.id(parent, filename);

    // send API request to delete file
    await Vue.$api.files.delete(parent, filename);

    // remove data from content store
    await store.dispatch("content/remove", "files/" + id);

    Vue.$events.$emit("file.delete", id);
    store.dispatch("notification/success");
  },
  dropdown(options = {}, view = "view") {
    let dropdown = [];

    if (view === "list") {
      dropdown.push({
        icon: "open",
        text: Vue.$t("open"),
        click: "download",
      });
    }

    dropdown.push({
      icon: "title",
      text: Vue.$t("rename"),
      click: "rename",
      disabled: !options.changeName,
    });

    dropdown.push({
      icon: "upload",
      text: Vue.$t("replace"),
      click: "replace",
      disabled: !options.replace,
    });

    dropdown.push({
      icon: "trash",
      text: Vue.$t("delete"),
      click: "remove",
      disabled: !options.delete,
    });

    return dropdown;
  },
  id(parent, filename) {
    return parent + "/" + filename;
  },
  link(parent, filename, path) {
    return "/" + this.url(parent, filename, path);
  },
  async options(parent, filename, view = "view") {
    const url = this.url(parent, filename);
    const file = await Vue.$api.get(url, { select: "options" });
    return this.dropdown(file.options, view);
  },
  storeId(parent, filename) {
    return store.getters["content/id"](this.url(parent, filename));
  },
  url(parent, filename, path) {
    let url = parent + "/files/" + filename;

    if (path) {
      url += "/" + path;
    }

    return url;
  },
  async update(parent, filename) {
    // get values
    const storeId = this.storeId(parent, filename);
    const data = store.getters["content/values"](storeId);

    // send updates to API and store
    const file = await Vue.$api.files.update(parent, filename, data);
    store.dispatch("content/update", { id: storeId, values: data });

    // emit events
    Vue.$events.$emit("file.update", data);
    store.dispatch("notification/success");
    return file;
  }
});
