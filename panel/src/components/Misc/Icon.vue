<template>
  <span
    :aria-label="alt"
    :role="alt ? 'img' : null"
    :aria-hidden="!alt"
    :data-back="back"
    :data-size="size"
    :class="'k-icon k-icon-' + type"
  >
    <span v-if="isEmoji" class="k-icon-emoji">{{ type }}</span>
    <svg v-else :style="{ color: color }" viewBox="0 0 16 16">
      <use :xlink:href="'#icon-' + type" />
    </svg>
  </span>
</template>

<script>
/**
 * The icon component can be used to display any icon from our own icon set.
 * @example <k-icon type="pencil" />
 */
export default {
  props: {
    /**
     * For better accessibility of icons, you can pass an additional alt attribute like for images.
     */
    alt: String,
    /**
     * Sets a custom color. Directly applied as value of the CSS `color` attribute
     */
    color: String,
    /**
     * Like with the `k-image` component, you can set the background for the icon. By default, the background is transparent.
     * Values: black, white, pattern
     */
    back: String,
    /**
     * By default the icon size is set to `1rem = 16px`, which corresponds with the Panel font size.
     * @values regular, medium, large
     */
    size: String,
    /**
     * Select the icon with this attribute
     */
    type: String
  },
  computed: {
    isEmoji() {
      return this.$helper.string.hasEmoji(this.type);
    }
  }
};
</script>

<style>
.k-icon {
  position: relative;
  line-height: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.k-icon svg {
  width: 1rem;
  height: 1rem;
  -moz-transform: scale(1);
}
.k-icon svg * {
  fill: currentColor;
}
.k-icon[data-back="black"] {
  background: var(--color-gray-900);
  color: var(--color-white);
}
.k-icon[data-back="white"] {
  background: var(--color-white);
  color: var(--color-gray-900);
}
.k-icon[data-back="pattern"] {
  background: var(--color-gray-800) var(--bg-pattern);
  color: var(--color-white);
}
[data-disabled] .k-icon[data-back="black"] {
  background-color: var(--color-gray-600);
}
[data-disabled] .k-icon[data-back="pattern"] {
  background: var(--color-gray-500) var(--bg-pattern);
}
[data-disabled] .k-icon[data-back="pattern"] svg {
  opacity: 1;
}
.k-icon[data-size="medium"] svg {
  width: 2rem;
  height: 2rem;
}
.k-icon[data-size="large"] svg {
  width: 3rem;
  height: 3rem;
}
.k-icon-emoji {
  display: block;
  line-height: 1;
  font-style: normal;
  font-size: 1rem;
}

/* fix emoji alignment on high-res screens */
@media only screen and (-webkit-min-device-pixel-ratio: 2), not all, not all, not all, only screen and (min-resolution: 192dpi), only screen and (min-resolution: 2dppx) {
  .k-icon-emoji {
    font-size: 1.25rem;
  }
}

</style>
