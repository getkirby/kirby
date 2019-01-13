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
  ...window.panel.plugins.views
};
