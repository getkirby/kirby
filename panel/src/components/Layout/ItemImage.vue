<template>
  <div
    v-if="image"
    class="k-item-figure"
    :style="{ background: $helper.color(back) }"
  >
    <!-- image -->
    <k-image
      v-if="image.src"
      :cover="image.cover"
      :ratio="ratio"
      :sizes="sizes"
      :src="image.src"
      :srcset="image.srcset"
      class="k-item-image"
    />
    <!-- icon -->
    <k-aspect-ratio v-else :ratio="ratio">
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
    },
    width: String
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
          return "large";
        case "cardlets":
          return "medium";
        default:
          return "regular";
      }
    },
    sizes() {
      switch (this.width) {
        case "1/2":
        case "2/4":
          return "(min-width: 30em) and (max-width: 65em) 59em, (min-width: 65em) 44em, 27em";
        case "1/3":
          return "(min-width: 30em) and (max-width: 65em) 59em, (min-width: 65em) 29.333em, 27em";
        case "1/4":
          return "(min-width: 30em) and (max-width: 65em) 59em, (min-width: 65em) 22em, 27em";
        case "2/3":
          return "(min-width: 30em) and (max-width: 65em) 59em, (min-width: 65em) 27em, 27em";
        case "3/4":
          return "(min-width: 30em) and (max-width: 65em) 59em, (min-width: 65em) 66em, 27em";
        default:
          return "(min-width: 30em) and (max-width: 65em) 59em, (min-width: 65em) 88em, 27em";
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
