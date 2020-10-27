<template>
  <ul class="k-block-gallery-preview" @click="$emit('open')">
    <template v-if="content.images.length === 0">
      <li />
      <li />
      <li />
    </template>
    <template v-else>
      <li v-for="image in content.images" :key="image.id">
        <img :src="image.url">
      </li>
    </template>
  </ul>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    content: Object
  }
};
</script>

<style lang="scss">
.k-block-gallery {
  padding: 1.5rem 0;
}
.k-block-gallery-preview {
  display: grid;
  grid-gap: .75rem;
  grid-template-columns: repeat(auto-fit, minmax(6rem, 1fr));
  line-height: 0;
  justify-content: center;
  cursor: pointer;
}
.k-block-gallery-preview li {
  padding-bottom: 100%;
  position: relative;
  border-radius: $rounded-sm;
  background: #efefef;
}
.k-block-gallery-preview img {
  position: absolute;
  top: 0;
  left: 0;
  bottom: 0;
  right: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
}
</style>
