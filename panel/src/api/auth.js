import api from "./api.js";

export default {
  user() {
    return api.get("auth");
  },
  login(user) {
    let data = {
      long: user.remember || false,
      email: user.email,
      password: user.password
    };

    return api.post("auth/login", data).then(auth => {
      return auth.user;
    });
  },
  logout() {
    return api.post("auth/logout");
  }
};
