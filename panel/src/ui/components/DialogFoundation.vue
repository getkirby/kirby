<template>
  <k-overlay ref="overlay" :visible="visible">
    <k-modal
      ref="modal"
      :autofocus="autofocus"
      :cancel-button="cancelButtonConfig"
      :loading="loading"
      :submit-button="submitButtonConfig"
      @cancel="cancel"
      @submit="submit"
    >
      <k-backdrop
        :dir="$direction"
        :data-size="size"
        :data-loading="loading"
        slot-scope="{
          cancel,
          cancelButton,
          closeNotification,
          notification,
          submit,
          submitButton
        }"
        class="k-dialog flex items-center justify-center"
        @click="cancel"
      >
        <div
          class="k-dialog-box relative m-6 bg-light rounded-sm shadow-md"
          @click.stop
        >
          <k-notification
            v-if="notification"
            v-bind="notification"
            class="k-dialog-notification px-3"
            @close="closeNotification()"
          />
          <div class="k-dialog-body p-6">
            <slot>
              <k-text v-html="text" />
            </slot>
          </div>
          <slot
            name="footer"
            :cancel="cancel"
            :cancelButton="cancelButton"
            :submitButton="submitButton"
            :submit="submit"
          >
            <footer
              v-if="cancelButton || submitButton"
              class="k-dialog-footer flex justify-between"
            >
              <k-button
                v-if="cancelButton"
                v-bind="cancelButton"
                class="k-dialog-cancel-button mr-auto py-3 px-6"
                @click="cancel"
              >
                {{ cancelButton.text }}
              </k-button>
              <k-button
                v-if="submitButton"
                v-bind="submitButton"
                class="k-dialog-submit-button ml-auto py-3 px-6"
                @click="submit"
              >
                {{ submitButton.text }}
              </k-button>
            </footer>
          </slot>
        </div>
      </k-backdrop>
    </k-modal>
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
      type: [Boolean, Object, String],
      default: true
    },
    loading: {
      type: Boolean,
      default: false,
    },
    /**
     * Available options: `small`|`default`|`medium`|`large`
     */
    size: {
      type: String,
      default: "default"
    },
    submitButton: {
      type: [Boolean, Object, String],
      default: true
    },
    text: {
      type: String
    },
    visible: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    /**
     * Supports deprecated cancel button config
     * with the cancel attribute
     */
    cancelButtonConfig() {
      return this.$attrs["cancel"] || this.cancelButton;
    },
    /**
     * Supports deprecated submit button config
     * with the icon, theme and button attributes
     */
    submitButtonConfig() {
      let button   = this.$attrs["button"] || this.submitButton;
      let defaults = {
        icon: this.$attrs["icon"] || "check",
        text: this.$t("confirm"),
        color: this.$attrs["theme"]
      };

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

      return this.$attrs["button"] || this.submitButton;
    }
  },
  methods: {
    cancel() {
      if (this.loading) {
        return false;
      }

      this.$emit("cancel");
      this.close();
    },
    close() {
      if (this.loading) {
        return false;
      }

      this.$emit("close");
      this.$refs.overlay.close();
    },
    error(message) {
      this.$refs.modal.error(message);
    },
    open() {
      this.$refs.overlay.open();
    },
    submit() {
      if (this.loading) {
        return false;
      }

      this.$emit("submit");
    },
    success(message) {
      this.$refs.modal.success(message);
    }
  }
};
</script>

<style lang="scss">
.k-dialog-box {
  width: 100%;
  line-height: 1;
  max-height: calc(100vh - 3rem);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

@media screen and (min-width: 20rem) {
  .k-dialog[data-size="small"] .k-dialog-box {
    width: 20rem;
  }
}

@media screen and (min-width: 22rem) {
  .k-dialog[data-size="default"] .k-dialog-box {
    width: 22rem;
  }
}

@media screen and (min-width: 30rem) {
  .k-dialog[data-size="medium"] .k-dialog-box {
    width: 30rem;
  }
}

@media screen and (min-width: 40rem) {
  .k-dialog[data-size="large"] .k-dialog-box {
    width: 40rem;
  }
}

/** Sections **/
.k-dialog-header,
.k-dialog-footer {
  padding: 0;
  line-height: 1;
  flex-shrink: 0;
  border-color: lighten($color-border, 8%);
  height: 2.5rem;
}
.k-dialog-body {
  overflow-y: auto;
  overflow-x: hidden;
  flex-grow: 1;
}
.k-dialog-header {
  border-bottom-width: 1px;
}
.k-dialog-footer {
  border-top-width: 1px;
}

/** Notification **/
.k-dialog .k-dialog-notification {
  border-top-left-radius: $rounded-sm;
  border-top-right-radius: $rounded-sm;
  margin-top: -3px;
}

/** Pagination **/
.k-dialog .k-dialog-pagination {
  margin-bottom: -1.5rem;
}

/** Dialog search field **/
.k-dialog .k-dialog-search {
  margin-bottom: .75rem;
}
.k-dialog .k-dialog-search.k-input {
  background: rgba(#000, .075);
  padding: 0 1rem;
  height: 36px;
  border-radius: $rounded-sm;
}
</style>
