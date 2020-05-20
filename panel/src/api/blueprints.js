export default (api) => {
  return {
    async list(params) {
      return api.get("blueprints", params);
    },
    async get(name) {
      return api.get("blueprints/" + name.replace("/", "+"));
    }
  }
};
