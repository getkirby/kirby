<template>
  <span
    :data-cover="cover"
    :style="{ 'padding-block-end': ratioPadding }"
    class="k-aspect-ratio"
  >
    <slot />
  </span>
</template>

<script>
export default {
  props: {
    cover: Boolean,
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
  padding-block-end: 100%;
}
.k-aspect-ratio > * {
  position: absolute !important;
  inset: 0;
  height: 100%;
  width: 100%;
  object-fit: contain;
}
.k-aspect-ratio[data-cover] > * {
  object-fit: cover;
}
</style>