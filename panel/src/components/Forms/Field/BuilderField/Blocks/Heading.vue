<template>
  <k-editor
    class="k-block-heading"
    placeholder="Heading …"
    :value="content.text"
    @input="$emit('update', { text: $event })"
  />
</template>

<script>
export default {
  props: {
    content: [Array, Object]
  }
};
</script>

<style lang="scss">
.k-block-heading {
  font-weight: $font-bold;
  font-size: $text-3xl;
  padding: .75rem 0;
}
</style>
