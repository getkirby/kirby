<template>
  <transition name="kirby-dialog-transition">
    <div v-if="isOpen" class="kirby-dialog" @click="cancel">
      <div :data-size="size" class="kirby-dialog-box" @click.stop>

        <div v-if="notification" :data-theme="notification.type" class="kirby-dialog-notification">
          <p>{{ notification.message }}</p>
          <kirby-button
            icon="cancel"
            @click="notification = null"
          />
        </div>

        <div class="kirby-dialog-body">
          <slot/>
        </div>
        <slot name="footer">
          <footer class="kirby-dialog-footer">
            <kirby-button-group>
              <kirby-button icon="cancel" @click="cancel">
                {{ "Cancel" | t("cancel") }}
              </kirby-button>
              <kirby-button :icon="icon" :theme="theme" @click="submit">
                {{ button | t("confirm") }}
              </kirby-button>
            </kirby-button-group>
          </footer>
        </slot>
      </div>
    </div>
  </transition>
</template>

<script>
export default {
  props: {
    button: {
      type: String,
      default: "Ok"
    },
    icon: {
      type: String,
      default: "check"
    },
    size: String,
    theme: String,
    visible: Boolean,
  },
  data() {
    return {
      notification: null,
      isOpen: this.visible,
    };
  },
  mounted() {
    if (this.isOpen === true) {
      this.$emit("open");
    }
  },
  methods: {
    open() {
      this.notification = null;
      this.isOpen = true;
      this.$emit("open");
      this.$events.$on("keydown.esc", this.close);

      this.$nextTick(() => {
        var autofocus = this.$el.querySelector(
          "input, textarea, select, button:not([data-options])"
        );
        if (autofocus) {
          autofocus.focus();
        }
      });
    },
    close() {
      this.notification = null;
      this.isOpen = false;
      this.$emit("close");
      this.$events.$off("keydown.esc", this.close);
    },
    cancel() {
      this.$emit("cancel");
      this.close();
    },
    error(message) {
      this.notification = {
        message: message,
        type: "error",
      };
    },
    submit() {
      this.$emit("submit");
    },
    success(message) {
      this.notification = {
        message: message,
        type: "success",
      };
    }
  }
};
</script>

<style lang="scss">
.kirby-dialog {
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
}

.kirby-dialog-transition-enter-active,
.kirby-dialog-transition-leave-active {
  transition: opacity 0.15s;
}
.kirby-dialog-transition-enter,
.kirby-dialog-transition-leave-to {
  opacity: 0;
}

.kirby-dialog-transition-enter-active .kirby-dialog-box,
.kirby-dialog-transition-leave-active .kirby-dialog-box {
  transition: transform 0.2s;
}
.kirby-dialog-transition-enter .kirby-dialog-box,
.kirby-dialog-transition-leave-to .kirby-dialog-box {
  transform: translateY(-5%);
}

.kirby-dialog-box {
  position: relative;
  background: $color-light;
  width: 22rem;
  box-shadow: $box-shadow;
  border-radius: $border-radius;
  line-height: 1;
  max-height: calc(100vh - 3rem);
  margin: 1.5rem;
}
.kirby-dialog-box[data-size="small"] {
  width: 20rem;
}
.kirby-dialog-box[data-size="medium"] {
  width: 30rem;
}
.kirby-dialog-box[data-size="large"] {
  width: 40rem;
}
.kirby-dialog-notification {
  padding: .75rem 1.5rem;
  background: $color-dark;
  width: 100%;
  line-height: 1.25rem;
  color: $color-white;
  display: flex;
  align-items: center;
}
.kirby-dialog-notification[data-theme="error"] {
  background: $color-negative-on-dark;
  color: $color-black;
}
.kirby-dialog-notification[data-theme="success"] {
  background: $color-positive-on-dark;
  color: $color-black;
}
.kirby-dialog-notification p {
  flex-grow: 1;
  word-wrap: break-word;
  overflow: hidden;
}
.kirby-dialog-notification .kirby-button {
  display: flex;
  margin-left: 1rem;
}


.kirby-dialog-body {
  padding: 1.5rem;
  max-height: calc(100vh - 9rem);
  overflow-y: auto;
  overflow-x: hidden;
}
.kirby-dialog-body .kirby-fieldset {
  padding-bottom: 0.5rem;
}
.kirby-dialog-footer {
  border-top: 1px solid $color-border;
  padding: 0;
  border-bottom-left-radius: $border-radius;
  border-bottom-right-radius: $border-radius;
  line-height: 1;
}
.kirby-dialog-footer .kirby-button-group {
  display: flex;
  margin: 0;
  justify-content: space-between;

  .kirby-button {
    padding: .75rem 1rem;
    line-height: 1.25rem;
  }

  .kirby-button:first-child {
    text-align: left;
    padding-left: 1.5rem;
  }
  .kirby-button:last-child {
    text-align: right;
    padding-right: 1.5rem;
  }
}
</style>
