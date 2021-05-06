<template>
  <component
    :is="component"
    ref="button"
    v-bind="$props"
    v-on="$listeners"
  >
    <slot />
  </component>
</template>

<script>
/**
 * @example <k-button icon="check">Save</k-button>
 */
export default {
  inheritAttrs: false,
  props: {
    autofocus: Boolean,
    /**
     * Sets the `aria-current` attribute. Especially useful in connection with a `link` attribute.
     */
    current: [String, Boolean],
    /**
     * A disabled button will have no pointer events and the opacity is be reduced.
     */
    disabled: Boolean,
    /**
     * Adds an icon to the button.
     */
    icon: String,
    id: [String, Number],
    /**
     * If the link attribute is set, the button will automatically be converted to a proper `a` tag.
     */
    link: String,
    /**
     * A responsive button will hide the button text on smaller screens automatically and only keep the icon. An icon must be set in this case.
     */
    responsive: Boolean,
    rel: String,
    role: String,
    /**
     * In connection with the `link` attribute, you can also set the target of the link. This does not apply to regular buttons.
     */
    target: String,
    tabindex: String,
    /**
     * With the theme you can control the general design of the button.
     * @values positive, negative
     */
    theme: String,
    /**
     * The tooltip attribute can be used to add additional text to the button, which is shown on mouseover (with the `title` attribute).

     */
    tooltip: String,
    /**
     * The type attribute sets the button type like in HTML.
     * @values button, submit, reset
     */
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

<style>
button {
  line-height: inherit;
  border: 0;
  font-family: var(--font-sans);
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
  font-size: var(--text-sm);
  transition: color .3s;
  outline: none;

}
.k-button:focus,
.k-button:hover {
  outline: none;
}

.k-button[data-tabbed] {
  box-shadow: var(--shadow-outline);
}

.k-button * {
  vertical-align: middle;
}

/* hide button text on small screens */
.k-button[data-responsive] .k-button-text {
  display: none;
}
@media screen and (min-width: 30em) {
  .k-button[data-responsive] .k-button-text {
    display: inline;
  }
}

.k-button[data-theme="positive"] {
  color: var(--color-positive);
}

.k-button[data-theme="negative"] {
  color: var(--color-negative);
}

.k-button-icon {
  display: inline-flex;
  align-items: center;
  line-height: 0;
}

[dir="ltr"] .k-button-icon ~ .k-button-text {
  padding-left: .5rem;
}

[dir="rtl"] .k-button-icon ~ .k-button-text {
  padding-right: .5rem;
}

.k-button-text {
  opacity: .75;
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
