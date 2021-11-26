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
      // props for the dialog
      let props = error;

      // handle when a simple string is thrown as error
      // we should avoid that whenever possible
      if (typeof error === "string") {
        props = {
          message: error
        };
      }

      // handle proper Error instances
      if (error instanceof Error) {
        // convert error objects to props for the dialog
        props = {
          message: error.message
        };

        // only log errors to the console in debug mode
        if (window.panel.$config.debug) {
          window.console.error(error);
        }
      }

      // show the error dialog
      context.dispatch(
        "dialog",
        {
          component: "k-error-dialog",
          props: props
        },
        { root: true }
      );

      // remove the notification from store
      // to avoid showing it in the topbar
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
