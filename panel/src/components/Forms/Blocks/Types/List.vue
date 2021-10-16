<template>
  <k-input
    ref="input"
    :marks="marks"
    :value="content.text"
    class="k-block-type-list-input"
    type="list"
    @input="update({ text: $event })"
  />
</template>

<script>
/**
 * @displayName BlockTypeList
 * @internal
 */
export default {
  computed: {
    marks() {
      return this.field("text", {}).marks;
    }
  },
  methods: {
    focus() {
      this.$refs.input.focus();
    }
  }
};
</script>
