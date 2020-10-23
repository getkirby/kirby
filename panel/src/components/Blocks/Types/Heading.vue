<template>
  <k-writer
    ref="editor"
    :inline="true"
    :nodes="false"
    :value="content.text"
    class="k-block-heading-editor"
    placeholder="Heading …"
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
    append() {
      alert("whohoow");
    }
  }
};
</script>

<style lang="scss">
.k-block-heading {
  padding: .25rem 0;
}
.k-block-heading .k-writer {
  font-weight: $font-bold;
  font-size: $text-3xl;
}
.k-block-heading-editor[data-level="2"] {
  font-size: $text-2xl;
}
.k-block-heading-editor[data-level="3"] {
  font-size: $text-xl;
}
.k-block-heading .ProseMirror strong {
  font-weight: 700;
}
</style>
