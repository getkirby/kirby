<template>
  <kirby-button
    ref="button"
    v-bind="$props"
    class="kirby-dropdown-item"
    v-on="listeners"
  >
    <slot />
  </kirby-button>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    disabled: Boolean,
    icon: String,
    image: [String, Object],
    link: String,
    target: String,
    upload: String,
    current: [String, Boolean]
  },
  data() {
    return {
      listeners: {
        ...this.$listeners,
        click: (event) => {
          this.$parent.close();
          this.$emit("click", event);
        }
      }
    }
  },
  methods: {
    focus() {
      this.$refs.button.focus();
    },
    tab() {
      this.$refs.button.tab();
    }
  }
};
</script>

<style lang="scss">
.kirby-dropdown-item {
  white-space: nowrap;
  line-height: 1;
  display: flex;
  width: 100%;
  align-items: center;
  font-size: $font-size-small;
  padding: 6px 16px;

  &:focus {
    @include focus-ring;
  }
}

.kirby-dropdown-item .kirby-button-figure {
  text-align: center;
  padding-right: 0.5rem;
}
</style>
