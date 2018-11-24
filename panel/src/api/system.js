import api from "./api.js";

export default {
  info(options) {
    return api.get("system", options);
  },
  install(user) {
    return api.post("system/install", user).then(auth => {
      return auth.user;
    });
  },
  register(license) {
    return api.post("system/register", { license: license });
  }
};
