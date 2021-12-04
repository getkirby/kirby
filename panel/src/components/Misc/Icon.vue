<template>
  <span
    :aria-label="alt"
    :role="alt ? 'img' : null"
    :aria-hidden="!alt"
    :data-back="back"
    :data-size="size"
    :class="'k-icon k-icon-' + type"
    :style="{ background: $helper.color(back) }"
  >
    <span v-if="isEmoji" class="k-icon-emoji">{{ type }}</span>
    <svg v-else :style="{ color: $helper.color(color) }" viewBox="0 0 16 16">
      <use :xlink:href="'#icon-' + type" />
    </svg>
  </span>
</template>

<script>
/**
 * Use to display any icon from the Panel's icon set.
 * @public
 *
 * @example <k-icon type="edit" />
 */
export default {
  props: {
    /**
     * For better accessibility of icons,
     * you can pass an additional alt
     * attribute like for images.
     */
    alt: String,
    /**
     * Sets a custom color. Either shorthand
     * for Panel default colors or directly
     * applied CSS value.
     */
    color: String,
    /**
     * Background color/pattern for the icon.
     * Either shorthand for Panel default
     * colors or directly  applied CSS value.
     * By default, the background is transparent.
     */
    back: String,
    /**
     * By default the icon size is set
     * to `1rem = 16px`, which corresponds
     * with the Panel font size.
     *
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
  --size: 1rem;
  position: relative;
  line-height: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  font-size: var(--size);
}
.k-icon[data-size="medium"] {
  --size: 2rem;
}
.k-icon[data-size="large"] {
  --size: 3rem;
}
.k-icon svg {
  width: var(--size);
  height: var(--size);
  -moz-transform: scale(1);
}
.k-icon svg * {
  fill: currentColor;
}
.k-icon[data-back="black"] {
  color: var(--color-white);
}
.k-icon[data-back="white"] {
  color: var(--color-gray-900);
}
.k-icon[data-back="pattern"] {
  color: var(--color-white);
}
[data-disabled="true"] .k-icon[data-back="pattern"] svg {
  opacity: 1;
}

.k-icon-emoji {
  display: block;
  line-height: 1;
  font-style: normal;
  font-size: var(--size);
}

/* fix emoji alignment on high-res screens */
@media only screen and (-webkit-min-device-pixel-ratio: 2),
  not all,
  not all,
  not all,
  only screen and (min-resolution: 192dpi),
  only screen and (min-resolution: 2dppx) {
  .k-icon-emoji {
    font-size: 1.25em;
  }
}
</style>
