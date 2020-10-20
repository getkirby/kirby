<template>
  <k-writer
    ref="editor"
    :breaks="true"
    :value="content.text"
    class="k-block-text-editor"
    placeholder="Text …"
    @enter="$emit('append', 'text')"
    @input="$emit('update', { text: $event })"
  />
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    content: [Array, Object]
  },
  methods: {
    focus() {
      this.$refs.editor.focus();
    }
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
