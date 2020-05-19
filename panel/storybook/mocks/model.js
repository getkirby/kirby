export default (Vue) => {
  return {
    files: {
      delete() {},
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
  };
};
