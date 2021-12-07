export default {
  namespaced: true,
  state: {
    open: []
  },
  mutations: {
    CLOSE(state, id) {
      if (id) {
        state.open = state.open.filter((item) => item.id !== id);
      } else {
        state.open = [];
      }
    },
    GOTO(state, id) {
      state.open = state.open.slice(
        0,
        state.open.findIndex((item) => item.id === id) + 1
      );
    },
    OPEN(state, drawer) {
      state.open.push(drawer);
    }
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
