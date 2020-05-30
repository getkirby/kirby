
export default (Vue, store) => ({
  breadcrumb(user) {
    return [
      {
        link: "/users/" + user.id,
        label: user.username
      }
    ];
  },
  async changeEmail(id, email) {
    const user = await Vue.$api.users.changeEmail(id, email);

    // if current panel user, update user store
    if (id === Vue.$user.id) {
      await store.dispatch("user/email", email);
    }

    Vue.$events.$emit("user.changeEmail", user);
    store.dispatch("notification/success");
    return user;
  },
  async changeLanguage(id, language) {
    const user = await Vue.$api.users.changeLanguage(id, language);

    // if current panel user, update store to switch language
    if (id === Vue.$user.id) {
      await store.dispatch("user/language", language);
    }

    Vue.$events.$emit("user.changeLanguage", user);
    store.dispatch("notification/success");
    return user;
  },
  async changeName(id, name) {
    const user = await Vue.$api.users.changeName(id, name);

    // if current panel user, update store
    if (id === Vue.$user.id) {
      await store.dispatch("user/name", name);
    }

    Vue.$events.$emit("user.changeName", user);
    store.dispatch("notification/success");
    return user;
  },
  async changePassword(id, password) {
    const user = await Vue.$api.users.changePassword(id, password);
    Vue.$events.$emit("user.changePassword", user);
    store.dispatch("notification/success");
    return user;
  },
  async changeRole(id, role) {
    const user = await Vue.$api.users.changeRole(id, role);

    // if current panel user, update store
    if (id === Vue.$user.id) {
      await this.load();
    }

    Vue.$events.$emit("user.changeRole", user);
    store.dispatch("notification/success");
    return user;
  },
  async create(values) {
    const user = await Vue.$api.users.create(values);
    Vue.$events.$emit("user.create", user);
    store.dispatch("notification/success");
    return user;
  },
  async delete(id) {
    // send API request to delete user
    await Vue.$api.users.delete(id);

    // remove data from content store
    await store.dispatch("content/remove", "users/" + id);

    Vue.$events.$emit("user.delete", id);
    store.dispatch("notification/success");
  },
  dropdown(options = {}) {
    let dropdown = [];

    dropdown.push({
      click: "rename",
      icon: "title",
      text: Vue.$t("user.changeName"),
      disabled: !options.changeName,
    });

    dropdown.push("-");

    dropdown.push({
      click: "email",
      icon: "email",
      text: Vue.$t("user.changeEmail"),
      disabled: !options.changeEmail,
    });

    dropdown.push({
      click: "role",
      icon: "bolt",
      text: Vue.$t("user.changeRole"),
      disabled: !options.changeRole,
    });

    dropdown.push({
      click: "password",
      icon: "key",
      text: Vue.$t("user.changePassword"),
      disabled: !options.changePassword,
    });

    dropdown.push({
      click: "language",
      icon: "globe",
      text: Vue.$t("user.changeLanguage"),
      disabled: !options.changeLanguage,
    });

    dropdown.push("-");

    dropdown.push({
      click: "remove",
      icon: "trash",
      text: Vue.$t("user.delete"),
      disabled: !options.delete,
    });

    return dropdown;
  },
  link(id, path) {
    return "/" + this.url(id, path);
  },
  async load() {
    const user = await Vue.$api.auth.user();
    store.dispatch("user/current", user);
    return user;
  },
  async login(credentials) {
    const user = await Vue.$api.auth.login(credentials)
    store.dispatch("user/current", user);
    store.dispatch("translation/activate", user.language);
    Vue.$router.push(store.state.user.path || "/");
    return user;
  },
  async logout(force = false) {
    store.dispatch("user/current", null);

    if (force) {
      window.location.href = (window.panel.url || "") + "/login";
      return;
    }

    try {
      await Vue.$api.auth.logout();
    } finally {
      Vue.$router.push("/login");
    }
  },
  async options(id) {
    const url  = this.url(id);
    const user = await Vue.$api.get(url, { select: "options" });
    return this.dropdown(user.options);
  },
  url(id, path) {
    let url = !id ? "users" : "users/" + id;

    if (path) {
      url += "/" + path;
    }

    return url;
  },
  async update(id, data) {
    const user = await Vue.$api.users.update(id, data);
    store.dispatch("content/update", { id: id, data: data });
    Vue.$events.$emit("user.update", user);
    store.dispatch("notification/success");
    return user;
  },
});
