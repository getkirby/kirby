export default (api) => {
  return {
    async get(options = { view: "panel" }) {
      return api.get("system", options);
    },
    async install(user) {
      const auth = await api.post("system/install", user);
      return auth.user;
    },
    async register(license) {
      return api.post("system/register", license);
    }
  };
};
