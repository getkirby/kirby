<template>
  <k-aspect-ratio
    :ratio="ratio"
    :data-back="back"
    :data-cover="cover"
    :class="`bg-${back}`"
    class="k-image"
    v-on="$listeners"
  >
    <img
      :alt="alt || ''"
      :src="src"
      :srcset="srcset"
      :sizes="sizes"
      @dragstart.prevent
    >
  </k-aspect-ratio>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    alt: String,
    back: String,
    cover: Boolean,
    ratio: String,
    sizes: String,
    src: String,
    srcset: String,
  },
  computed: {
    ratioPadding() {
      return this.$helper.ratio(this.ratio || "1/1");
    }
  }
};
</script>

<style lang="scss">
.k-image img {
  object-fit: contain;
}
.k-image[data-cover] img {
  object-fit: cover;
}
</style>
