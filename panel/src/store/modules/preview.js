export default {
  namespaced: true,
  state: {
    active: false,
    exit: null,
    model: null,
  },
  getters: {
    model: state => {
      return state.model;
    }
  },
  mutations: {
    SET_ACTIVE(state, active) {
      state.active = active;
    },
    SET_EXIT(state, exit) {
      state.exit = exit;
    },
    SET_MODEL(state, model) {
      state.model = model;
    }
  },
  actions: {
    current(context, model) {
      context.commit("SET_MODEL", model);
    },
    exit(context, url) {
      context.commit("SET_EXIT", url);
    },
    toggle(context) {
      const state = !context.state.active;
      context.commit("SET_ACTIVE", state);
    }
  }
};
