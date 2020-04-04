import Vue from "vue";
import Api from "@/api/api.js";

export default {
  breadcrumb(file, route) {
    let parent = null;
    let breadcrumb = [];

    switch (route) {
      case "UserFile":
        breadcrumb.push({
          label: file.parent.username,
          link: Api.users.link(file.parent.id)
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
  link(parent, filename, path) {
    return "/" + this.url(parent, filename, path);
  },
  async options(parent, filename, view) {
    const file    = await Api.get(this.url(parent, filename), { select: "options" });
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
}
