const users = {
  ada: {
    id: "ada",
    role: "admin",
    email: "ada@getkirby.com",
    name: "Ada Lovelace",
    language: "en"
  }
};

export default {
  get: {
    "users": () => (Object.values(users)),
    "users/ada": () => (users.ada)
  },
  post: {
    "users/search": () => (Object.values(users)),
  }
};
