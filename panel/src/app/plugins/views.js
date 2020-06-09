export default (Vue) => [
  {
    id: "site",
    link: "/site",
    icon: "home",
    text: Vue.$store.state.system.site || Vue.$t("view.site")
  },
  ...Object.values(window.panel.plugins.views).map(view => {
    view.text = view.text || Vue.$t("view." + view.id);
    return view;
  }),
  {
    id: "users",
    link: "/users",
    icon: "users",
    text: Vue.$t("view.users"),
  },
  {
    id: "settings",
    link: "/settings",
    icon: "settings",
    text: Vue.$t("view.settings")
  },
  "-",
  {
    id: "account",
    link: "/account",
    icon: "account",
    text: Vue.$t("view.account")
  },
  "-",
  {
    id: "logout",
    link: "/logout",
    icon: "logout",
    text: Vue.$t("logout")
  }
];
