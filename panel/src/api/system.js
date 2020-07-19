export default (api) => {
  let system = {
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
  }

  // @deprecated aliases
  // TODO: remove in 3.6.0
  system.info = system.get;

  return system;
};
