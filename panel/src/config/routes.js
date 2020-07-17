import Vue from "vue";
import auth from "./auth.js";
import store from "@/store/store.js";

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
    component: Vue.component("k-browser-view"),
    meta: {
      outside: true
    }
  },
  {
    path: "/login",
    component: Vue.component("k-login-view"),
    meta: {
      outside: true
    }
  },
  {
    path: "/logout",
    beforeEnter() {

      // remove all form changes from localStorage
      Object.keys(localStorage).forEach(key => {
        if (key.startsWith("kirby$content$")) {
          localStorage.removeItem(key);
        }
      });

      store.dispatch("user/logout");

    },
    meta: {
      outside: true
    }
  },
  {
    path: "/installation",
    component: Vue.component("k-installation-view"),
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
    component: Vue.component("k-site-view"),
    beforeEnter: auth
  },
  {
    path: "/site/files/:filename",
    name: "SiteFile",
    meta: {
      view: "site"
    },
    component: Vue.component("k-file-view"),
    beforeEnter: auth,
    props: route => ({
      path: "site",
      filename: route.params.filename
    })
  },
  {
    path: "/pages/:path/files/:filename",
    name: "PageFile",
    meta: {
      view: "site"
    },
    component: Vue.component("k-file-view"),
    beforeEnter: auth,
    props: route => ({
      path: "pages/" + route.params.path,
      filename: route.params.filename
    })
  },
  {
    path: "/users/:path/files/:filename",
    name: "UserFile",
    meta: {
      view: "users"
    },
    component: Vue.component("k-file-view"),
    beforeEnter: auth,
    props: route => ({
      path: "users/" + route.params.path,
      filename: route.params.filename
    })
  },
  {
    path: "/pages/:path",
    name: "Page",
    meta: {
      view: "site"
    },
    component: Vue.component("k-page-view"),
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
    component: Vue.component("k-settings-view"),
    beforeEnter: auth
  },
  {
    path: "/users/role/:role",
    name: "UsersByRole",
    meta: {
      view: "users"
    },
    component: Vue.component("k-users-view"),
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
    component: Vue.component("k-users-view")
  },
  {
    path: "/users/:id",
    name: "User",
    meta: {
      view: "users"
    },
    component: Vue.component("k-user-view"),
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
    component: Vue.component("k-user-view"),
    beforeEnter: auth,
    props: () => ({
      id: store.state.user.current ? store.state.user.current.id : false
    })
  },
  {
    path: "/plugins/:id",
    name: "Plugin",
    meta: {
      view: "plugin"
    },
    props: route => ({
      plugin: route.params.id
    }),
    beforeEnter: auth,
    component: Vue.component("k-custom-view")
  },
  {
    path: "*",
    name: "NotFound",
    beforeEnter: (to, from, next) => {
      next("/");
    }
  }
];
