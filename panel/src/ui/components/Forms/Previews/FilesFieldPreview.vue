<template>
  <ul class="k-files-field-preview" v-if="value">
    <li v-for="file in value" :key="file.url">
      <k-link :title="file.filename" @click.native.stop :to="file.link">
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
  grid-template-columns: repeat(auto-fill, 2.25rem);
}
.k-files-field-preview li {
  line-height: 0;
}

</style>
