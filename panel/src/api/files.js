import Vue from "vue";
import api from "./api.js";

export default {
  get(parent, filename, query) {
    return api.get(this.url(parent, filename), query).then(file => {
      if (Array.isArray(file.content) === true) {
        file.content = {};
      }

      return file;
    });
  },
  update(parent, filename, data) {
    return api.patch(this.url(parent, filename), data);
  },
  rename(parent, filename, to) {
    return api.patch(this.url(parent, filename, "name"), {
      name: to
    });
  },
  url(parent, filename, path) {
    let url = parent + "/files/" + filename;

    if (path) {
      url += "/" + path;
    }

    return url;
  },
  link(parent, filename, path) {
    return "/" + this.url(parent, filename, path);
  },
  delete(parent, filename) {
    return api.delete(this.url(parent, filename));
  },
  options(parent, filename, view) {
    return api.get(this.url(parent, filename), {select: "options"}).then(file => {
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
    });
  },
  breadcrumb(file, route) {

    let parent = null;
    let breadcrumb = [];

    switch (route) {
      case "UserFile":
        breadcrumb.push({
          label: file.parent.username,
          link: api.users.link(file.parent.id)
        });
        parent = 'users/' + file.parent.id;
        break;
      case "SiteFile":
        parent = "site";
        break;
      case "PageFile":
        breadcrumb = file.parents.map(parent => ({
          label: parent.title,
          link: api.pages.link(parent.id)
        }));
        parent = api.pages.url(file.parent.id);
        break;
    }

    breadcrumb.push({
      label: file.filename,
      link: this.link(parent, file.filename)
    });

    return breadcrumb;
  }
};
