import Users from "./users.js";

export default function (Vue, { $api, $events, $store}) {
  return {
    breadcrumb(file, route) {
      let parent = null;
      let breadcrumb = [];

      switch (route) {
        case "UserFile":
          breadcrumb.push({
            label: file.parent.username,
            link: Users(Vue).link(file.parent.id)
          });
          parent = 'users/' + file.parent.id;
          break;
        case "SiteFile":
          parent = "site";
          break;
        case "PageFile":
          breadcrumb = file.parents.map(parent => ({
            label: parent.title,
            link: this.link(parent.id)
          }));
          parent = this.url(file.parent.id);
          break;
      }

      breadcrumb.push({
        label: file.filename,
        link: this.link(parent, file.filename)
      });

      return breadcrumb;
    },
    async changeName(id, parent, filename, name) {
      const file = await $api.pages.changeName(parent, filename, name);

      // move in content store
      await $store.dispatch("content/move", [
        "files/" + id,
        "files/" + file.id
      ]);

      this.onUpdate("changeName", file);
      return file;
    },
    async delete(id, parent, filename) {
      // send API request to delete file
      await $api.files.delete(parent, filename);

      // remove data from content store
      await $store.dispatch("content/remove", "files/" + id);

      this.onUpdate("delete", id);
    },
    link(parent, filename, path) {
      return "/" + this.url(parent, filename, path);
    },
    onUpdate(event, data) {
      $events.$emit("file." + event, data);
      $store.dispatch("notification/success");
    },
    async options(parent, filename, view) {
      const url     = this.url(parent, filename);
      const file    = await $api.get(url, { select: "options" });
      const options = file.options;
      let result    = [];

      if (view === "list") {
        result.push({
          icon: "open",
          text: Vue.i18n.translate("open"),
          click: "download"
        });
      }

      result.push({
        icon: "title",
        text: Vue.i18n.translate("rename"),
        click: "rename",
        disabled: !options.changeName
      });

      result.push({
        icon: "upload",
        text: Vue.i18n.translate("replace"),
        click: "replace",
        disabled: !options.replace
      });

      result.push({
        icon: "trash",
        text: Vue.i18n.translate("delete"),
        click: "remove",
        disabled: !options.delete
      });

      return result;
    },
    url(parent, filename, path) {
      let url = parent + "/files/" + filename;

      if (path) {
        url += "/" + path;
      }

      return url;
    }
  };
}
