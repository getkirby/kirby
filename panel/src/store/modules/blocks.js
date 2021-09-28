export default {
  namespaced: true,
  state: {
    current: null,
    clipboard: null,
  },
  mutations: {
    CURRENT(state, current) {
      state.current = current;
    },
    CLIPBOARD(state, clipboard) {
      state.clipboard = clipboard;
    },
  },
  actions: {
    copy(context, clipboard) {
      context.commit("CLIPBOARD", clipboard);
    },
    current(context, current) {
      context.commit("CURRENT", current);
    }
  }
};
