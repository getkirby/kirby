<template>
  <component
    v-tab
    ref="button"
    :aria-current="current"
    :id="id"
    :is="component"
    :disabled="disabled"
    :data-tabbed="tabbed"
    :data-theme="theme"
    :data-responsive="responsive"
    :tabindex="tabindex"
    :target="target"
    :title="tooltip"
    :to="link"
    :type="link ? null : type"
    class="kirby-button"
    v-on="$listeners"
  >
    <figure v-if="image || icon" class="kirby-button-figure">
      <img
        v-if="image"
        :src="imageUrl"
        :alt="tooltip || ''"
      >
      <kirby-icon
        v-else
        :type="icon"
        :alt="tooltip"
      />
    </figure>
    <span v-if="$slots.default" class="kirby-button-text"><slot /></span>
  </component>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    current: [String, Boolean],
    disabled: Boolean,
    icon: String,
    id: [String, Number],
    image: [String, Object],
    link: String,
    responsive: Boolean,
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
      return this.link ? "kirby-link" : "button";
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
  color: currentColor;
  background: none;
  cursor: pointer;
}
button::-moz-focus-inner {
  padding: 0;
  border: 0;
}
.kirby-button[disabled] {
  pointer-events: none;
  opacity: 0.5;
}

.kirby-button {
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
.kirby-button[data-responsive] .kirby-button-text {
  display: none;

  @media screen and (min-width: $breakpoint-small) {
    display: inline;
  }
}

.kirby-button[data-theme="positive"] {
  color: $color-positive;
}

.kirby-button[data-theme="negative"] {
  color: $color-negative;
}

.kirby-button-figure {
  display: inline-block;
  line-height: 0;
}
.kirby-button-figure .kirby-icon {
  position: relative;
  top: 0px;
  color: currentColor;
}

.kirby-button-figure img {
  width: 16px;
  height: 16px;
  background: $color-dark;
  object-fit: cover;
  border-radius: 50%;
}

.kirby-button-figure ~ .kirby-button-text {
  padding-left: 0.5rem;
}

.kirby-button-text {
  opacity: 0.75;
}
.kirby-button:focus .kirby-button-text,
.kirby-button:hover .kirby-button-text {
  opacity: 1;
}

.kirby-button-text span,
.kirby-button-text b {
  vertical-align: baseline;
}
</style>
