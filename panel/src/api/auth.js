import api from "./api.js";

export default {
  user() {
    return api.get("auth");
  },
  login(user) {
    let data = {
      long: user.remember || false
    };

    let headers = {
      headers: {
        Authorization: `Basic ${btoa(`${user.email}:${user.password}`)}`
      }
    };

    return api.post("auth/login", data, headers).then(auth => {
      return auth.user;
    });
  },
  logout() {
    return api.post("auth/logout");
  }
};
