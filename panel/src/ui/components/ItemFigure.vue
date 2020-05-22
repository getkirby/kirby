<template>
  <div
    v-if="preview"
    class="k-item-figure"
  >
    <!-- image -->
    <k-image
      v-if="preview.image"
      :back="back"
      :cover="preview.cover"
      :ratio="ratio"
      :src="preview.image.src || preview.image"
      class="k-item-image"
    />
    <!-- icon -->
    <k-aspect-ratio
      v-else
      :back="back"
      :ratio="ratio"
    >
      <template v-if="preview.icon !== false">
        <k-icon
          :color="preview.color || 'white'"
          :size="preview.size"
          :type="preview.icon || 'page'"
          class="k-item-icon"
        />
      </template>
    </k-aspect-ratio>
  </div>
</template>

<script>
import figure from "@/ui/mixins/figure.js";

export default {
  inheritAttrs: false,
  mixins: [figure],
  computed: {
    back() {
      return this.preview.back || "black";
    },
    ratio() {
      if (this.layout === "card") {
        return this.preview.ratio || "1/1";
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
