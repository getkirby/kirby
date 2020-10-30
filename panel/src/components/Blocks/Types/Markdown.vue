<template>
  <div class="k-block-markdown-editor">
    <k-input
      ref="code"
      :buttons="false"
      :placeholder="$t('field.blocks.markdown.placeholder') + ' …'"
      :spellcheck="false"
      :value="content.text"
      type="textarea"
      @input="$emit('update', { ...content, text: $event })"
    />
  </div>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    content: [Array, Object],
    fieldset: Object
  },
  methods: {
    focus() {
      this.$refs.code.focus();
    }
  }
};
</script>

<style lang="scss">
.k-block-markdown {
  padding: 1.5rem 0;
}
.k-block-markdown-editor {
  position: relative;
  font-size: $text-sm;
  line-height: 1.5em;
  background: $color-background;
  border-radius: $rounded;
  padding: .5rem;
  font-family: $font-mono;
}
</style>
