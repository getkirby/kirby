<template>
  <k-input
    ref="input"
    :buttons="false"
    :placeholder="placeholder"
    :spellcheck="false"
    :value="content.text"
    class="k-block-type-markdown-input"
    type="textarea"
    @input="update({ text: $event })"
  />
</template>

<script>
/**
 * @displayName BlockTypeMarkdown
 * @internal
 */
export default {
  computed: {
    placeholder() {
      return this.field("text", {}).placeholder;
    }
  },
  methods: {
    focus() {
      this.$refs.input.focus();
    }
  }
};
</script>

<style>
.k-block-type-markdown-input {
  position: relative;
  font-size: var(--text-sm);
  line-height: 1.5em;
  background: var(--color-background);
  border-radius: var(--rounded);
  padding: .5rem .5rem 0;
  font-family: var(--font-mono);
}
</style>
