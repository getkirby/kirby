<template>
  <k-aspect-ratio
    :ratio="ratio"
    :back="back"
    class="k-image"
    v-on="$listeners"
  >
    <img
      :alt="alt || ''"
      :src="src"
      :srcset="srcset"
      :sizes="sizes"
      :class="imgClasses"
      @dragstart.prevent
    >
  </k-aspect-ratio>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    /**
     * Just like in regular img tags, you can and should define
     * a proper alt attribute whenever possible.
     * The component will add an empty alt tag when no alt text is
     * specified to be skipped by screen readers. Otherwise the
     * filename would be read.
     */
    alt: String,
    /**
     * By default the background of images will be transparent.
     * But you can change it to `black`|`white"`|`pattern"` or a hex code.
     */
    back: String,
    /**
     * If images don't fit the defined ratio, the component will
     * add additional space around images. You can change that behavior
     * with this attribute. If `true`, the image will be cropped to
     * fit the ratio.
     */
    cover: Boolean,
    /**
     * The container can be set to a fixed ratio. The ratio can be
     * defined freely with the format `widthFraction/heightFraction`.
     * The ratio will be calculated automatically.
     */
    ratio: String,
    sizes: String,
    src: String,
    srcset: String,
  },
  computed: {
    imgClasses() {
      return "object-" + (this.cover ? "cover" : "contain");
    },
    ratioPadding() {
      return this.$helper.ratio(this.ratio || "1/1");
    }
  }
};
</script>
