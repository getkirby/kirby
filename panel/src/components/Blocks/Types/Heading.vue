<template>
  <k-writer
    :data-level="content.level"
    :inline="true"
    :value="content.text"
    class="k-block-heading-editor"
    placeholder="Heading …"
    @dblclick="$emit('open')"
    @enter="$emit('append', 'paragraph')"
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
  font-weight: 900;
}
</style>
