
export default {
  namespaced: true,
  state: {
    info: {
      title: null
    }
  },
  mutations: {
    SET_INFO(state, info) {
      state.info = info;
    },
    SET_LICENSE(state, license) {
      state.info.license = license;
    },
    SET_TITLE(state, title) {
      state.info.title = title;
    }
  },
  actions: {
    info(context, info) {
      context.commit("SET_INFO", info);
    },
    register(context, license) {
      context.commit("SET_LICENSE", license);
    },
    title(context, title) {
      context.commit("SET_TITLE", title);
    }
  }
};
