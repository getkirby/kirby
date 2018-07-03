import Vue from "vue";
import api from "./api.js";

export default {
  create(parent, data) {
    if (parent === null || parent === "/") {
      return api.post("site/children", data);
    }

    return api.post(this.url(parent, "children"), data);
  },
  url(id, path) {
    let url = id === null ? "pages" : "pages/" + id.replace(/\//g, "+");

    if (path) {
      url += "/" + path;
    }

    return url;
  },
  link(id) {
    return "/" + this.url(id);
  },
  get(id, query) {
    return api.get(this.url(id), query).then(page => {
      if (Array.isArray(page.content) === true) {
        page.content = {};
      }

      return page;
    });
  },
  options(id, view = "view") {
    return api.get(this.url(id, "options")).then(options => {
      let result = [];

      if (view === "list") {
        result.push({
          click: "preview",
          icon: "open",
          text: Vue.i18n.translate("open"),
        });
      }

      result.push({
        click: "rename",
        icon: "title",
        text: Vue.i18n.translate("rename"),
        disabled: !options.changeTitle
      });

      result.push({
        click: "url",
        icon: "url",
        text: Vue.i18n.translate("page.url.change"),
        disabled: !options.changeSlug
      });

      result.push({
        click: "status",
        icon: "preview",
        text: Vue.i18n.translate("page.status.change"),
        disabled: !options.changeStatus
      });

      result.push({
        click: "template",
        icon: "template",
        text: Vue.i18n.translate("page.template.change"),
        disabled: !options.changeTemplate
      });

      result.push({
        click: "remove",
        icon: "trash",
        text: Vue.i18n.translate("delete"),
        disabled: !options.delete
      });

      return result;
    });
  },
  update(id, data) {
    return api.patch(this.url(id), data);
  },
  children(id, query) {
    return api.post(this.url(id, "children/search"), query);
  },
  files(id, query) {
    return api.post(this.url(id, "files/search"), query);
  },
  delete(id) {
    return api.delete(this.url(id));
  },
  slug(id, slug) {
    return api.patch(this.url(id, "slug"), { slug: slug });
  },
  title(id, title) {
    return api.patch(this.url(id, "title"), { title: title });
  },
  template(id, template) {
    return api.patch(this.url(id, "template"), { template: template });
  },
  search(parent, query) {
    if (parent) {
      return api.post('pages/' + parent.replace('/', '+') + '/children/search?select=id,title,hasChildren', query);
    } else {
      return api.post('site/children/search?select=id,title,hasChildren', query);
    }
  },
  status(id, status, position) {
    return api.patch(this.url(id, "status"), {
      status: status,
      position: position
    });
  },
  states() {
    return {
      draft: {
        icon: "draft",
        label: Vue.i18n.translate("page.status.draft"),
        description: Vue.i18n.translate("page.status.draft.description")
      },
      unlisted: {
        icon: "toggle-off",
        label: Vue.i18n.translate("page.status.unlisted"),
        description: Vue.i18n.translate("page.status.unlisted.description")
      },
      listed: {
        icon: "toggle-on",
        label: Vue.i18n.translate("page.status.listed"),
        description: Vue.i18n.translate("page.status.listed.description")
      }
    };
  },
  breadcrumb(page, self = true) {
    var breadcrumb = page.parents.map(parent => ({
      label: parent.slug,
      link: this.link(parent.id)
    }));

    if (self === true) {
      breadcrumb.push({
        label: page.slug,
        link: this.link(page.id)
      });
    }

    return breadcrumb;
  }
};
