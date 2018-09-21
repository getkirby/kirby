<template>
  <p class="k-url-field-preview">
    <span class="k-structure-item-label">{{ column.label }}</span>
    <k-link @click.native.stop :to="link" target="_blank">{{ value }}</k-link>
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
}
.k-url-field-preview a:hover {
  color: $color-black;
}

</style>
