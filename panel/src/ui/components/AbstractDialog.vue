<template>
  <portal v-if="isOpen">
    <div
      :dir="$direction"
      :data-loading="loading"
      :data-size="size"
      class="k-dialog fixed flex items-center justify-center bg-backdrop"
      @click="cancel"
    >
      <div
        ref="box"
        class="k-dialog-box relative m-6 bg-light rounded-sm shadow-md"
        @click.stop
      >
        <header
          v-if="$slots['header']"
          class="k-dialog-header"
        >
          <slot name="header" />
        </header>
        <div class="k-dialog-body p-6">
          <slot />
        </div>
        <footer
          v-if="$slots['footer'] || cancelButtonConfig || submitButtonConfig"
          class="k-dialog-footer"
        >
          <slot name="footer">
            <div class="flex justify-between">
              <k-button
                v-if="cancelButtonConfig"
                v-bind="cancelButtonConfig"
                class="k-dialog-button k-dialog-button-cancel"
                @click="cancel"
              >
                {{ cancelButtonConfig.text }}
              </k-button>
              <k-button
                v-if="submitButtonConfig"
                v-bind="submitButtonConfig"
                class="k-dialog-button k-dialog-button-submit"
                @click="submit"
              >
                {{ submitButtonConfig.text }}
              </k-button>
            </div>
          </slot>
        </footer>
      </div>
    </div>
  </portal>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    autofocus: {
      type: Boolean,
      default: true
    },
    cancelButton: {
      type: [Boolean, Object, String],
      default: true
    },
    icon: {
      type: String,
      default: "check"
    },
    loading: {
      type: Boolean,
      default: false
    },
    submitButton: {
      type: [Boolean, Object, String],
      default: true
    },
    /**
     * Legacy: use submitButton color instead
     */
    theme: String,
  },
  computed: {
    cancelButtonConfig() {
      return this.buttonConfig("cancelButton", "cancel", {
        icon: "cancel",
        text: this.$t("cancel"),
      });
    },
    submitButtonConfig() {
      return this.buttonConfig("submitButton", "button", {
        icon: this.icon || "check",
        text: this.$t("confirm"),
        color: this.theme
      });
    }
  },
  methods: {
    buttonConfig(prop, attr, defaults) {
      let button = this.$attrs[attr] || this.$props[prop];

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
    open() {
      this.notification = null;
    },
    close() {
      if (this.loading) {
        return false;
      }

      this.notification = null;
    },
    cancel() {
      if (this.loading) {
        return false;
      }

      this.$emit("cancel");
      this.close();
    },
    error(message) {
      this.notification = {
        message: message,
        type: "error"
      };
    },
    submit() {
      if (this.loading) {
        return false;
      }

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



/** Pagination **/
.k-dialog-pagination {
  margin-bottom: -1.5rem;
}

/** Dialog search field **/
.k-dialog-search {
  margin-bottom: .75rem;
}

.k-dialog-search.k-input {
  background: rgba(#000, .075);
  padding: 0 1rem;
  height: 36px;
  border-radius: $rounded-sm;
}
</style>
