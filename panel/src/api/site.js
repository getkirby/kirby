export default (api) => {
  return {
    async blueprint() {
      return api.get("site/blueprint");
    },
    async blueprints() {
      return api.get("site/blueprints");
    },
    async changeTitle(title) {
      return api.patch("site/title", { title: title });
    },
    async children(query) {
      return api.post("site/children/search", query);
    },
    async get(query = { view: "panel" }) {
      return api.get("site", query);
    },
    async update(data) {
      return api.post("site", data);
    }
  };
};
