import api from "./api.js";

export default {
  async info(options) {
    return api.get("system", options);
  },
  async install(user) {
    const auth = await api.post("system/install", user);
    return auth.user;
  },
  async register(info) {
    return api.post("system/register", info);
  }
};
