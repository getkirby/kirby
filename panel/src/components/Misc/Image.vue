<template>
  <span
    :data-ratio="ratio"
    :data-back="back"
    :data-cover="cover"
    class="k-image"
    v-on="$listeners"
  >
    <span :style="'padding-bottom:' + ratioPadding">
      <img
        v-if="loaded"
        :key="src"
        :alt="alt || ''"
        :src="src"
        :srcset="srcset"
        :sizes="sizes"
        @dragstart.prevent
      >
      <k-loader
        v-if="!loaded && !error"
        position="center"
        theme="light"
      />
      <k-icon
        v-if="!loaded && error"
        class="k-image-error"
        type="cancel"
      />
    </span>
  </span>
</template>

<script>
/**
 * The `k-image` component simplifies loading and sizing of images and their backgrounds. It can be used as a replacement for regular img tags, but has a bunch of additional options and built-in lazy-loading.
 * @example <k-image src="myimage.jpg" />
 */
export default {
  props: {
    /**
     * Just like in regular `<img>` tags, you can and should define a proper alt attribute whenever possible. The component will add an empty alt tag when no alt text is specified to be skipped by screen readers. Otherwise the filename would be read.
     */
    alt: String,
    /**
     * By default the background of images will be transparent
     * @values black, white, pattern
     */
    back: String,
    /**
     * If images don't fit the defined ratio, the component will add additional space around images. You can change that behavior with the `cover` attribute. If `true`, the image will be cropped to fit the ratio.
     */
    cover: Boolean,
    /**
     * The container can be set to a fixed ratio. The ratio can be defined freely with the format `widthFraction/heightFraction`. The ratio will be calculated automatically. E.g. `1/1`, `16/9` or `4/5`
     */
    ratio: String,
    sizes: String,
    /**
     * The path/URL to the image file
     */
    src: String,
    srcset: String,
  },
  data() {
    return {
      loaded: {
        type: Boolean,
        default: false
      },
      error: {
        type: Boolean,
        default: false
      }
    };
  },
  computed: {
    ratioPadding() {
      return this.$helper.ratio(this.ratio || "1/1");
    }
  },
  created() {
    let img = new Image();

    img.onload = () => {
      this.loaded = true;
      this.$emit("load");
    };

    img.onerror = () => {
      this.error = true;
      this.$emit("error");
    };

    img.src = this.src;
  }
};
</script>

<style>
.k-image span {
  position: relative;
  display: block;
  line-height: 0;
  padding-bottom: 100%;
}
.k-image img {
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: contain;
}
.k-image-error {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  color: var(--color-white);
  font-size: .9em;
}
.k-image-error svg * {
  fill: rgba(255, 255, 255, .3));
}
.k-image[data-cover] img {
  object-fit: cover;
}
.k-image[data-back="black"] span {
  background: var(--color-gray-900);
}
.k-image[data-back="white"] span {
  background: var(--color-white);
  color: var(--color-gray-900);
}
.k-image[data-back="white"] .k-image-error {
  background: var(--color-gray-900);
  color: var(--color-white);
}
.k-image[data-back="pattern"] span {
  background: var(--color-gray-800) var(--bg-pattern);
}
</style>
