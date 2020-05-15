import config from "./config.js";

export default (Vue, store) => {

  return async (to, from, next) => {

    // load the system, the user and the translation
    await store.dispatch("system/load");
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
        message: Vue.i18n.translate("error.access.view")
      });

      return next("/");
    }

    next();
  };

};
