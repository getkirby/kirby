<template>
  <div
    v-if="isOpen"
    :data-align="align"
    :data-theme="theme"
    class="k-dropdown-content absolute mb-24 bg-black text-white rounded-sm shadow-lg"
  >
    <slot>
      <template v-for="(option, index) in items">
        <hr
          v-if="option === '-'"
          :key="_uid + '-item-' + index"
        >
        <k-dropdown-item
          v-else
          :ref="_uid + '-item-' + index"
          :key="_uid + '-item-' + index"
          v-bind="option"
          @click="onClick(option.click, option, index)"
        >
          {{ option.text }}
        </k-dropdown-item>
      </template>
    </slot>
  </div>
</template>

<script>
let OpenDropdown = null;

export default {
  props: {
    align: {
      type: String,
      default: "left"
    },
    options: [Array, Function],
    theme: {
      type: String,
      default: "dark"
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
    fetchOptions(ready) {
      if (this.options) {
        if (typeof this.options === "string") {
          fetch(this.options)
            .then(response => response.json())
            .then(json => {
              return ready(json);
            });
        } else if (typeof this.options === "function") {
          this.options(ready);
        } else if (Array.isArray(this.options)) {
          ready(this.options);
        }
      } else {
        return ready(this.items);
      }
    },
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
        this.$emit("open");
        OpenDropdown = this;
      });
    },
    reset() {
      this.current = -1;
      this.$events.$off("keydown", this.navigate);
      this.$events.$off("click", this.close);
    },
    close() {
      this.reset();
      this.isOpen = OpenDropdown = false;
      this.$emit("close");
    },
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
    },
    onClick(click, option, optionIndex) {
      // legacy
      this.$emit('action', click, option, optionIndex);
      this.$emit('option', click, option, optionIndex);
    }
  }
};
</script>

<style lang="scss">
.k-dropdown-content {
  top: 100%;
  text-align: left;
  z-index: z-index(dropdown);

  [dir="ltr"] & {
    left: 0;
  }

  [dir="rtl"] & {
    right: 0;
  }

}

.k-dropdown-content[data-align="center"] {
  left: 50%;
  transform: translateX(-50%);
}

.k-dropdown-content[data-align="right"] {
  [dir="ltr"] & {
    left: auto;
    right: 0;
  }

  [dir="rtl"] & {
    left: 0;
    right: auto;
  }
}


.k-dropdown-content > .k-dropdown-item:first-child {
  margin-top: .5rem;
}
.k-dropdown-content > .k-dropdown-item:last-child {
  margin-bottom: .5rem;
}
.k-dropdown-content hr {
  border-color: currentColor;
  opacity: 0.2;
  margin: .5rem 1rem;
}
.k-dropdown-content[data-theme="light"] {
  background: $color-white;
  color: $color-black;
}
.k-dropdown-content[data-theme="light"] hr {
  opacity: 0.1;
}
</style>
