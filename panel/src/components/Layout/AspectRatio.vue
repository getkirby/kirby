<template>
  <span
    :data-cover="cover"
    :style="{ 'padding-bottom': ratioPadding }"
    class="k-aspect-ratio"
  >
    <!-- @slot Content -->
    <slot />
  </span>
</template>

<script>
/**
 * Creates a layout element
 * in the specified ratio
 * @public
 *
 * @example <k-aspect-ratio ratio="3/2">
  <div>Ratio!</div>
</k-aspect-ratio>
 */
export default {
  props: {
    /**
     * If `true`, the content will fill
     * the element's entire space/ratio
     */
    cover: Boolean,
    /**
     * The ratio can be defined freely with the format
     * `widthFraction/heightFraction`. The ratio will
     * be calculated automatically.
     *
     * @values e.g. `1/1`, `16/9` or `4/5`
     */
    ratio: String
  },
  computed: {
    ratioPadding() {
      return this.$helper.ratio(this.ratio);
    }
  }
};
</script>

<style>
.k-aspect-ratio {
  position: relative;
  display: block;
  overflow: hidden;
  padding-bottom: 100%;
}
.k-aspect-ratio > * {
  position: absolute !important;
  inset: 0;
  height: 100%;
  width: 100%;
  object-fit: contain;
}
.k-aspect-ratio[data-cover="true"] > * {
  object-fit: cover;
}
</style>
