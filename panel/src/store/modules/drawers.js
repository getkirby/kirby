export default {
  namespaced: true,
  state: {
    open: [],
  },
  mutations: {
    CLOSE(state, id) {
      state.open = state.open.filter(item => item.id !== id);
    },
    GOTO(state, id) {
      state.open = state.open.filter(item => item.id === id);
    },
    OPEN(state, drawer) {
      state.open.push(drawer);
    },
  },
  actions: {
    close(context, id) {
      context.commit("CLOSE", id);
    },
    goto(context, id) {
      context.commit("GOTO", id);
    },
    open(context, drawer) {
      context.commit("OPEN", drawer);
    }
  }
};
