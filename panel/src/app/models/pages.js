
export default (Vue, store) => ({
  breadcrumb(page, self = true) {
    let breadcrumb = page.parents.map(parent => ({
      label: parent.title,
      link: this.link(parent.id)
    }));

    if (self === true) {
      breadcrumb.push({
        label: page.title,
        link: this.link(page.id),
      });
    }

    return breadcrumb;
  },
  async changeSlug(id, slug) {
    const page = await Vue.$api.pages.changeSlug(id, slug);

    // move in content store
    await store.dispatch("content/move", {
      from: "pages/" + id,
      to: "pages/" + page.id
    });

    Vue.$events.$emit("page.changeSlug", page);
    store.dispatch("notification/success");
    return page;
  },
  async changeStatus(id, status, position) {
    const page = await Vue.$api.pages.changeStatus(id, status, position);
    Vue.$events.$emit("page.changeStatus", page);
    store.dispatch("notification/success");
    return page;
  },
  async changeTemplate(id, template) {
    const page = await Vue.$api.pages.changeTemplate(id, template);
    Vue.$events.$emit("page.changeTemplate", page);
    store.dispatch("notification/success");
    return page;
  },
  async changeTitle(id, title) {
    const page = await Vue.$api.pages.changeTitle(id, title);
    Vue.$events.$emit("page.changeTitle", page);
    store.dispatch("notification/success");
    return page;
  },
  async create(parent, props) {
    const page = await Vue.$api.pages.create(parent, props);
    Vue.$events.$emit("page.create", page);
    store.dispatch("notification/success");
    return page;
  },
  async delete(id, props) {
    // send API request to delete page
    await Vue.$api.pages.delete(id, props);

    // remove data from content store
    await store.dispatch("content/remove", "pages/" + id);

    Vue.$events.$emit("page.delete", id);
    store.dispatch("notification/success");
  },
  dropdown(options = {}, view = "view") {
    let dropdown = [];

    if (view === "list") {
      dropdown.push({
        click: "preview",
        icon: "open",
        text: Vue.$t("open"),
        disabled: options.preview === false,
      });

      dropdown.push("-");
    }

    dropdown.push({
      click: "rename",
      icon: "title",
      text: Vue.$t("rename"),
      disabled: !options.changeTitle,
    });

    dropdown.push({
      click: "duplicate",
      icon: "copy",
      text: Vue.$t("duplicate"),
      disabled: !options.duplicate,
    });

    dropdown.push("-");

    dropdown.push({
      click: "slug",
      icon: "url",
      text: Vue.$t("page.changeSlug"),
      disabled: !options.changeSlug,
    });

    dropdown.push({
      click: "status",
      icon: "preview",
      text: Vue.$t("page.changeStatus"),
      disabled: !options.changeStatus,
    });

    dropdown.push({
      click: "template",
      icon: "template",
      text: Vue.$t("page.changeTemplate"),
      disabled: !options.changeTemplate,
    });

    dropdown.push("-");

    dropdown.push({
      click: "remove",
      icon: "trash",
      text: Vue.$t("delete"),
      disabled: !options.delete,
    });

    return dropdown;
  },
  async duplicate(id, slug, props) {
    const page = await Vue.$api.pages.duplicate(id, slug, props);
    Vue.$events.$emit("page.create", page);
    Vue.$events.$emit("page.duplicate", page);
    store.dispatch("notification/success");
    return page;
  },
  id(id) {
    return id.replace(/\//g, "+");
  },
  link(id) {
    return "/" + this.url(id);
  },
  async options(id, view = "view") {
    const url  = this.url(id);
    const page = await Vue.$api.get(url, { select: "options" });
    return this.dropdown(page.options, view);
  },
  statusIcon(status) {
    return this.statusIcons()[status];
  },
  statusIcons() {
    return {
      draft: {
        type: "circle-outline",
        color: "red-light",
      },
      unlisted: {
        type: "circle-half",
        color: "blue-light",
      },
      listed: {
        type: "circle",
        color: "green-light",
      }
    };
  },
  storeId(id) {
    return store.getters["content/id"](this.url(id));
  },
  async update(id) {
    // get values
    const storeId = this.storeId(id);
    const data = store.getters["content/values"](storeId);

    // send updates to API and store
    const page = await Vue.$api.pages.update(id, data);
    store.dispatch("content/update", { id: storeId, values: data });

    // emit events
    Vue.$events.$emit("page.update", data);
    store.dispatch("notification/success");
    return page;
  },
  url(id, path) {
    let url = id === null ? "pages" : "pages/" + this.id(id);

    if (path) {
      url += "/" + path;
    }
    return url;
  }
});
