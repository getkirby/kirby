<template>
  <ul v-if="value" class="k-files-field-preview">
    <li v-for="file in value" :key="file.url">
      <k-link :title="file.filename" :to="file.link" @click.native.stop>
        <k-image :src="file.url" back="pattern" />
      </k-link>
    </li>
  </ul>
</template>

<script>
export default {
  props: {
    value: Array,
  }
}
</script>

<style lang="scss">

.k-files-field-preview {
  display: grid;
  grid-gap: .5rem;
  grid-template-columns: repeat(auto-fill, 1.525rem);
  padding: 0 .75rem;
}
.k-files-field-preview li {
  line-height: 0;
}

</style>
