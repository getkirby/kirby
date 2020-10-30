<template>
  <k-writer
    ref="editor"
    :nodes="false"
    :placeholder="$t('field.blocks.text.placeholder') + ' …'"
    :value="content.text"
    class="k-block-text-editor"
    @input="$emit('update', { text: $event })"
  />
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    content: Object
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
