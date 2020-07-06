<template>
  <div v-if="isOpen" class="k-dialog" @mousedown="cancel">
    <div :data-size="size" class="k-dialog-box" @mousedown.stop>
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
  </div>
</template>

<script>
  export default {
    props: {
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
        notification: null,
        isOpen: this.visible,
        scrollTop: 0
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
      if (this.isOpen === true) {
        this.$emit("open");
      }
    },
    methods: {
      storeScrollPosition() {
        const view = document.querySelector(".k-panel-view");

        if (view && view.scrollTop) {
          this.scrollTop = view.scrollTop;
        } else {
          this.scrollTop = 0;
        }
      },
      restoreScrollPosition() {
        const view = document.querySelector(".k-panel-view");

        if (view && view.scrollTop) {
          view.scrollTop = this.scrollTop;
        }
      },
      open() {
        this.storeScrollPosition();
        this.$store.dispatch("dialog", true);
        this.notification = null;
        this.isOpen = true;
        this.$emit("open");
        this.$events.$on("keydown.esc", this.close);

        this.$nextTick(() => {
          if (this.$el) {
            // focus on the first useful element
            this.focus();

            // blur trap
            document.body.addEventListener(
              "focus",
              e => {
                if (this.$el.contains(e.target) === false) {
                  this.focus();
                }
              },
              true
            );
          }
        });
      },
      close() {
        this.notification = null;
        this.isOpen = false;
        this.$emit("close");
        this.$events.$off("keydown.esc", this.close);
        this.$store.dispatch("dialog", null);
        this.restoreScrollPosition();
      },
      cancel() {
        this.$emit("cancel");
        this.close();
      },
      focus() {
        if (this.$el && this.$el.querySelector) {
          let autofocus = this.$el.querySelector(
            "[autofocus], [data-autofocus], input, textarea, select, .k-dialog-button-submit"
          );

          if (!autofocus) {
            autofocus = this.$el.querySelector(".k-dialog-button-cancel");
          }

          if (autofocus) {
            autofocus.focus();
            return;
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
  display: flex;
  align-items: center;
  justify-content: center;

  position: fixed;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  border: 0;

  width: 100%;
  height: 100%;

  background: $color-backdrop;
  z-index: z-index(dialog);
  transform: translate3d(0, 0, 0);
}

.k-dialog-box {
  position: relative;
  background: $color-light;
  width: 100%;
  box-shadow: $box-shadow;
  border-radius: $border-radius;
  line-height: 1;
  max-height: calc(100vh - 3rem);
  margin: 1.5rem;
  display: flex;
  flex-direction: column;
}

@media screen and (min-width: 20rem) {
  .k-dialog-box[data-size="small"] {
    width: 20rem;
  }
}

@media screen and (min-width: 22rem) {
  .k-dialog-box[data-size="default"] {
    width: 22rem;
  }
}

@media screen and (min-width: 30rem) {
  .k-dialog-box[data-size="medium"] {
    width: 30rem;
  }
}

@media screen and (min-width: 40rem) {
  .k-dialog-box[data-size="large"] {
    width: 40rem;
  }
}

.k-dialog-notification {
  padding: 0.75rem 1.5rem;
  background: $color-dark;
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
  border-bottom-left-radius: $border-radius;
  border-bottom-right-radius: $border-radius;
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
  border-radius: $border-radius;
}
</style>
