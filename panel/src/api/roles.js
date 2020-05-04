import api from "./api.js";

export default {
  async list(params) {
    return api.get("roles", params);
  },
  async get(name) {
    return api.get("roles/" + name);
  }
};
