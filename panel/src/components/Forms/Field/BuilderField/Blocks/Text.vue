<template>
  <k-editor
    class="k-block-text"
    placeholder="Text …"
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
.k-block-text {
  font-size: $text-base;
  line-height: 1.5em;
}
.k-block-text:focus {
  outline: none;
}
</style>
