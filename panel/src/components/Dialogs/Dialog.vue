<template>
  <k-overlay
    ref="overlay"
    :autofocus="autofocus"
    :centered="true"
    @ready="$emit('ready')"
  >
    <div
      ref="dialog"
      :data-size="size"
      :class="$vnode.data.staticClass"
      class="k-dialog"
      @mousedown.stop
    >
      <div v-if="notification" :data-theme="notification.type" class="k-dialog-notification">
        <p>{{ notification.message }}</p>
        <k-button icon="cancel" @click="notification = null" />
      </div>

      <div class="k-dialog-body">
        <slot />
      </div>

      <footer v-if="$slots['footer'] || cancelButton || submitButton" class="k-dialog-footer">
        <slot name="footer">
          <k-button-group>
            <span>
              <k-button
                v-if="cancelButton"
                icon="cancel"
                class="k-dialog-button-cancel"
                @click="cancel"
              >
                {{ cancelButtonLabel }}
              </k-button>
            </span>
            <span>
              <k-button
                v-if="submitButtonConfig"
                :icon="icon"
                :theme="theme"
                class="k-dialog-button-submit"
                @click="submit"
              >
                {{ submitButtonLabel }}
              </k-button>
            </span>
          </k-button-group>
        </slot>
      </footer>
    </div>
  </k-overlay>
</template>

<script>
/**
 * Modal dialogs are used in Kirby's Panel in many places for quick actions like adding new pages, changing titles, etc. that don't necessarily need a full new view. You can create your own modals for your fields and other plugins or reuse our existing modals to invoke typical Panel actions.
 */
export default {
  props: {
    autofocus: {
      type: Boolean,
      default: true
    },
    cancelButton: {
      type: [String, Boolean],
      default: true,
    },
    /**
     * The icon type for the submit button
     */
    icon: {
      type: String,
      default: "check"
    },
    /**
     * The modal size can be adjusted with the size prop
     * @values small, medium, large
     */
    size: {
      type: String,
      default: "default"
    },
    /**
     * The text for the submit button
     */
    submitButton: {
      type: [String, Boolean],
      default: true
    },
    /**
     * The theme of the submit button
     * @values positive, negative
     */
    theme: String,
    /**
     * Dialogs are only openend on demand with the `open()` method. If you need a dialog that's visible on creation, you can set the `visible` prop
     */
    visible: Boolean
  },
  data() {
    return {
      notification: null
    };
  },
  computed: {
    cancelButtonLabel() {
      if (this.cancelButton === false) {
        return false;
      }

      if (this.cancelButton === true || this.cancelButton.length === 0) {
        return this.$t("cancel");
      }

      return this.cancelButton;
    },
    submitButtonConfig() {

      if (this.$attrs["button"] !== undefined) {
        return this.$attrs["button"];
      }

      if (this.submitButton !== undefined) {
        return this.submitButton;
      }

      return true;
    },
    submitButtonLabel() {
      if (this.submitButton === true || this.submitButton.length === 0) {
        return this.$t("confirm");
      }

      return this.submitButton;
    }
  },
  created() {
    this.$events.$on("keydown.esc", this.close, false);
  },
  destroyed() {
    this.$events.$off("keydown.esc", this.close, false);
  },
  mounted() {
    if (this.visible) {
      this.$nextTick(this.open);
    }
  },
  methods: {
    /**
     * Opens the dialog and triggers the `@open` event
     * @public
     */
    open() {
      this.$store.dispatch("dialog", true);
      this.notification = null;
      this.$refs.overlay.open();
      /**
       * This event is triggered as soon as the dialog opens.
       * @event open
       */
      this.$emit("open");
      this.$events.$on("keydown.esc", this.close);
    },
    /**
     * Triggers the `@close` event and closes the dialog.
     * @public
     */
    close() {
      this.notification = null;
      if (this.$refs.overlay) {
        this.$refs.overlay.close();
      }
      /**
       * This event is triggered when the dialog is being closed. 
       * This happens independently from the cancel event.
       * @event close
       */
      this.$emit("close");
      this.$events.$off("keydown.esc", this.close);
      this.$store.dispatch("dialog", false);
    },
    /**
     * Triggers the `@cancel` event and closes the dialog.
     * @public
     */
    cancel() {
      /**
       * This event is triggered whenever the cancel button or 
       * the backdrop is clicked.
       * @event cancel
       */
      this.$emit("cancel");
      this.close();
    },
    focus() {
      if (this.$refs.dialog && this.$refs.dialog.querySelector) {
        const btn = this.$refs.dialog.querySelector(".k-dialog-button-cancel");

        if (btn && typeof btn.focus === "function") {
          btn.focus();
        }
      }
    },
    /**
     * Shows the error notification bar in the dialog with the given message
     * @public
     * @param {string} message
     */
    error(message) {
      this.notification = {
        message: message,
        type: "error"
      };
    },
    submit() {
      /**
       * This event is triggered when the submit button is clicked.
       * @event submit
       */
      this.$emit("submit");
    },
    /**
     * Shows the success notification bar in the dialog with the given message
     * @public
     * @param {string} message
     */
    success(message) {
      this.notification = {
        message: message,
        type: "success"
      };
    }
  }
};
</script>

