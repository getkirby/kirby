import api from "./api.js";

export default {
  async list() {
    return api.get("translations");
  },
  async get(locale) {
    return api.get("translations/" + locale);
  }
};
