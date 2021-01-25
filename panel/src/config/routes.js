import Vue from "vue";
import auth from "./auth.js";
import store from "@/store/store.js";

// make sure custom components are loaded
// to support overwriting route views
import "./plugins.js";

/* Routes */
let routes = [
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
    beforeEnter: auth,
    props: route => ({
      tab: route.hash.slice(1) || "main",
    })
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
      filename: route.params.filename,
      path: "site",
      tab: route.hash.slice(1) || "main",
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
      filename: route.params.filename,
      path: "pages/" + route.params.path,
      tab: route.hash.slice(1) || "main",
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
      filename: route.params.filename,
      path: "users/" + route.params.path,
      tab: route.hash.slice(1) || "main",
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
      path: route.params.path,
      tab: route.hash.slice(1) || "main"
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
      id: route.params.id,
      tab: route.hash.slice(1) || "main",
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
    props: route => ({
      id: store.state.user.current ? store.state.user.current.id : false,
      tab: route.hash.slice(1) || "main"
    })
  },
  {
    path: "/reset-password",
    name: "Reset password",
    meta: {
      view: "resetPassword"
    },
    component: Vue.component("k-reset-password-view"),
    beforeEnter: auth
  },
  {
    path: "/plugins/:id",
    name: "Plugin",
    meta: {
      view: "plugin"
    },
    props: route => ({
      plugin: route.params.id,
      hash: route.hash.slice(1)
    }),
    beforeEnter: auth,
    component: Vue.component("k-custom-view")
  },
];

// UI Sandbox
if (process.env.NODE_ENV !== "production") {

  routes.push({
    path: "/sandbox/:component?",
    name: "Sandbox",
    meta: {
      outside: true,
    },
    beforeEnter: auth,
    component: require("@/sandbox/Sandbox.vue").default,
  });

  routes.push({
    path: "/sandbox/preview/:component",
    name: "SandboxComponent",
    meta: {
      outside: true,
    },
    beforeEnter: auth,
    component: require("@/sandbox/Iframe.vue").default,
  });

}

routes.push({
  path: "*",
  name: "NotFound",
  beforeEnter: (to, from, next) => {
    next("/");
  }
});


export default routes;
