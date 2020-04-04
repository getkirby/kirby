import Vue from "vue";
import Api from "@/api/api.js";

export default {
  breadcrumb(user) {
    return [
      {
        link: "/users/" + user.id,
        label: user.username
      }
    ];
  },
  link(id, path) {
    return "/" + this.url(id, path);
  },
  async options(id) {
    const user    = await Api.get(this.url(id), { select: "options" });
    const options = user.options;
    let result    = [];

    result.push({
      click: "rename",
      icon: "title",
      text: Vue.i18n.translate("user.changeName"),
      disabled: !options.changeName
    });

    result.push({
      click: "email",
      icon: "email",
      text: Vue.i18n.translate("user.changeEmail"),
      disabled: !options.changeEmail
    });

    result.push({
      click: "role",
      icon: "bolt",
      text: Vue.i18n.translate("user.changeRole"),
      disabled: !options.changeRole
    });

    result.push({
      click: "password",
      icon: "key",
      text: Vue.i18n.translate("user.changePassword"),
      disabled: !options.changePassword
    });

    result.push({
      click: "language",
      icon: "globe",
      text: Vue.i18n.translate("user.changeLanguage"),
      disabled: !options.changeLanguage
    });

    result.push({
      click: "remove",
      icon: "trash",
      text: Vue.i18n.translate("user.delete"),
      disabled: !options.delete
    });

    return result;
  },
  url(id, path) {
    let url = !id ? "users" : "users/" + id;

    if (path) {
      url += "/" + path;
    }

    return url;
  }
}
