import api from "./api.js";

export default {
  async user() {
    return api.get("auth");
  },
  async login(user) {
    let data = {
      long: user.remember || false,
      email: user.email,
      password: user.password
    };

    let auth = await api.post("auth/login", data);
    return auth.user;
  },
  async logout() {
    return api.post("auth/logout");
  }
};
