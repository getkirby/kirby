
export default {
  install(Vue, router) {
    Vue.prototype.$go = (path) => {
      router.push(path).catch(e => {
        if (e && e.name && e.name === "NavigationDuplicated") {
          return true;
        }

        throw e;
      });
    };
  }
};
