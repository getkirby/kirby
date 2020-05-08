<template>
  <k-focus-boundary
    ref="modal"
    :dir="$direction"
    class="k-modal"
    @click.stop
  >
    <slot
      :cancel="cancel"
      :cancelButton="cancelButtonConfig"
      :closeNotification="closeNotification"
      :error="error"
      :notification="notification"
      :submitButton="submitButtonConfig"
      :submit="submit"
      :success="success"
    />
  </k-focus-boundary>
</template>

<script>
export default {
  props: {
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
      notification: false
    };
  },
  mounted() {
    // autofocus
    this.$refs.modal.focus();
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
      this.notification = {
        message: message,
        type: "error"
      };
    },
    submit(event) {
      this.$emit("submit", event);
    },
    success(message) {
      this.notification = {
        message: message,
        type: "success"
      };
    }
  }
};
</script>

