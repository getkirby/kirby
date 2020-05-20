import users from "./users.js";

export default {
  get: {
    "auth": () => ({
      code: 404,
      status: "error",
      message: "The user could not be found"
    })
  },
  post: {
    "auth/login": (credentials) => {
      return {
        code: 200,
        status: "ok",
        user: users.get["users/ada"]()
      };
    },
    "auth/logout": () => ({
      code: 200,
      status: "ok"
    })
  }
};
