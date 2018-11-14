<template>
  <div v-if="isOpen" :data-align="align" class="k-dropdown-content">
    <slot>
      <k-dropdown-item
        v-for="(option, index) in items"
        :ref="_uid + '-item-' + index"
        :key="_uid + '-item-' + index"
        v-bind="option"
        @click="$emit('action', option.click)"
      >
        {{ option.text }}
      </k-dropdown-item>
    </slot>
  </div>
</template>

<script>
let OpenDropdown = null;

export default {
  props: {
    options: [Array, Function],
    align: String
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
    focus(n) {
      n = n || 0;
      if (this.$children[n] && this.$children[n].focus) {
        this.current = n;
        this.$children[n].focus();
      }
    },
    navigate(e) {

      switch (e.code) {
        case "Escape":
        case "ArrowLeft":
          this.close();
          this.$emit("leave", e.code);
          break;
        case "ArrowUp":
          e.preventDefault();
          if (this.current > 0) {
            this.current--;
            this.focus(this.current);
          } else {
            this.close();
            this.$emit("leave", e.code);
          }
          break;
        case "ArrowDown":
          e.preventDefault();

          if (this.current < this.$children.length - 1) {
            this.current++;
            this.focus(this.current);
          }

          break;
      }
    }
  }
};
</script>

<style lang="scss">
.k-dropdown-content {
  position: absolute;
  top: 100%;
  background: $color-dark;
  color: $color-white;
  z-index: z-index(dropdown);
  box-shadow: $box-shadow;
  border-radius: $border-radius;
  text-align: left;

  [dir="ltr"] & {
    left: 0;
  }

  [dir="rtl"] & {
    right: 0;
  }

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
  position: relative;
  padding: 0.5rem 0;
  border: 0;

  &::after {
    position: absolute;
    top: 0.5rem;
    left: 1rem;
    right: 1rem;
    content: "";
    height: 1px;
    background: currentColor;
    opacity: 0.2;
  }
}
</style>
