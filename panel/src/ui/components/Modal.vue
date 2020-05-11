<script>

/**
 * The Modal component is a renderless component.
 * It is meant to be used as boilerplate for all
 * kinds of modal dialogs and quick inline inputs.
 * It's also the foundation of the big
 * Dialog and Drawer components.
 */
export default {
  props: {
    autofocus: {
      type: Boolean,
      default: true,
    },
    cancelButton: {
      type: [Boolean, Object, String],
      default: true
    },
    submitButton: {
      type: [Boolean, Object, String],
      default: true
    },
  },
  data() {
    return {
      isLoading: false,
      notification: false
    };
  },
  mounted() {
    if (this.autofocus !== true) {
      return;
    }

    let target = this.$el.querySelector(
      "[autofocus], [data-autofocus], input, textarea, select, button"
    );

    if (target && typeof target.focus === "function") {
      target.focus();
      return;
    }
  },
  computed: {
    cancelButtonConfig() {
      return this.buttonConfig("cancelButton", {
        icon: "cancel",
        text: this.$t("cancel")
      });
    },
    submitButtonConfig() {
      return this.buttonConfig("submitButton", {
        icon: "check",
        text: this.$t("confirm")
      });
    }
  },
  methods: {
    buttonConfig(prop, defaults) {
      let button = this.$props[prop];

      if (button === false) {
        return false;
      }

      if (button === true) {
        return defaults;
      }

      if (typeof button === "string") {
        button = { text: button };
      }

      return {
        ...defaults,
        ...button
      };
    },
    cancel(event) {
      this.$emit("cancel", event);
    },
    closeNotification() {
      this.notification = false;
    },
    error(message) {
      this.openNotification({
        message: message,
        type: "error"
      });
    },
    openNotification(notification) {
      this.notification = notification;
    },
    startLoading() {
      this.isLoading = true;
    },
    stopLoading() {
      this.isLoading = false;
    },
    submit(event) {
      this.$emit("submit", event);
    },
    success(message) {
      this.openNotification({
        message: message,
        type: "success"
      });
    },
  },
  render() {
    return this.$scopedSlots.default({
      cancel: this.cancel,
      cancelButton: this.cancelButtonConfig,
      closeNotification: this.closeNotification,
      error: this.error,
      isLoading: this.isLoading,
      notification: this.notification,
      openNotification: this.openNotification,
      startLoading: this.startLoading,
      stopLoading: this.stopLoading,
      submitButton: this.submitButtonConfig,
      submit: this.submit,
      success: this.success,
    })
  }
};
</script>

