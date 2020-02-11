import Router from "vue-router";
import routes from "./routes.js";
import supports from "./supports.js";
import config from "./config.js";

export default (Vue, store) => {

  Vue.use(Router);

  const router = new Router({
    mode: "history",
    routes: routes(Vue, store),
    url: config.url === '/' ? '' : config.url,
  });

  router.beforeEach((to, from, next) => {
    // check for supported browsers
    if (to.name !== "Browser" && supports.all() === false) {
      next("/browser");
    }

    // keep the last visted path
    if (!to.meta.outside) {
      store.dispatch("user/visit", to.path);
    }

    // store the current view
    store.dispatch("view", to.meta.view);

    // reset the content locks
    store.dispatch("content/lock", null);
    store.dispatch("content/unlock", null);

    // clear all heartbeats
    store.dispatch("heartbeat/clear");

    next();
  });

  return router;

};
