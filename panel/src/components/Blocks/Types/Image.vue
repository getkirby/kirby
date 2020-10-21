<template>
  <k-block-figure
    :caption="content.caption"
    :is-empty="!image"
    empty-icon="image"
    empty-text="Select an image …"
    v-on="$listeners"
  >
    <img v-if="image" :src="image.url" />
  </k-block-figure>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    content: Object
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
</style>
