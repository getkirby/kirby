export default (api) => {
  let site = {
    async get(query = { view: "panel" }) {
      return api.get("site", query);
    },
    async update(data) {
      return api.post("site", data);
    },
    async changeTitle(title) {
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
  };

  // deprecated aliases
  site.title = site.changeTitle;

  return site;
};
