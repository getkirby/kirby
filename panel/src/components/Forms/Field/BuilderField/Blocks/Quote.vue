<template>
  <div class="k-block-quote-editor">
    <k-editor
      :value="content.text"
      class="k-block-quote-text"
      placeholder="Quote …"
      @input="update({ text: $event })"
    />
    <k-editor
      :value="content.citation"
      class="k-block-quote-citation"
      placeholder="by …"
      @input="update({ citation: $event })"
    />
  </div>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    content: [Array, Object]
  },
  methods: {
    update(value) {
      this.$emit("update", {
        ...this.content,
        ...value
      });
    }
  }
};
</script>

<style lang="scss">
.k-block-quote {
  padding: 1.5rem 0;
}
.k-block-quote-editor {
  padding-left: 1rem;
  border-left: 3px solid #000;
}
.k-block-quote-text {
  font-size: $text-xl;
  margin-bottom: .25rem;
  line-height: 1.35em;
}
.k-block-quote-citation {
  font-style: italic;
  font-size: $text-sm;
  color: $color-gray-500;
}
</style>
