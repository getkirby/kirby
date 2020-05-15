
export default {
  languages: {
    defaultLanguageCode() {
      return "en";
    },
  },
  pages: {
    blueprints(id, section) {
      return [
        { name: "article", title: "Article" },
        { name: "project", title: "Project" }
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
    }
  },
  translations: {
    async options() {
      return [
        { value: "en", text: "English" },
        { value: "de", text: "Deutsch" },
      ];
    }
  }
};
