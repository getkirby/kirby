<template>
  <component
    v-tab
    ref="button"
    :aria-current="current"
    :autofocus="autofocus"
    :id="id"
    :is="component"
    :disabled="disabled"
    :data-tabbed="tabbed"
    :data-theme="theme"
    :data-responsive="responsive"
    :role="role"
    :tabindex="tabindex"
    :target="target"
    :title="tooltip"
    :to="link"
    :type="link ? null : type"
    class="k-button"
    v-on="$listeners"
  >
    <k-icon
      v-if="icon"
      :type="icon"
      :alt="tooltip"
      class="k-button-icon"
    />
    <span v-if="$slots.default" class="k-button-text"><slot /></span>
  </component>
</template>

<script>
/* Directives */
import TabDirective from "@/directives/tab.js";

export default {
  directives: {
    "tab": TabDirective,
  },
  inheritAttrs: false,
  props: {
    autofocus: Boolean,
    current: [String, Boolean],
    disabled: Boolean,
    icon: String,
    id: [String, Number],
    link: String,
    responsive: Boolean,
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
  data() {
    return {
      tabbed: false
    };
  },
  computed: {
    component() {
      return this.link ? "k-link" : "button";
    },
    imageUrl() {
      if (!this.image) {
        return null;
      }

      if (typeof this.image === "object") {
        return this.image.url;
      }

      return this.image;
    },
  },
  methods: {
    focus() {
      this.$refs.button.focus();
    },
    tab() {
      this.focus();
      this.tabbed = true;
    },
    untab() {
      this.tabbed = false;
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
.k-button[disabled],
.k-button[data-disabled] {
  pointer-events: none;
  opacity: 0.5;
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
