<template>
  <div class="k-block-images" @click="$emit('edit')">
    <ul class="k-block-images-preview">
      <li v-for="image in content.images" :key="image.id">
        <img :src="image.url" />
      </li>
    </ul>
  </div>
</template>

<script>
export default {
  props: {
    content: Object
  }
};
</script>

<style lang="scss">
.k-block-images-preview {
  display: grid;
  grid-gap: .75rem;
  grid-template-columns: repeat(auto-fit, minmax(12rem, 1fr));
  line-height: 0;
  justify-content: center;
  cursor: pointer;
  padding: 1.5rem 0;
}
.k-block-images-preview li {
  padding-bottom: 100%;
  position: relative;
  border-radius: $rounded-sm;
}
.k-block-images-preview img {
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
