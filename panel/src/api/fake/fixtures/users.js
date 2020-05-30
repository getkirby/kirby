import { blueprint } from "./blueprints.js";

export default [
  {
    avatar: {
      url: "https://source.unsplash.com/user/erondu/400x400",
    },
    blueprint: blueprint("admin"),
    id: "ada",
    roleId: "admin",
    email: "ada@getkirby.com",
    name: "Ada Lovelace",
    language: "en",
    options: {
      changeEmail: true,
      changeLanguage: true,
      changeName: true,
      changePassword: true,
      changeRole: true,
      delete: true
    },
    password: "demodemo",
  },
];
