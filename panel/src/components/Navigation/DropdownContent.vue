<template>
  <div
    v-if="isOpen"
    :data-align="align"
    :data-dropup="dropup"
    :data-theme="theme"
    class="k-dropdown-content"
  >
    <!-- @slot Content of the dropdown -->
    <slot>
      <template v-for="(option, index) in items">
        <hr v-if="option === '-'" :key="_uid + '-item-' + index" />
        <k-dropdown-item
          v-else
          :ref="_uid + '-item-' + index"
          :key="_uid + '-item-' + index"
          v-bind="option"
          @click="onOptionClick(option)"
        >
          {{ option.text }}
        </k-dropdown-item>
      </template>
    </slot>
  </div>
</template>

<script>
let OpenDropdown = null;

/**
 * See `<k-dropdown>` for how to use these components together.
 * @internal
 */
export default {
  props: {
    /**
     * Alignment of the dropdown items
     * @values left, right
     */
    align: {
      type: String,
      default: "left"
    },
    options: [Array, Function, String],
    /**
     * Visual theme of the dropdown
     * @values dark, light
     */
    theme: {
      type: String,
      default: "dark"
    }
  },
  data() {
    return {
      current: -1,
      dropup: false,
      isOpen: false,
      items: []
    };
  },
  methods: {
    async fetchOptions(ready) {
      if (this.options) {
        if (typeof this.options === "string") {
          this.$dropdown(this.options)(ready);
        } else if (typeof this.options === "function") {
          this.options(ready);
        } else if (Array.isArray(this.options)) {
          ready(this.options);
        }
      } else {
        return ready(this.items);
      }
    },
    onOptionClick(option) {
      if (typeof option.click === "function") {
        option.click.call(this);
      } else if (option.click) {
        this.$emit("action", option.click);
      }
    },
    /**
     * Opens the dropdown
     * @public
     */
    open() {
      this.reset();

      if (OpenDropdown && OpenDropdown !== this) {
        // close the current dropdown
        OpenDropdown.close();
      }

      this.fetchOptions((items) => {
        this.$events.$on("keydown", this.navigate);
        this.$events.$on("click", this.close);
        this.items = items;
        this.isOpen = true;
        OpenDropdown = this;
        this.onOpen();
        /**
         * When the dropdown content is opened
         * @event open
         */
        this.$emit("open");
      });
    },
    reset() {
      this.current = -1;
      this.$events.$off("keydown", this.navigate);
      this.$events.$off("click", this.close);
    },
    /**
     * Closes the dropdown
     * @public
     */
    close() {
      this.reset();
      this.isOpen = OpenDropdown = false;
      /**
       * When the dropdown content is closed
       * @event close
       */
      this.$emit("close");
    },
    /**
     * Toggles the open state of the dropdown
     * @public
     */
    toggle() {
      this.isOpen ? this.close() : this.open();
    },
    focus(n = 0) {
      if (this.$children[n]?.focus) {
        this.current = n;
        this.$children[n].focus();
      }
    },
    onOpen() {
      // disable dropup before calculate
      this.dropup = false;

      this.$nextTick(() => {
        if (this.$el) {
          // get window height depending on the browser
          let windowHeight =
            window.innerHeight ||
            document.body.clientHeight ||
            document.documentElement.clientHeight;

          // the minimum height required from above and below for the behavior of the dropup
          // k-topbar or form-buttons (2.5rem = 40px)
          // safe area height is slightly higher than that
          let safeSpaceHeight = 50;

          // dropdown content position relative to the viewport
          let scrollTop = this.$el.getBoundingClientRect().top || 0;

          // dropdown content height
          let dropdownHeight = this.$el.clientHeight;

          // activates the dropup if the dropdown content overflows
          // to the bottom of the screen but only if there is enough space top of screen
          if (
            scrollTop + dropdownHeight > windowHeight - safeSpaceHeight &&
            dropdownHeight + safeSpaceHeight * 2 < scrollTop
          ) {
            this.dropup = true;
          }
        }
      });
    },
    navigate(e) {
      /*eslint no-constant-condition: ["error", { "checkLoops": false }]*/
      switch (e.code) {
        case "Escape":
        case "ArrowLeft":
          this.close();
          this.$emit("leave", e.code);
          break;
        case "ArrowUp":
          e.preventDefault();

          while (true) {
            this.current--;

            if (this.current < 0) {
              this.close();
              this.$emit("leave", e.code);
              break;
            }

            if (
              this.$children[this.current] &&
              this.$children[this.current].disabled === false
            ) {
              this.focus(this.current);
              break;
            }
          }

          break;
        case "ArrowDown":
          e.preventDefault();

          while (true) {
            this.current++;

            if (this.current > this.$children.length - 1) {
              const enabled = this.$children.filter(
                (x) => x.disabled === false
              );
              this.current = this.$children.indexOf(
                enabled[enabled.length - 1]
              );
              break;
            }

            if (
              this.$children[this.current] &&
              this.$children[this.current].disabled === false
            ) {
              this.focus(this.current);
              break;
            }
          }

          break;
        case "Tab":
          while (true) {
            this.current++;

            if (this.current > this.$children.length - 1) {
              this.close();
              this.$emit("leave", e.code);
              break;
            }

            if (
              this.$children[this.current] &&
              this.$children[this.current].disabled === false
            ) {
              break;
            }
          }

          break;
      }
    }
  }
};
</script>

<style>
.k-dropdown-content {
  position: absolute;
  top: 100%;
  background: var(--color-black);
  color: var(--color-white);
  z-index: var(--z-dropdown);
  box-shadow: var(--shadow-lg);
  border-radius: var(--rounded-xs);
  text-align: start;
  margin-bottom: 6rem;
}
.k-dropdown-content[data-align="left"] {
  inset-inline-start: 0;
}
.k-dropdown-content[data-align="right"] {
  inset-inline-end: 0;
}
.k-dropdown-content > .k-dropdown-item:first-child {
  margin-top: 0.5rem;
}
.k-dropdown-content > .k-dropdown-item:last-child {
  margin-bottom: 0.5rem;
}

.k-dropdown-content[data-dropup="true"] {
  top: auto;
  bottom: 100%;
  margin-bottom: 0.5rem;
}

.k-dropdown-content hr {
  border-color: currentColor;
  opacity: 0.2;
  margin: 0.5rem 1rem;
}
.k-dropdown-content[data-theme="light"] {
  background: var(--color-white);
  color: var(--color-black);
}
</style>
