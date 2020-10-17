<template>
  <k-editor
    :data-level="content.level"
    :value="content.text"
    class="k-block-heading-editor"
    placeholder="Heading …"
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
    content: [Array, Object]
  }
};
</script>

<style lang="scss">
.k-block-heading {
  padding: .25rem 0;
}
.k-block-heading-editor {
  font-weight: $font-bold;
  font-size: $text-3xl;
}
.k-block-heading-editor[data-level="2"] {
  font-size: $text-2xl;
}
.k-block-heading-editor[data-level="3"] {
  font-size: $text-xl;
}
</style>
