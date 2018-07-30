<template>
  <figure
    :data-ratio="ratio"
    :data-back="back"
    :data-cover="cover"
    class="kirby-image"
    v-on="$listeners"
  >
    <span :style="'padding-bottom:' + ratioPadding">
      <transition name="kirby-image-transition">
        <img
          v-if="loaded"
          :alt="alt || ''"
          :src="src"
          @dragstart.prevent
        >
      </transition>
      <kirby-loader
        v-if="!loaded && !error"
        position="center"
        theme="light"
      />
      <kirby-icon
        v-if="!loaded && error"
        class="kirby-image-error"
        type="cancel"
      />
    </span>
    <figcaption v-if="caption" v-html="caption" />
  </figure>
</template>

<script>
import ratioPadding from "../../helpers/ratioPadding.js";

export default {
  props: {
    src: String,
    alt: String,
    ratio: String,
    back: String,
    caption: String,
    cover: Boolean
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
      return ratioPadding(this.ratio || "1/1");
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
  },
};
</script>

<style lang="scss">
.kirby-image span {
  position: relative;
  display: block;
  line-height: 0;
  padding-bottom: 100%;
}
.kirby-image img {
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: contain;
}
.kirby-image-error {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  color: $color-white;
  font-size: 0.9em;
}
.kirby-image-error svg * {
  fill: rgba($color-white, 0.3);
}
.kirby-image-transition-enter-active,
.kirby-image-transition-leave-active {
  transition: opacity 0.2s;
}
.kirby-image-transition-enter,
.kirby-image-transition-leave-to {
  opacity: 0;
}
.kirby-image[data-cover] img {
  object-fit: cover;
}
.kirby-image[data-back="black"] span {
  background: $color-dark;
}
.kirby-image[data-back="white"] span {
  background: $color-white;
}
.kirby-image[data-back="white"] .kirby-image-error {
  background: $color-dark;
}
.kirby-image[data-back="pattern"] span {
  background: lighten($color-dark, 10%) url($pattern);
}
</style>
