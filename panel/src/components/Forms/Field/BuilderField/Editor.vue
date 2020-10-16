<template>
  <div
    ref="editor"
    :data-placeholder="placeholder"
    class="k-editor"
    contenteditable
    spellcheck="false"
    @input="onInput"
  />
</template>

<script>
export default {
  props: {
    placeholder: String,
    value: String
  },
  mounted() {
    this.$refs.editor.innerHTML = this.value;
  },
  methods: {
    onInput(event) {
      this.$emit("input", event.target.innerHTML);
    }
  }
};
</script>

<style lang="scss">
.k-editor:focus {
  outline: none;
}
.k-editor:empty:after {
  content: attr(data-placeholder);
  color: $color-gray-400;
}
</style>
