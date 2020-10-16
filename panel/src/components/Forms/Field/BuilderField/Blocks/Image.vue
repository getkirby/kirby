<template>
  <figure v-if="image" class="k-block-image-figure" @click="$emit('edit')">
    <img :src="image.url" />
    <figcaption v-if="content.caption">{{ content.caption }}</figcaption>
  </figure>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    content: Object
  },
  mounted() {
    if (this.content.image.length === 0) {
      this.$emit("edit");
    }
  },
  computed: {
    image() {
      return this.content.image[0];
    }
  }
};
</script>

<style lang="scss">
.k-block-image {
  padding: 1.5rem 0;
}
.k-block-image-figure {
  cursor: pointer;
  position: relative;
  overflow: hidden;
}
.k-block-image-figure img {
  max-height: 20rem;
  width: 100%;
  object-fit: cover;
}
.k-block-image-figure figcaption {
  padding-top: .5rem;
  color: $color-gray-500;
  font-size: $text-sm;
}
</style>
