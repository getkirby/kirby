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
    async options(id, view = "view", overwrite = {}) {
      const pageUrl = this.url(id);
      const page    = await api.get(pageUrl, {select: "options, previewUrl"})
      const options = page.options;
      let result    = [];

      const disabled = function (action) {
        return options[action] === false || overwrite[action] === false;
      };

      if (view === "list") {
        result.push({
          click() {
            window.open(page.previewUrl, "_blank");
          },
          icon: "open",
          text: window.panel.$t("open"),
          disabled: disabled("preview")
        });

        result.push("-");

      }

      result.push({
        click() {
          this.$dialog(pageUrl + "/changeTitle", {
            query: {
              select: "title"
            }
          });
        },
        icon: "title",
        text: window.panel.$t("rename"),
        disabled: disabled("changeTitle")
      });

      result.push({
        click() {
          this.$dialog(pageUrl + "/duplicate");
        },
        icon: "copy",
        text: window.panel.$t("duplicate"),
        disabled: disabled("duplicate")
      });

      result.push("-");

      result.push({
        click() {
          this.$dialog(pageUrl + "/changeTitle", {
            query: {
              select: "slug"
            }
          });
        },
        icon: "url",
        text: window.panel.$t("page.changeSlug"),
        disabled: disabled("changeSlug")
      });

      result.push({
        click() {
          this.$dialog(pageUrl + '/changeStatus');
        },
        icon: "preview",
        text: window.panel.$t("page.changeStatus"),
        disabled: disabled("changeStatus")
      });

      if (view === "list") {
        result.push({
          click() {
            this.$dialog(pageUrl + '/changeSort');
          },
          icon: "sort",
          text: window.panel.$t("page.sort"),
          disabled: disabled("sort")
        });
      }

      result.push({
        click() {
          this.$dialog(pageUrl + '/changeTemplate');
        },
        icon: "template",
        text: window.panel.$t("page.changeTemplate"),
        disabled: disabled("changeTemplate")
      });

      result.push("-");

      result.push({
        click() {
          this.$dialog(pageUrl + '/delete');
        },
        icon: "trash",
        text: window.panel.$t("delete"),
        disabled: disabled("delete")
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
      let url = id === null ? "pages" : "pages/" + String(id).replace(/\//g, "+");

      if (path) {
        url += "/" + path;
      }

      return url;
    },
  };
};
