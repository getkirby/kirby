
export default function (Vue, { $api, $events, $store}) {
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
    async changeSlug(id, slug) {
      const page = await $api.pages.changeSlug(id, slug);

      // move in content store
      await $store.dispatch("content/move", [
        "pages/" + id,
        "pages/" + page.id
      ]);

      $events.$emit("page.changeSlug", page);
      $store.dispatch("notification/success");
      return page;
    },
    async changeStatus(id, status, position) {
      const page = await $api.pages.changeStatus(id, status, position);
      $events.$emit("page.changeStatus", page);
      $store.dispatch("notification/success");
      return page;
    },
    async changeTemplate(id, template) {
      const page = await $api.pages.changeTemplate(id, template);
      $events.$emit("page.changeTemplate", page);
      $store.dispatch("notification/success");
      return page;
    },
    async changeTitle(id, title) {
      const page = await $api.pages.changeTitle(id, title);
      $events.$emit("page.changeTitle", page);
      $store.dispatch("notification/success");
      return page;
    },
    async create(parent, props) {
      const page = await $api.pages.create(parent, props);
      $events.$emit("page.create", page);
      $store.dispatch("notification/success");
      return page;
    },
    async delete(id, props) {
      // send API request to delete page
      await $api.pages.delete(id, props);

      // remove data from content store
      await $store.dispatch("content/remove", "pages/" + id);

      $events.$emit("page.delete", id);
      $store.dispatch("notification/success");
    },
    async duplicate(id, slug, props) {
      const page = await $api.pages.duplicate(id, slug, props);
      $events.$emit("page.create", page);
      $events.$emit("page.duplicate", page);
      $store.dispatch("notification/success");
      return page;
    },
    link(id) {
      return "/" + this.url(id);
    },
    async options(id, view = "view") {
      const url     = this.url(id);
      const page    = await $api.get(url, { select: "options" });
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
      let url = id === null ? "pages" : "pages/" + id.replace(/\//g, "+");

      if (path) {
        url += "/" + path;
      }
      return url;
    }
  };
}