<style>
.k-dialog {
  position: relative;
  background: var(--color-background);
  width: 100%;
  box-shadow: var(--shadow-lg);
  border-radius: var(--rounded-xs);
  line-height: 1;
  max-height: calc(100vh - 3rem);
  margin: 1.5rem;
  display: flex;
  flex-direction: column;
}

@media screen and (min-width: 20rem) {
  .k-dialog[data-size="small"] {
    width: 20rem;
  }
}

@media screen and (min-width: 22rem) {
  .k-dialog[data-size="default"] {
    width: 22rem;
  }
}

@media screen and (min-width: 30rem) {
  .k-dialog[data-size="medium"] {
    width: 30rem;
  }
}

@media screen and (min-width: 40rem) {
  .k-dialog[data-size="large"] {
    width: 40rem;
  }
}

.k-dialog-notification {
  padding: .75rem 1.5rem;
  background: var(--color-gray-900);
  width: 100%;
  line-height: 1.25rem;
  color: var(--color-white);
  display: flex;
  flex-shrink: 0;
  align-items: center;
}

.k-dialog-notification[data-theme="error"] {
  background: var(--color-negative-light);
  color: var(--color-black);
}

.k-dialog-notification[data-theme="success"] {
  background: var(--color-positive-light);
  color: var(--color-black);
}

.k-dialog-notification p {
  flex-grow: 1;
  word-wrap: break-word;
  overflow: hidden;
}

.k-dialog-notification .k-button {
  display: flex;
  margin-left: 1rem;
}

.k-dialog-body {
  padding: 1.5rem;
  overflow-y: auto;
  overflow-x: hidden;
}

.k-dialog-body .k-fieldset {
  padding-bottom: .5rem;
}

.k-dialog-footer {
  border-top: 1px solid var(--color-gray-300);
  padding: 0;
  border-bottom-left-radius: var(--rounded-xs);
  border-bottom-right-radius: var(--rounded-xs);
  line-height: 1;
  flex-shrink: 0;
}

.k-dialog-footer .k-button-group {
  display: flex;
  margin: 0;
  justify-content: space-between;

}
.k-dialog-footer .k-button-group .k-button {
  padding: .75rem 1rem;
  line-height: 1.25rem;
}

.k-dialog-footer .k-button-group .k-button:first-child {
  text-align: left;
  padding-left: 1.5rem;
}
.k-dialog-footer .k-button-group .k-button:last-child {
  text-align: right;
  padding-right: 1.5rem;
}

/** Pagination **/
.k-dialog-pagination {
  margin-bottom: -1.5rem;
  display: flex;
  justify-content: center;
  align-items: center;
}

/** Dialog search field **/
.k-dialog-search {
  margin-bottom: .75rem;
}

.k-dialog-search.k-input {
  background: rgba(0, 0, 0, .075);
  padding: 0 1rem;
  height: 36px;
  border-radius: var(--rounded-xs);
}
</style>
