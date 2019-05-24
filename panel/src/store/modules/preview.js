export default {
  namespaced: true,
  state: {
    active: false,
    after: null,
    model: null,
  },
  getters: {
    url: state => {
      return state.model.previewUrl;
    }
  },
  mutations: {
    SET_ACTIVE(state, active) {
      state.active = active;
    },
    SET_AFTER(state, after) {
      state.after = after;
    },
    SET_MODEL(state, model) {
      state.model = model;
    }
  },
  actions: {
    after(context, url) {
      context.commit("SET_AFTER", url);
    },
    current(context, model) {
      context.commit("SET_MODEL", model);
    },
    toggle(context) {
      context.commit("SET_ACTIVE", !context.state.active);
    }
  }
};
