
export default {
  namespaced: true,
  state: {
    kirbytext: null,
    license: null,
    multilang: null,
    requirements: null,
    site: null,
    status: {
      isReady: false
    },
    title: null,
    updates: {},
    version: null
  },
  mutations: {
    SET(state, system) {
      Object.keys(system).forEach(key => {
        if (state.hasOwnProperty(key) === true) {
          state[key] = system[key];
        }
      })
    }
  },
  actions: {
    register(context, license) {
      context.commit("SET", {
        license: license
      });
    },
    set(context, system) {
      context.commit("SET", system);
    },
    status(context, status) {
      context.commit("SET", {
        status: status
      });
    },
    title(context, title) {
      context.commit("SET", {
        title: title
      });
    }
  }
};
