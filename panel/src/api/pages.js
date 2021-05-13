export default (api) => {
  return {
    async blueprint(parent) {
      return api.get("pages/" + this.id(parent) + "/blueprint");
    },
    async blueprints(parent, section) {
      return api.get("pages/" + this.id(parent) + "/blueprints", {
        section: section
      });
    },
    breadcrumb(page, self = true) {
      var breadcrumb = page.parents.map(parent => ({
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
      return api.patch("pages/" + this.id(id) + "/slug", { slug: slug });
    },
    async changeStatus(id, status, position) {
      return api.patch("pages/" + this.id(id) + "/status", {
        status: status,
        position: position
      });
    },
    async changeTemplate(id, template) {
      return api.patch("pages/" + this.id(id) + "/template", {
        template: template
      });
    },
    async changeTitle(id, title) {
      return api.patch("pages/" + this.id(id) + "/title", { title: title });
    },
    async children(id, query) {
      return api.post("pages/" + this.id(id) + "/children/search", query);
    },
    async create(parent, data) {
      if (parent === null || parent === "/") {
        return api.post("site/children", data);
      }

      return api.post("pages/" + this.id(parent) + "/children", data);
    },
    async delete(id, data) {
      return api.delete("pages/" + this.id(id), data);
    },
    async duplicate(id, slug, options) {
      return api.post("pages/" + this.id(id) + "/duplicate", {
        slug:     slug,
        children: options.children || false,
        files:    options.files    || false,
      });
    },
    async get(id, query) {
      let page = await api.get("pages/" + this.id(id), query);

      if (Array.isArray(page.content) === true) {
        page.content = {};
      }

      return page;
    },
    id(id) {
      return id.replace(/\//g, "+");
    },
    async files(id, query) {
      return api.post("pages/" + this.id(id) + "/files/search", query);
    },
    link(id) {
      return "/" + this.url(id);
    },
    async options(id, view = "view", sortable = true) {
      const page    = await api.get(this.url(id), {select: "options"})
      const options = page.options;
      let result    = [];

      if (view === "list") {
        result.push({
          click: "preview",
          icon: "open",
          text: window.panel.$t("open"),
          disabled: options.preview === false
        });

        result.push("-");

      }

      result.push({
        click: "rename",
        icon: "title",
        text: window.panel.$t("rename"),
        disabled: !options.changeTitle
      });

      result.push({
        click: "duplicate",
        icon: "copy",
        text: window.panel.$t("duplicate"),
        disabled: !options.duplicate
      });

      result.push("-");

      result.push({
        click: "url",
        icon: "url",
        text: window.panel.$t("page.changeSlug"),
        disabled: !options.changeSlug
      });

      result.push({
        click: "status",
        icon: "preview",
        text: window.panel.$t("page.changeStatus"),
        disabled: !options.changeStatus
      });

      if (view === "list") {
        result.push({
          click: "sort",
          icon: "sort",
          text: window.panel.$t("page.sort"),
          disabled: !(options.sort  && sortable)
        });
      }

      result.push({
        click: "template",
        icon: "template",
        text: window.panel.$t("page.changeTemplate"),
        disabled: !options.changeTemplate
      });

      result.push("-");

      result.push({
        click: "remove",
        icon: "trash",
        text: window.panel.$t("delete"),
        disabled: !options.delete
      });

      return result;
    },
    async preview(id) {
      const page = await this.get(this.id(id), { select: "previewUrl" });
      return page.previewUrl;
    },
    async search(parent, query) {
      if (parent) {
        return api.post('pages/' + this.id(parent) + '/children/search?select=id,title,hasChildren', query);
      }

      return api.post('site/children/search?select=id,title,hasChildren', query);
    },
    async update(id, data) {
      return api.patch("pages/" + this.id(id), data);
    },
    url(id, path) {
      let url = id === null ? "pages" : "pages/" + id.replace(/\//g, "+");

      if (path) {
        url += "/" + path;
      }

      return url;
    },
  };
};
