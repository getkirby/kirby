
export default function (Vue) {
  return {
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
    id(id) {
      return id.replace(/\//g, "+");
    },
    unid(id) {
      return id.replace(/\+/g, "/");
    },
    link(id) {
      return "/" + this.url(id);
    },
    async options(id, view = "view") {
      const url     = this.url(id);
      const page    = await Vue.prototype.$api.get(url, { select: "options" });
      const options = page.options;
      let result    = [];

      if (view === "list") {
        result.push({
          click: "preview",
          icon: "open",
          text: Vue.i18n.translate("open"),
          disabled: options.preview === false
        });

        result.push("-");
      }

      result.push({
        click: "rename",
        icon: "title",
        text: Vue.i18n.translate("rename"),
        disabled: !options.changeTitle
      });

      result.push({
        click: "duplicate",
        icon: "copy",
        text: Vue.i18n.translate("duplicate"),
        disabled: !options.duplicate
      });

      result.push("-");

      result.push({
        click: "url",
        icon: "url",
        text: Vue.i18n.translate("page.changeSlug"),
        disabled: !options.changeSlug
      });

      result.push({
        click: "status",
        icon: "preview",
        text: Vue.i18n.translate("page.changeStatus"),
        disabled: !options.changeStatus
      });

      result.push({
        click: "template",
        icon: "template",
        text: Vue.i18n.translate("page.changeTemplate"),
        disabled: !options.changeTemplate
      });

      result.push("-");

      result.push({
        click: "remove",
        icon: "trash",
        text: Vue.i18n.translate("delete"),
        disabled: !options.delete
      });

      return result;
    },
    url(id, path) {
      let url = id === null ? "pages" : "pages/" + this.id(id);

      if (path) {
        url += "/" + path;
      }
      return url;
    }
  };
}
