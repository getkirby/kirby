<template>
  <k-writer
    ref="input"
    :nodes="false"
    :placeholder="placeholder"
    :value="content.text"
    class="k-block-type-text-input"
    @input="update({ text: $event })"
  />
</template>

<script>
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

<style lang="scss">
.k-block-type-text-input {
  font-size: $text-base;
  line-height: 1.5em;
}
</style>
