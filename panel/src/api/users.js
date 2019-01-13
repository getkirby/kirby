import Vue from "vue";
import api from "./api.js";

export default {
  create(data) {
    return api.post(this.url(), data);
  },
  list(query) {
    return api.post(this.url(null, "search"), query);
  },
  get(id, query) {
    return api.get(this.url(id), query);
  },
  update(id, data) {
    return api.patch(this.url(id), data);
  },
  delete(id) {
    return api.delete(this.url(id));
  },
  changeEmail(id, email) {
    return api.patch(this.url(id, "email"), { email: email });
  },
  changeLanguage(id, language) {
    return api.patch(this.url(id, "language"), { language: language });
  },
  changeName(id, name) {
    return api.patch(this.url(id, "name"), { name: name });
  },
  changePassword(id, password) {
    return api.patch(this.url(id, "password"), { password: password });
  },
  changeRole(id, role) {
    return api.patch(this.url(id, "role"), { role: role });
  },
  deleteAvatar(id) {
    return api.delete(this.url(id, "avatar"));
  },
  blueprint(id) {
    return api.get(this.url(id, "blueprint"));
  },
  breadcrumb(user) {
    return [
      {
        link: "/users/" + user.id,
        label: user.username
      }
    ];
  },
  options(id) {
    return api.get(this.url(id), {select: "options"}).then(user => {
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
    });
  },
  url(id, path) {
    let url = !id ? "users" : "users/" + id;

    if (path) {
      url += "/" + path;
    }

    return url;
  },
  link(id, path) {
    return "/" + this.url(id, path);
  }
};
