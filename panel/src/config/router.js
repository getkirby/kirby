import Vue from "vue";
import Router from "vue-router";
import routes from "./routes.js";
import supports from "./supports.js";
import store from "@/store/store.js";
import config from "./config.js";

Vue.use(Router);

const router = new Router({
  mode: "history",
  routes: routes,
  url: config.url === '/' ? '' : config.url,
});

// Check browser support
router.beforeEach((to, from, next) => {
  // check for supported browsers
  if (to.name !== "Browser" && supports.all() === false) {
    next("/browser");
  }

  // store the current view
  store.dispatch("view", to.meta.view);

  // reset the form store
  store.dispatch("form/reset");

  // keep the last visted path
  if (!to.meta.outside) {
    store.dispatch("user/visit", to.path);
  }

  next();
});

export default router;
