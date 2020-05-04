import api from "./api.js";

export default {
  async create(data) {
    return api.post("users", data);
  },
  async list(query) {
    return api.post("users/search", query);
  },
  async get(id, query) {
    return api.get("users/" + id, query);
  },
  async update(id, data) {
    return api.patch("users/" + id, data);
  },
  async delete(id) {
    return api.delete("users/" + id);
  },
  async blueprint(id) {
    return api.get("users/" + id + "/blueprint");
  },
  async changeEmail(id, email) {
    return api.patch("users/" + id + "/email", { email: email });
  },
  async changeLanguage(id, language) {
    return api.patch("users/" + id + "/language", { language: language });
  },
  async changeName(id, name) {
    return api.patch("users/" + id + "/name", { name: name });
  },
  async changePassword(id, password) {
    return api.patch("users/" + id + "/password", { password: password });
  },
  async changeRole(id, role) {
    return api.patch("users/" + id + "/role", { role: role });
  },
  async deleteAvatar(id) {
    return api.delete("users/" + id + "/avatar");
  }
};
