
export default function (Vue, { $api, $events, $store, $user}) {
  return {
    breadcrumb(user) {
      return [
        {
          link: "/users/" + user.id,
          label: user.username
        }
      ];
    },
    async changeEmail(id, email) {
      const user = await $api.users.changeEmail(id, email);

      // if current panel user, update user store
      if (id === $user.id) {
        await $store.dispatch("user/email", email);
      }

      // move in content store
      await $store.dispatch("content/move", [
        "users/" + id,
        "users/" + user.id
      ]);

      $events.$emit("user.changeEmail", user);
      $store.dispatch("notification/success");
      return user;
    },
    async changeLanguage(id, language) {
      const user = await $api.users.changeLanguage(id, language);

      // if current panel user, update store to switch language
      if (id === $user.id) {
        await $store.dispatch("user/language", language);
      }

      $events.$emit("user.changeLanguage", user);
      $store.dispatch("notification/success");
      return user;
    },
    async changeName(id, name) {
      const user = await $api.users.changeName(id, name);

      // if current panel user, update store
      if (id === $user.id) {
        await $store.dispatch("user/name", name);
      }

      $events.$emit("user.changeName", user);
      $store.dispatch("notification/success");
      return user;
    },
    async changePassword(id, password) {
      const user = await $api.users.changePassword(id, password);
      $events.$emit("user.changePassword", user);
      $store.dispatch("notification/success");
      return user;
    },
    async changeRole(id, role) {
      const user = await $api.users.changeRole(id, role);

      // if current panel user, update store
      if (id === $user.id) {
        await $store.dispatch("user/load");
      }

      $events.$emit("user.changeRole", user);
      $store.dispatch("notification/success");
      return user;
    },
    async create(values) {
      const user = await $api.users.create(values);
      $events.$emit("user.create", user);
      $store.dispatch("notification/success");
      return user;
    },
    async delete(id) {
      // send API request to delete user
      await $api.files.delete(id);

      // remove data from content store
      await $store.dispatch("content/remove", "users/" + id);

      $events.$emit("user.delete", file);
      $store.dispatch("notification/success");
    },
    link(id, path) {
      return "/" + this.url(id, path);
    },
    async options(id) {
      const url     = this.url(id);
      const user    = await $api.get(url, { select: "options" });
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
  };
}
