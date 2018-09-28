import Vue from "vue";
import api from "./api.js";

export default {
  get(page, filename, query) {
    return api.get(this.url(page, filename), query).then(file => {
      if (Array.isArray(file.content) === true) {
        file.content = {};
      }

      return file;
    });
  },
  update(page, filename, data) {
    return api.patch(this.url(page, filename), data);
  },
  rename(page, filename, to) {
    return api.patch(this.url(page, filename, "rename"), {
      name: to
    });
  },
  url(page, filename, path) {
    let url = "";

    if (!page) {
      url = "site/files/" + filename;
    } else {
      url = api.pages.url(page, "files/" + filename);
    }

    if (path) {
      url += "/" + path;
    }

    return url;
  },
  link(page, filename, path) {
    return "/" + this.url(page, filename, path);
  },
  delete(page, filename) {
    return api.delete(this.url(page, filename));
  },
  options(page, filename, view) {
    return api.get(this.url(page, filename, "options")).then(options => {
      let result = [];

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
  preview(file) {
    if (file.type === "image") {
      return {
        image: file.url
      };
    }

    let preview = {
      icon: "file",
      color: "#81a2be"
    };

    switch (file.mime) {
      case "application/zip":
        preview.icon = "file-zip";
        break;
    }

    preview.color = this.color(file);

    switch (file.extension) {
      case "indd":
        preview.color = "#cc6666";
        break;
      case "pdf":
        preview.icon = "file-pdf";
        break;
      case "css":
      case "js":
      case "xml":
        preview.icon = "file-code";
        break;
      case "xls":
      case "xlsx":
      case "csv":
        preview.icon = "file-checklist";
        preview.color = "#b5bd68";
        break;
      case "mdown":
      case "md":
        preview.icon = "markdown";
        break;
      case "mov":
      case "m4v":
        preview.icon = "video";
        break;
    }

    return preview;
  },
  color(file) {
    switch (file.type) {
      case "image":
        return "#454953";
      case "video":
        return "#f0c674";
      case "document":
        return "#cc6666";
      case "audio":
        return "#8abeb7";
      case "code":
        return "#b294bb";
      default:
        return "#81a2be";
    }
  },
  breadcrumb(file, self = true) {
    var breadcrumb = file.parents.map(parent => ({
      label: parent.title,
      link: api.pages.link(parent.id)
    }));

    if (self === true) {
      breadcrumb.push({
        label: file.filename,
        link: this.link(file.parent.id, file.filename)
      });
    }

    return breadcrumb;
  }
};
