<template>
  <div
    v-if="image"
    class="k-item-figure"
    :style="{ background: $helper.color(back) }"
  >
    <!-- image -->
    <k-image
      v-if="image.src"
      ref="image"
      :cover="image.cover"
      :ratio="ratio"
      :src="image.src"
      :srcset="image.srcset"
      class="k-item-image"
    />
    <!-- icon -->
    <k-aspect-ratio
      v-else
      :ratio="ratio"
    >
      <k-icon
        :color="$helper.color(image.color)"
        :size="size"
        :type="image.icon"
        class="k-item-icon"
      />
    </k-aspect-ratio>
  </div>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    image: [Object, Boolean],
    layout: {
      type: String,
      default: "list"
    }
  },
  computed: {
    back() {
      return this.image.back || "black";
    },
    ratio() {
      if (this.layout === "cards") {
        return this.image.ratio || "1/1";
      }

      return "1/1";
    },
    size() {
      switch (this.layout) {
        case "cards":
          return "large"
        case "cardlets":
            return "medium"
        default:
          return "regular"
      }
    }
  },
  mounted() {
    this.onResize();
  },
  destroyed() {
    this.onResize(false);
  },
  methods: {
    onResize(init = true) {
      if (
        this.$refs.image &&
        this.$refs.image.$refs.image
      ) {
        if (init === true) {
          if (!window.panel.$imgsizes) {
            window.panel.$imgsizes = new ResizeObserver(entries => entries.forEach(entry => entry.target.sizes = entry.contentRect.width + "px"));
          }

          window.panel.$imgsizes.observe(this.$refs.image.$refs.image);
          return;
        }

        if (window.panel.$imgsizes) {
          window.panel.$imgsizes.unobserve(this.$refs.image.$refs.image);
        }
      }
    }
  }
};
</script>

<style>
.k-item-figure {
  overflow: hidden;
  flex-shrink: 0;
}
</style>
