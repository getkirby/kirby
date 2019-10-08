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
export default {
  props: {
    alt: String,
    back: String,
    cover: Boolean,
    ratio: String,
    sizes: String,
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

<style lang="scss">
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
  color: $color-white;
  font-size: 0.9em;
}
.k-image-error svg * {
  fill: rgba($color-white, 0.3);
}
.k-image[data-cover] img {
  object-fit: cover;
}
.k-image[data-back="black"] span {
  background: $color-dark;
}
.k-image[data-back="white"] span {
  background: $color-white;
  color: $color-dark;
}
.k-image[data-back="white"] .k-image-error {
  background: $color-dark;
  color: $color-white;
}
.k-image[data-back="pattern"] span {
  background: lighten($color-dark, 10%) url($pattern);
}
</style>
