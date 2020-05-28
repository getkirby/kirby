
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
    await store.dispatch("content/move", [
      "pages/" + id,
      "pages/" + page.id
    ]);

    this.onUpdate("changeSlug", page);
    return page;
  },
  async changeStatus(id, status, position) {
    const page = await Vue.$api.pages.changeStatus(id, status, position);
    this.onUpdate("changeStatus", page);
    return page;
  },
  async changeTemplate(id, template) {
    const page = await Vue.$api.pages.changeTemplate(id, template);
    this.onUpdate("changeTemplate", page);
    return page;
  },
  async changeTitle(id, title) {
    const page = await Vue.$api.pages.changeTitle(id, title);
    this.onUpdate("changeTitle", page);
    return page;
  },
  async create(parent, props) {
    const page = await Vue.$api.pages.create(parent, props);
    this.onUpdate("create", page);
    return page;
  },
  async delete(id, props) {
    // send API request to delete page
    await Vue.$api.pages.delete(id, props);

    // remove data from content store
    await store.dispatch("content/remove", "pages/" + id);

    this.onUpdate("delete", id);
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
    this.onUpdate(["create", "duplicate"], page);
    return page;
  },
  link(id) {
    return "/" + this.url(id);
  },
  onUpdate(event, data) {
    if (Array.isArray(event)) {
      event.forEach(e => {
        this.onUpdate(e, data);
      });
    }

    Vue.$events.$emit("page." + event, data);
    store.dispatch("notification/success");
  },
  async options(id, view = "view") {
    const url  = this.url(id);
    const page = await Vue.$api.get(url, { select: "options" });
    return this.dropdown(page.options, view);
  },
  async update(id, data) {
    const page = await Vue.$api.pages.update(id, data);
    this.onUpdate("update", page);
    return page;
  },
  url(id, path) {
    let url = id === null ? "pages" : "pages/" + id.replace(/\//g, "+");

    if (path) {
      url += "/" + path;
    }
    return url;
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
  }
});
