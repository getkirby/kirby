<template>
  <k-block-figure
    :caption="content.caption"
    :caption-marks="captionMarks"
    :empty-text="$t('field.blocks.image.placeholder') + ' …'"
    :is-empty="!src"
    empty-icon="image"
    @open="open"
    @update="update"
  >
    <template v-if="src">
      <k-aspect-ratio v-if="ratio" :ratio="ratio" :cover="crop">
        <img :alt="content.alt" :src="src">
      </k-aspect-ratio>
      <img
        v-else
        :alt="content.alt"
        :src="src"
        class="k-block-type-image-auto"
      >
    </template>
  </k-block-figure>
</template>

<script>
/**
 * @displayName BlockTypeImage
 * @internal
 */
export default {
  computed: {
    captionMarks() {
      return this.field("caption", { marks: true }).marks;
    },
    crop() {
      return this.content.crop || false;
    },
    src() {
      if (this.content.location === "web") {
        return this.content.src;
      }

      if (this.content.image[0] && this.content.image[0].url) {
        return this.content.image[0].url;
      }

      return false;
    },
    ratio() {
      return this.content.ratio || false;
    },
  }
};
</script>

<style>
.k-block-type-image .k-block-figure-container {
  display: block;
  text-align: center;
  line-height: 0;
}
.k-block-type-image-auto {
  max-width: 100%;
  max-height: 30rem;
}
</style>
