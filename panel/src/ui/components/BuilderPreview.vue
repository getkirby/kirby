<template>
  <iframe class="k-builder-preview bg-yellow" />
</template>

<script>

export default {
  inheritAttrs: false,
  props: {
    fieldset: {
      type: Object,
      default: () => {
        return {};
      }
    },
    block: {
      type: Object,
      default: () => {
        return {};
      }
    }
  }
}
</script>

<style lang="scss">
.k-builder-preview {
  width: 100%;
  border: 0;
}
</style>
