<template>
  <k-writer
    class="k-block-text-editor"
    placeholder="Text …"
    :value="content.text"
    @input="$emit('update', { text: $event })"
  />
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    content: [Array, Object]
  }
};
</script>

<style lang="scss">
.k-block-text {
  padding: .5rem 0;
}

.k-block-text-editor {
  font-size: $text-base;
  line-height: 1.5em;
}
</style>
