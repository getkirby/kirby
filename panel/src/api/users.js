export default (api) => {
  return {
    async blueprint(id) {
      return api.get("users/" + id + "/blueprint");
    },
    async blueprints(id, section) {
      return api.get("users/" + id + "/blueprints", {
        section: section
      });
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
    async create(data) {
      return api.post("users", data);
    },
    async delete(id) {
      return api.delete("users/" + id);
    },
    async deleteAvatar(id) {
      return api.delete("users/" + id + "/avatar");
    },
    link(id, path) {
      return "/" + this.url(id, path);
    },
    async list(query) {
      return api.post(this.url(null, "search"), query);
    },
    async get(id, query) {
      return api.get("users/" + id, query);
    },
    async roles(id) {
      const roles = await api.get(this.url(id, "roles"));
      return roles.data.map((role) => ({
        info:
          role.description ||
          `(${window.panel.$t("role.description.placeholder")})`,
        text: role.title,
        value: role.name
      }));
    },
    async search(query) {
      return api.post("users/search", query);
    },
    async update(id, data) {
      return api.patch("users/" + id, data);
    },
    url(id, path) {
      let url = !id ? "users" : "users/" + id;

      if (path) {
        url += "/" + path;
      }

      return url;
    }
  };
};
