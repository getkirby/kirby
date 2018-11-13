<template>
  <p class="k-url-field-preview">
    <k-link :to="link" target="_blank" @click.native.stop>{{ value }}</k-link>
  </p>
</template>

<script>
export default {
  props: {
    column: Object,
    value: String,
  },
  computed: {
    link() {
      return this.value;
    }
  }
}
</script>

<style lang="scss">

.k-url-field-preview a {
  color: $color-focus;
  text-decoration: underline;
  transition: color .3s;
  display: inline-block;
  padding: 0 .75rem;
  overflow: hidden;
  white-space: nowrap;
  max-width: 100%;
  text-overflow: ellipsis;
}
.k-url-field-preview a:hover {
  color: $color-black;
}

</style>
