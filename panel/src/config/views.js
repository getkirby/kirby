export default {
  site: {
    link: "/site",
    icon: "page",
    menu: true
  },
  users: {
    link: "/users",
    icon: "users",
    menu: true
  },
  settings: {
    link: "/settings",
    icon: "settings",
    menu: true
  },
  account: {
    link: "/account",
    icon: "users",
    menu: false
  },
  resetPassword: {
    link: "/reset-password",
    icon: "key",
    menu: false
  },
  ...window.panel.plugins.views
};
