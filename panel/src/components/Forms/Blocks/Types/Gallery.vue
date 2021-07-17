<template>
  <ul @dblclick="open">
    <template v-if="content.images.length === 0">
      <li />
      <li />
      <li />
      <li />
      <li />
    </template>
    <template v-else>
      <li v-for="image in content.images" :key="image.id">
        <img :src="image.url" :alt="image.alt">
      </li>
    </template>
  </ul>
</template>

<script>
/**
 * @displayName BlockTypeGallery
 * @internal
 */
export default {}
</script>

<style>
.k-block-type-gallery ul {
  display: grid;
  grid-gap: .75rem;
  grid-template-columns: repeat(auto-fit, minmax(6rem, 1fr));
  line-height: 0;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}
.k-block-type-gallery li:empty {
  padding-bottom: 100%;
  background: var(--color-background);
}
.k-block-type-gallery li {
  display: flex;
  position: relative;
  align-items: center;
  justify-content: center;
}
.k-block-type-gallery li img {
  flex-grow: 1;
  max-width: 100%;
}
</style>
