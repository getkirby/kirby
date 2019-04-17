import Vue from "vue";
import api from "@getkirby/api-js";

export default {
  options(id, view = "view") {
    return api.get(this.url(id), {select: "options"}).then(page => {
      const options = page.options;
      let result = [];
      if (view === "list") {
        result.push({
          click: "preview",
          icon: "open",
          text: Vue.i18n.translate("open"),
          disabled: options.preview === false
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
      result.push({
        click: "remove",
        icon: "trash",
        text: Vue.i18n.translate("delete"),
        disabled: !options.delete
      });
      return result;
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
  }
};
