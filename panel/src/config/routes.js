import config from "./config.js";

export default function(app) {

  const auth = (to, from, next) => {
    const store = app.config.globalProperties.$store;

    // load the system, the user and the translation
    store.dispatch("system/load").then(() => {
      const user = store.state.user.current;
  
      // no user? logout!
      if (!user) {
        store.dispatch("user/visit", to.path);
        store.dispatch("user/logout");
        return false;
      }
  
      const access = user.permissions.access;
  
      // no access? redirect to website
      if (access.panel === false) {
        window.location.href = config.site;
        return false;
      }
  
      // no access to view? redirect to the panel index
      if (access[to.meta.view] === false) {
        store.dispatch("notification/error", {
          message: app.$t("error.access.view")
        });
  
        return next(access.site === false ? "/account" : "/");
      }
  
      next();
    });
  };

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
      component: app.component("k-browser-view"),
      meta: {
        outside: true
      }
    },
    {
      path: "/login",
      component: app.component("k-login-view"),
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

        app.$store.dispatch("user/logout");

      },
      meta: {
        outside: true
      }
    },
    {
      path: "/installation",
      component: app.component("k-installation-view"),
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
      component: app.component("k-site-view"),
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
      component: app.component("k-file-view"),
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
      component: app.component("k-file-view"),
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
      component: app.component("k-file-view"),
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
      component: app.component("k-page-view"),
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
      component: app.component("k-settings-view"),
      beforeEnter: auth
    },
    {
      path: "/users/role/:role",
      name: "UsersByRole",
      meta: {
        view: "users"
      },
      component: app.component("k-users-view"),
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
      component: app.component("k-users-view")
    },
    {
      path: "/users/:id",
      name: "User",
      meta: {
        view: "users"
      },
      component: app.component("k-user-view"),
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
      component: app.component("k-user-view"),
      beforeEnter: auth,
      props: route => ({
        id: app.$store.state.user.current ? app.$store.state.user.current.id : false,
        tab: route.hash.slice(1) || "main"
      })
    },
    {
      path: "/reset-password",
      name: "Reset password",
      meta: {
        view: "resetPassword"
      },
      component: app.component("k-reset-password-view"),
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
      component: app.component("k-custom-view")
    },
  ];

  // UI Sandbox
  if (import.meta.env.MODE !== "production") {
    routes.push({
      path: "/sandbox/:component?",
      name: "Sandbox",
      meta: {
        outside: true,
      },
      beforeEnter: auth,
      component: () => import("@/sandbox/Sandbox.vue"),
    });

    routes.push({
      path: "/sandbox/preview/:component",
      name: "SandboxComponent",
      meta: {
        outside: true,
      },
      beforeEnter: auth,
      component: () => import("@/sandbox/Iframe.vue"),
    });
  }

  routes.push({
    path: "/:pathMatch(.*)*",
    name: "NotFound",
    beforeEnter: (to, from, next) => {
      next("/");
    }
  });

  return routes;
};

