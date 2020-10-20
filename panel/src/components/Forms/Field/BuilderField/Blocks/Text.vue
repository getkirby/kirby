<template>
  <k-writer
    class="k-block-text-editor"
    placeholder="Text …"
    :breaks="true"
    :value="content.text"
    @dblclick="$emit('open')"
    @enter="$emit('append', 'text')"
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
