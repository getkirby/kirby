<template>
  <div v-if="isOpen" :data-align="align" class="k-dropdown-content">
    <!-- @slot Content of the dropdown -->
    <slot>
      <template v-for="(option, index) in items">
        <hr v-if="option === '-'" :key="_uid + '-item-' + index">
        <k-dropdown-item
          v-else
          :ref="_uid + '-item-' + index"
          :key="_uid + '-item-' + index"
          v-bind="option"
          @click="$emit('action', option.click)"
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
    options: [Array, Function],
    /**
     * Aligment of the dropdown items
     * @values left, right
     */
    align: {
      type: String,
      default: "left"
    }
  },
  data() {
    return {
      items: [],
      current: -1,
      isOpen: false
    };
  },
  methods: {
    async fetchOptions(ready) {
      if (this.options) {
        if (typeof this.options === "string") {
          const response = await fetch(this.options);
          const json = await response.json();
          return ready(json);

        } else if (typeof this.options === "function") {
          this.options(ready);

        } else if (Array.isArray(this.options)) {
          ready(this.options);
        }
      } else {
        return ready(this.items);
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

      this.fetchOptions(items => {
        this.$events.$on("keydown", this.navigate);
        this.$events.$on("click", this.close);
        this.items = items;
        this.isOpen = true;
        OpenDropdown = this;
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
      if (this.$children[n] && this.$children[n].focus) {
        this.current = n;
        this.$children[n].focus();
      }
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
              const enabled = this.$children.filter(x => x.disabled === false);
              this.current = this.$children.indexOf(enabled[enabled.length - 1]);
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
  background: var(--color-gray-900);
  color: var(--color-white);
  z-index: var(--z-dropdown);
  box-shadow: var(--shadow-lg);
  border-radius: var(--rounded-xs);
  text-align: left;
  margin-bottom: 6rem;
}
[dir="ltr"] .k-dropdown-content {
  left: 0;
}

[dir="rtl"] .k-dropdown-content {
  right: 0;
}
[dir="ltr"] .k-dropdown-content[data-align="right"] {
  left: auto;
  right: 0;
}
[dir="rtl"] .k-dropdown-content[data-align="right"] {
  left: 0;
  right: auto;
}
.k-dropdown-content > .k-dropdown-item:first-child {
  margin-top: .5rem;
}
.k-dropdown-content > .k-dropdown-item:last-child {
  margin-bottom: .5rem;
}
.k-dropdown-content hr {
  position: relative;
  padding: .5rem 0;
  border: 0;
}
.k-dropdown-content hr::after {
  position: absolute;
  top: .5rem;
  left: 1rem;
  right: 1rem;
  content: "";
  height: 1px;
  background: currentColor;
  opacity: .2;
}
</style>
