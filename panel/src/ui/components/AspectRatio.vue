<template>
  <component
    :is="element"
    :data-ratio="ratio"
    :class="$helper.color.class(back, 'bg-')"
    :style="'--ratio:' + ratio + ';' + $helper.color.style(back, 'background-')"
    class="k-aspect-ratio relative block"
    v-on="$listeners"
  >
    <!-- @slot Content that should be sized at the specified ratio -->
    <slot />
  </component>
</template>

<script>
export default {
  props: {
    /**
     * Background color. Hex code or suffix for a `bg-` utility class
     */
    back: String,
    /**
     * HTML tag to be used as wrapping element
     */
    element: {
      type: String,
      default: "span",
    },
    /**
     * Ratio fraction, e.g. `1/2`|`3/2` etc.
     */
    ratio: {
      type: String,
      default: "1/1"
    }
  }
}
</script>
<style lang="scss">
.k-aspect-ratio {
  --ratio: 1/1;
  line-height: 0;
}
.k-aspect-ratio::before {
  --ratioCalculated: calc(var(--ratio));
  content: "";
  display: block;
  padding-bottom: calc(100% / var(--ratioCalculated));
}
.k-aspect-ratio > * {
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 100%;
}
</style>
