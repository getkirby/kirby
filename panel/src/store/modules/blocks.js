export default {
  namespaced: true,
  state: {
    current: null,
  },
  mutations: {
    CURRENT(state, current) {
      state.current = current;
    },
  },
  actions: {
    current(context, current) {
      context.commit("CURRENT", current);
    }
  }
};
