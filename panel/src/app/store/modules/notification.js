export default {
  namespaced: true,
  state: {
    alerts: [],
    dialog: null
  },
  mutations: {
    ADD_ALERT(state, notification) {
      state.alerts = state.alerts.filter(existing => {
        return existing.type !== notification.type ||
               existing.message !== notification.message;
      });
      state.alerts.push(notification);
    },
    REMOVE_ALERT(state, id) {
      state.alerts = state.alerts.filter(alert => alert.id !== id);
    },
    SET_DIALOG(state, notification) {
      state.dialog = notification;
    },
    UNSET_DIALOG(state) {
      state.dialog = null;
    }
  },
  actions: {
    close(context, id) {
      if (id) {
        context.commit("REMOVE_ALERT", id);
      } else {
        context.commit("UNSET_DIALOG");
      }
    },
    send(context, [payload, defaults]) {
      // shorthand
      if (typeof payload === "string") {
        payload = { message: payload };
      }

      // defaults
      payload = {
        id: Date.now(),
        ...defaults,
        ...payload
      };

      // permanent
      if (payload.permanent) {
        delete payload.timeout;
      }

      // dialog with details
      if (payload.details && Object.keys(payload.details).length) {
        context.commit("SET_DIALOG", payload);

      // alert
      } else {
        context.commit("ADD_ALERT", payload);

        if (payload.timeout) {
          setTimeout(() => {
            context.commit("REMOVE_ALERT", payload.id);
          }, payload.timeout);
        }
      }
    },

    /** Shortcuts for notification types */
    error(context, payload) {
      context.dispatch("send", [payload, {
        type: "error",
        permanent: true
      }]);
    },
    info(context, payload) {
      context.dispatch("send", [payload, {
        type: "info",
        timeout: 4000
      }]);
    },
    success(context, payload) {
      context.dispatch("send", [payload, {
        type: "success",
        message: "👍",
        timeout: 4000
      }]);
    }
  }
};
