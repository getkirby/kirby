<template>
  <k-writer
    ref="input"
    :inline="textField.inline"
    :marks="textField.marks"
    :nodes="textField.nodes"
    :placeholder="textField.placeholder"
    :value="content.text"
    class="k-block-type-text-input"
    @input="update({ text: $event })"
  />
</template>

<script>
/**
 * @displayName BlockTypeText
 * @internal
 */
export default {
  computed: {
    textField() {
      return this.field("text", {});
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
.k-block-type-text-input {
  font-size: var(--text-base);
  line-height: 1.5em;
}
</style>
