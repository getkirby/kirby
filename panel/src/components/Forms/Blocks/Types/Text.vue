<template>
  <k-writer
    ref="input"
    :inline="textField.inline"
    :marks="textField.marks"
    :nodes="textField.nodes"
    :paste="paste"
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
  props: {
    endpoints: Object,
  },
  computed: {
    textField() {
      return this.field("text", {});
    }
  },
  methods: {
    paste(event, html, text) {

      // wait to conver the html into blocks
      (async () => {
        const blocks = await this.$api.post(this.endpoints.field + "/paste", { html: html || text });

        if (blocks.length > 1) {
          // append all found blocks after the current block
          this.$emit("append", blocks);
        } else {
          this.$refs.input.command("insertHtml", html || text);
        }

      })();

      // stop the original paste event in the writer
      return true;

    },
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
