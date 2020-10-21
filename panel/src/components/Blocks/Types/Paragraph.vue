<template>
  <k-writer
    ref="editor"
    :breaks="true"
    :value="content.text"
    class="k-block-paragraph-editor"
    placeholder="Text …"
    @backspaceWhenEmpty="$emit('remove')"
    @enter="onEnter"
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
    },
    onEnter({ before, after }) {
      this.$emit("update", { text: before });
      this.$emit("append", "paragraph", { text: after });
    }
  }
};
</script>

<style lang="scss">
.k-block-paragraph {
  padding: .5rem 0;
}
.k-block-paragraph-editor {
  font-size: $text-base;
  line-height: 1.5em;
}
</style>
