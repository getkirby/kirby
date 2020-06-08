export default (api) => {
  return {
    async login(user) {
      const data = {
        long: user.remember || false,
        email: user.email,
        password: user.password
      };

      const auth = await api.post("auth/login", data);
      return auth.user;
    },
    async logout() {
      return api.post("auth/logout");
    },
    async user(params) {
      return api.get("auth", params);
    },
  }
};
