<template>
  <component
    ref="button"
    :is="component"
    v-bind="$props"
    v-on="$listeners"
  >
    <slot />
  </component>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    autofocus: Boolean,
    current: [String, Boolean],
    disabled: Boolean,
    icon: String,
    id: [String, Number],
    link: String,
    responsive: Boolean,
    rel: String,
    role: String,
    target: String,
    tabindex: String,
    theme: String,
    tooltip: String,
    type: {
      type: String,
      default: "button"
    }
  },
  computed: {
    component() {
      if (this.disabled === true) {
        return "k-button-disabled";
      }

      return this.link ? "k-button-link" : "k-button-native";
    },
  },
  methods: {
    focus() {
      if (this.$refs.button.focus) {
        this.$refs.button.focus();
      }
    },
    tab() {
      if (this.$refs.button.tab) {
        this.$refs.button.tab();
      }
    },
    untab() {
      if (this.$refs.button.untab) {
        this.$refs.button.untab();
      }
    }
  }
};
</script>

<style lang="scss">
button {
  line-height: inherit;
  border: 0;
  font-family: $font-family-sans;
  font-size: 1rem;
  color: currentColor;
  background: none;
  cursor: pointer;
}
button::-moz-focus-inner {
  padding: 0;
  border: 0;
}

.k-button {
  display: inline-block;
  position: relative;
  font-size: $font-size-small;
  transition: color 0.3s;

  &:focus,
  &:hover {
    outline: none;
  }

  @include highlight-tabbed;

  * {
    vertical-align: middle;
  }
}

/* hide button text on small screens */
.k-button[data-responsive] .k-button-text {
  display: none;

  @media screen and (min-width: $breakpoint-small) {
    display: inline;
  }
}

.k-button[data-theme="positive"] {
  color: $color-positive;
}

.k-button[data-theme="negative"] {
  color: $color-negative;
}

.k-button-icon {
  display: inline-flex;
  align-items: center;
  line-height: 0;
}

.k-button-icon ~ .k-button-text {
  [dir="ltr"] & {
    padding-left: 0.5rem;
  }

  [dir="rtl"] & {
    padding-right: 0.5rem;
  }
}

.k-button-text {
  opacity: 0.75;
}
.k-button:focus .k-button-text,
.k-button:hover .k-button-text {
  opacity: 1;
}

.k-button-text span,
.k-button-text b {
  vertical-align: baseline;
}
</style>
