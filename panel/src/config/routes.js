import auth from "./auth.js";
import store from "./store.js";

/* Views */
import BrowserView from "@/components/Views/BrowserView.vue";
import FileView from "@/components/Views/FileView.vue";
import InstallationView from "@/components/Views/InstallationView.vue";
import SettingsView from "@/components/Views/SettingsView.vue";
import LoginView from "@/components/Views/LoginView.vue";
import PageView from "@/components/Views/PageView.vue";
import SiteView from "@/components/Views/SiteView.vue";
import UsersView from "@/components/Views/UsersView.vue";
import UserView from "@/components/Views/UserView.vue";

/* Routes */
export default [
  {
    path: "/",
    name: "Home",
    redirect: "/site"
  },
  {
    path: "/browser",
    name: "Browser",
    component: BrowserView,
    meta: {
      outside: true
    }
  },
  {
    path: "/login",
    component: LoginView,
    meta: {
      outside: true
    }
  },
  {
    path: "/logout",
    beforeEnter() {
      store.dispatch("user/logout");
    },
    meta: {
      outside: true
    }
  },
  {
    path: "/installation",
    component: InstallationView,
    meta: {
      outside: true
    }
  },
  {
    path: "/site",
    name: "Site",
    meta: {
      view: "site"
    },
    component: SiteView,
    beforeEnter: auth
  },
  {
    path: "/site/files/:filename",
    name: "SiteFile",
    meta: {
      view: "site"
    },
    component: FileView,
    beforeEnter: auth,
    props: route => ({
      path: null,
      filename: route.params.filename
    })
  },
  {
    path: "/pages/:path+/files/:filename",
    name: "File",
    meta: {
      view: "site"
    },
    component: FileView,
    beforeEnter: auth,
    props: route => ({
      path: route.params.path,
      filename: route.params.filename
    })
  },
  {
    path: "/pages/:path+",
    name: "Page",
    meta: {
      view: "site"
    },
    component: PageView,
    beforeEnter: auth,
    props: route => ({
      path: route.params.path
    })
  },
  {
    path: "/settings",
    name: "Settings",
    meta: {
      view: "settings"
    },
    component: SettingsView,
    beforeEnter: auth
  },
  {
    path: "/users/role/:role",
    name: "UsersByRole",
    meta: {
      view: "users"
    },
    component: UsersView,
    beforeEnter: auth,
    props: route => ({
      role: route.params.role
    })
  },
  {
    path: "/users",
    name: "Users",
    meta: {
      view: "users"
    },
    beforeEnter: auth,
    component: UsersView
  },
  {
    path: "/users/:id",
    name: "User",
    meta: {
      view: "users"
    },
    component: UserView,
    beforeEnter: auth,
    props: route => ({
      id: route.params.id
    })
  },
  {
    path: "/account",
    name: "Account",
    meta: {
      view: "account"
    },
    component: UserView,
    beforeEnter: auth,
    props: () => ({
      id: store.state.user.current.id
    })
  },
  {
    path: "*",
    name: "NotFound",
    beforeEnter: (to, from, next) => {
      next("/");
    }
  }
];
