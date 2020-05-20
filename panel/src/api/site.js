export default (api) => {
  return {
    async get(query) {
      return api.get("site", query);
    },
    async update(data) {
      return api.post("site", data);
    },
    async title(title) {
      return api.patch("site/title", { title: title });
    },
    async children(query) {
      return api.post("site/children/search", query);
    },
    async blueprint() {
      return api.get("site/blueprint");
    },
    async blueprints() {
      return api.get("site/blueprints");
    }
  }
};
