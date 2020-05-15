
export default function (Vue) {
  const Api = Vue.prototype.$api;

  return {
    async create(parent, props) {
      const page = await Api.pages.create(parent, props);
      this.$store.dispatch("notification/success", ":)");
      this.$events.$emit("page.create", page);

      const path = this.link(page.id);
      this.$router.push(path);
    },
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
    async duplicate(id, slug, props) {
      const page = await Api.pages.duplicate(id, slug, props);
      this.$store.dispatch("notification/success", ":)");
      this.$events.$emit("page.create", page);
      this.$events.$emit("page.duplicate", page);

      const path = this.link(page.id);
      this.$router.push(path);
    },
    link(id) {
      return "/" + this.url(id);
    },
    async options(id, view = "view") {
      const url     = this.url(id);
      const page    = await Api.get(url, { select: "options" });
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
    async remove(id, props) {
      // send API request to delete page
      await Api.pages.delete(id, props);

      // remove data from content store
      await this.$store.dispatch("content/remove", "pages/" + id);

      this.$store.dispatch("notification/success", ":)");
      this.$events.$emit("page.delete", id);
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
