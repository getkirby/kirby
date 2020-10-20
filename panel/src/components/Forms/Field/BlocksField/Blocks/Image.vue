<template>
  <figure class="k-block-image-figure" @click="$emit('open')">
    <span class="k-block-image-container">
      <img v-if="image" :src="image.url" />
      <k-button v-else class="k-block-image-empty" icon="image">Select an image …</k-button>
    </span>
    <figcaption v-if="content.caption" v-html="content.caption"></figcaption>
  </figure>
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

.k-block-image-figure {
  cursor: pointer;
}

.k-block-image-container {
  position: relative;
  display: block;
  overflow: hidden;
  padding-bottom: 52.65%;
  background: $color-background;
}

.k-block-image-container img {
  position: absolute;
  top: 0;
  left: 0;
  bottom: 0;
  right: 0;
  height: 100%;
  width: 100%;
  object-fit: cover;
}

.k-block-image-figure figcaption {
  padding-top: .5rem;
  color: $color-gray-600;
  font-size: $text-sm;
}

.k-block-image-empty.k-button {
  position: absolute;
  top: 0;
  left: 0;
  bottom: 0;
  right: 0;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: $color-gray-600;
}
</style>
