<template>
  <ul class="k-block-images-preview" @click="$emit('edit')">
    <li v-for="image in content.images" :key="image.id">
      <img :src="image.url" />
    </li>
  </ul>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    content: Object
  },
  mounted() {
    if (this.content.images.length === 0) {
      this.$emit("edit");
    }
  }
};
</script>

<style lang="scss">
.k-block.k-block-images {
  padding: 1.5rem 0;
}
.k-block-images-preview {
  display: grid;
  grid-gap: .75rem;
  grid-template-columns: repeat(auto-fit, minmax(6rem, 1fr));
  line-height: 0;
  justify-content: center;
  cursor: pointer;
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
