<template>
  <k-writer
    ref="editor"
    :data-level="content.level"
    :inline="true"
    :nodes="false"
    :placeholder="$t('field.blocks.heading.placeholder') + ' …'"
    :value="content.text"
    class="k-block-heading-editor"
    @input="$emit('update', {
      ...content,
      text: $event
    })"
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
    },
    append() {
      alert("whohoow");
    }
  }
};
</script>

<style lang="scss">
.k-block-heading {
  padding: .75rem 0;
}
.k-block-heading-editor {
  font-weight: $font-bold;
  font-size: $text-3xl;
  line-height: 1.125em;
}
.k-block-heading-editor[data-level="2"] {
  font-size: $text-2xl;
  line-height: 1.25em;
}
.k-block-heading-editor[data-level="3"] {
  line-height: 1.25em;
  font-size: $text-xl;
}
.k-block-heading-editor .ProseMirror strong {
  font-weight: 700;
}
</style>
