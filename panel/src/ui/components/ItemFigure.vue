<template>
  <div
    v-if="image || icon"
    class="k-item-figure"
  >
    <!-- image -->
    <k-image
      v-if="image.url"
      :back="back"
      :cover="image.cover"
      :ratio="ratio"
      :src="image.url"
      class="k-item-image"
    />
    <!-- icon -->
    <k-aspect-ratio
      v-else-if="icon.type !== false"
      :back="back"
      :ratio="ratio"
    >
      <k-icon
        :color="icon.color || 'white'"
        :size="icon.size"
        :type="icon.type || 'page'"
        class="k-item-icon"
      />
    </k-aspect-ratio>
  </div>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    icon: {
      type: [Object, Boolean],
      default: true
    },
    image: {
      type: [Object, Boolean],
      default: true
    },
    /**
     * Available options: `list`|`card`
     */
    layout: {
      type: String,
      default: "card"
    },
  },
  computed: {
    back() {
      return this.image.back || this.icon.back || "black";
    },
    ratio() {
      if (this.layout === "card") {
        return this.image.ratio || this.icon.ratio || "1/1";
      }

      return "1/1";
    }
  }
};
</script>

<style lang="scss">
.k-item-figure {
  overflow: hidden;
  flex-shrink: 0;
}
</style>
