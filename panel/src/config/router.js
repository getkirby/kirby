import { createRouter, createWebHistory } from 'vue-router'

import routes from "./routes.js";
import supports from "./supports.js";
import config from "./config.js";

export default {
  install(app) {

    const router = createRouter({
      history: createWebHistory(),
      routes: routes(app),
      url: config.url === '/' ? '' : config.url
    });

    router.beforeEach((to, from, next) => {
      const store = app.config.globalProperties.$store;

      // check for supported browsers
      if (to.name !== "Browser" && supports.all() === false) {
        next("/browser");
      }

      
      // keep the last visted path
      if (!to.meta.outside) {
        store.dispatch("user/visit", to.path);
      }
    
      // store the current view
      if (to.meta.view === "plugin") {
        store.dispatch("view", to.params.id);
      } else {
        store.dispatch("view", to.meta.view);
      }
    
      // reset the content locks
      store.dispatch("content/lock", null);
      store.dispatch("content/unlock", null);
    
      // clear all heartbeats
      store.dispatch("heartbeat/clear");
    
      next();
    });

    app.config.globalProperties.$go = (path) => {

      // support links with hash
      path = path.split("#");
      path = {
        path: path[0],
        hash: path[1] || null
      };
    
      router.push(path).catch(e => {
        if (e && e.name && e.name === "NavigationDuplicated") {
          return true;
        }
    
        throw e;
      });
    };
    
    app.use(router);
  }
};

