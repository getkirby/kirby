<template>
  <k-overlay ref="overlay" :visible="visible">
    <k-backdrop
      :data-size="size"
      class="k-dialog flex items-center justify-center"
      @click="cancel"
    >
      <k-modal
        ref="modal"
        :cancel-button="cancelButton"
        :submit-button="submitButton"
        class="k-dialog-box relative m-6 bg-light rounded-sm shadow-md"
      >
        <template slot-scope="{
          cancelButton,
          closeNotification,
          notification,
          submitButton
        }">
          <k-notification
            v-if="notification"
            v-bind="notification"
            class="k-dialog-notification px-3"
            @close="closeNotification()"
          />
          <div class="k-dialog-body p-6">
            <slot>
              <k-text>{{ text }}</k-text>
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
        </template>
      </k-modal>
    </k-backdrop>
  </k-overlay>
</template>

<script>
export default {
  props: {
    cancelButton: {
      type: [Boolean, Object, String],
      default: true
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
  methods: {
    cancel() {
      this.$emit("cancel");
      this.close();
    },
    close() {
      this.$emit("close");
      this.$refs.overlay.close();
    },
    error(message) {
      this.$refs.modal.error(message);
    },
    focus() {
      this.$refs.modal.focus();
    },
    open() {
      this.$refs.overlay.open();
    },
    submit() {
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
</style>
