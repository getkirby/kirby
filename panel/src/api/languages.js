import api from "./api.js";

export default {
  async get(code) {
    return await api.get("languages/" + code);
  },
  async delete(code) {
    return api.delete("languages/" + code);
  }
};
