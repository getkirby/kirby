import Users from "../data/Users.js";

export default (Vue) => {
  return {
    files: {
      changeName() {},
      delete() {},
      get(parent, filename) {
        return {
          filename: "example.jpg",
          height: 900,
          mime: "image/jpeg",
          niceSize: "128 KB",
          orientation: "landscape",
          template: "cover",
          url: "https://source.unsplash.com/user/erondu/1600x900",
          width: 1600,
        };
      },
      options(parent, filename, view) {
        let result = [];

        if (view === "list") {
          result.push({
            icon: "open",
            text: Vue.$t("open"),
            click: "download",
          });
        }

        result.push({
          icon: "title",
          text: Vue.$t("rename"),
          click: "rename",
        });

        result.push({
          icon: "upload",
          text: Vue.$t("replace"),
          click: "replace",
        });

        result.push({
          icon: "trash",
          text: Vue.$t("delete"),
          click: "remove",
        });

        return result;
      },
    },
    languages: {
      list() {
        return [
          { code: "en", name: "English", default: true },
          { code: "de", name: "Deutsch" },
        ];
      },
      defaultLanguageCode() {
        return "en";
      },
    },
    pages: {
      blueprints(id, section) {
        return [
          { name: "article", title: "Article" },
          { name: "project", title: "Project" },
        ];
      },
      changeSlug() {},
      changeStatus() {},
      changeTemplate() {},
      changeTitle() {},
      create() {},
      delete() {},
      duplicate() {},
    },
    roles: {
      async options() {
        return [
          { value: "admin", text: "Admin" },
          { value: "editor", text: "Editor" },
          { value: "client", text: "Client" },
        ];
      },
    },
    translations: {
      async options() {
        return [
          { value: "en", text: "English" },
          { value: "de", text: "Deutsch" },
        ];
      },
    },
    users: {
      changeEmail() {},
      changeLanguage() {},
      changeName() {},
      changePassword() {},
      changeRole() {},
      create() {},
      delete() {},
      async list({ limit }) {
        return Users(limit);
      }
    }
  };
};
