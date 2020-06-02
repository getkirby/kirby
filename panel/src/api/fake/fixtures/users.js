import { blueprint } from "./blueprints.js";

export default [
  {
    avatar: {
      url: "https://source.unsplash.com/user/erondu/400x400",
    },
    blueprint: blueprint("admin"),
    content: {
      twitter: "adalovelace",
    },
    id: "ada",
    email: "ada@getkirby.com",
    language: "en",
    name: "Ada Lovelace",
    options: {
      changeEmail: true,
      changeLanguage: true,
      changeName: true,
      changePassword: true,
      changeRole: true,
      delete: true,
    },
    password: "demodemo",
    roleId: "admin",
    username: "Ada Lovelace"
  },
];
