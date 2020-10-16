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
      icon: {
        type: String,
        default: "check"
      },
      size: {
        type: String,
        default: "default"
      },
      submitButton: {
        type: [String, Boolean],
        default: true
      },
      theme: String,
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
      open() {
        this.$store.dispatch("dialog", true);
        this.notification = null;
        this.$refs.overlay.open();
        this.$emit("open");
        this.$events.$on("keydown.esc", this.close);
      },
      close() {
        this.notification = null;
        this.$refs.overlay.close();
        this.$emit("close");
        this.$events.$off("keydown.esc", this.close);
        this.$store.dispatch("dialog", false);
      },
      cancel() {
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
      error(message) {
        this.notification = {
          message: message,
          type: "error"
        };
      },
      submit() {
        this.$emit("submit");
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

<style lang="scss">
.k-dialog {
  position: relative;
  background: $color-light;
  width: 100%;
  box-shadow: $shadow-lg;
  border-radius: $rounded-xs;
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
  padding: 0.75rem 1.5rem;
  background: $color-gray-900;
  width: 100%;
  line-height: 1.25rem;
  color: $color-white;
  display: flex;
  flex-shrink: 0;
  align-items: center;
}

.k-dialog-notification[data-theme="error"] {
  background: $color-negative-on-dark;
  color: $color-black;
}

.k-dialog-notification[data-theme="success"] {
  background: $color-positive-on-dark;
  color: $color-black;
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
  padding-bottom: 0.5rem;
}

.k-dialog-footer {
  border-top: 1px solid $color-border;
  padding: 0;
  border-bottom-left-radius: $rounded-xs;
  border-bottom-right-radius: $rounded-xs;
  line-height: 1;
  flex-shrink: 0;
}

.k-dialog-footer .k-button-group {
  display: flex;
  margin: 0;
  justify-content: space-between;

  .k-button {
    padding: 0.75rem 1rem;
    line-height: 1.25rem;
  }

  .k-button:first-child {
    text-align: left;
    padding-left: 1.5rem;
  }

  .k-button:last-child {
    text-align: right;
    padding-right: 1.5rem;
  }
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
  background: rgba(#000, .075);
  padding: 0 1rem;
  height: 36px;
  border-radius: $rounded-xs;
}
</style>
