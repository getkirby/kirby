<template>
  <component
    :is="element"
    :data-ratio="ratio"
    :style="'--ratio:' + ratio"
    :class="back ? 'bg-' + back : false"
    class="k-aspect-ratio"
    v-on="$listeners"
  >
    <!-- @slot Content that should be sized at the specified ratio -->
    <slot />
  </component>
</template>

<script>
/**
 * Use `<k-aspect-ratio>` to size content at specified
 * dimensions (e.g. `"16/9"`).
 */

export default {
  props: {
    /**
     * Background color.
     * Available options: `"black"`, `"white"`, `"pattern"`
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
     * Ratio fraction, e.g. `"1/2"`, `"3/2"` etc.
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
  position: relative;
  display: block;
  line-height: 0;
}
.k-aspect-ratio::before {
  content: "";
  display: block;
  padding-bottom: calc(100% / (var(--ratio)));
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
