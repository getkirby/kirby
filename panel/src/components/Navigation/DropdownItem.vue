<template>
  <k-button
    ref="button"
    v-bind="$props"
    class="k-dropdown-item"
    v-on="listeners"
  >
    <!-- @slot The item's content/text -->
    <slot />
  </k-button>
</template>

<script>
/**
 * Item to be used within `<k-dropdown-content>`
 * @example <k-dropdown-item>Option A</k-dropdown-item>
 * @internal
 */
export default {
  inheritAttrs: false,
  props: {
    disabled: Boolean,
    icon: String,
    image: [String, Object],
    link: String,
    target: String,
    theme: String,
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

<style>
.k-dropdown-item {
  white-space: nowrap;
  line-height: 1;
  display: flex;
  width: 100%;
  align-items: center;
  font-size: var(--text-sm);
  padding: 6px 16px;
}
.k-dropdown-item:focus{
  /* TODO: @include focus-ring; */
}
.k-dropdown-item .k-button-figure {
  text-align: center;
  padding-right: .5rem;
}
</style>
