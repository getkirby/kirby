export default {
  timer: null,
  namespaced: true,
  state: {
    type: null,
    message: null,
    details: null,
    timeout: null
  },
  mutations: {
    SET(state, notification) {
      state.type = notification.type;
      state.message = notification.message;
      state.details = notification.details;
      state.timeout = notification.timeout;
    },
    UNSET(state) {
      state.type = null;
      state.message = null;
      state.details = null;
      state.timeout = null;
    }
  },
  actions: {
    close(context) {
      clearTimeout(this.timer);
      context.commit("UNSET");
    },
    deprecated(context, message) {
      console.warn("Deprecated: " + message);
    },
    error(context, error) {
      if (error instanceof PromiseRejectionEvent) {
        error.preventDefault();
        error = error.reason;
      }

      if (typeof error === "string") {
        error = { message: error };
      }

      if (window.panel.$config.debug) {
        window.console.error(error);
      }

      context.dispatch("dialog", {
        component: "k-error-dialog",
        props: error,
      }, {root: true});

      context.dispatch("close");
    },
    open(context, payload) {
      context.dispatch("close");
      context.commit("SET", payload);

      if (payload.timeout) {
        this.timer = setTimeout(() => {
          context.dispatch("close");
        }, payload.timeout);
      }
    },
    success(context, payload) {
      if (typeof payload === "string") {
        payload = { message: payload };
      }

      context.dispatch("open", {
        type: "success",
        timeout: 4000,
        ...payload
      });
    }
  }
};
