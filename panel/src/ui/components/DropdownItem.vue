<template>
  <k-button
    ref="button"
    v-bind="$props"
    class="k-dropdown-item flex items-center text-left text-sm px-5"
    v-on="listeners"
  >
    <slot />
  </k-button>
</template>

<script>
/**
 * k-dropdown-item elements inherit all the attributes from k-button elements, like link or icon
 */
export default {
  inheritAttrs: false,
  props: {
    color: String,
    current: [Boolean, String],
    disabled: Boolean,
    icon: String,
    link: String,
    target: String,
    theme: String,
    upload: String,
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
.k-dropdown-item {
  white-space: nowrap;
  width: 100%;
  padding: 6px 16px;

  &:focus {
    @include focus-ring;
  }
}

.k-dropdown-item .k-button-figure {
  text-align: center;
  padding-right: 0.5rem;
}
</style>
