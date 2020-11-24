<template>
  <k-field
    :input="_uid"
    :counter="false"
    v-bind="$props"
    class="k-list-field"
  >
    <k-input
      ref="input"
      :id="_uid"
      :marks="marks"
      :value="value"
      type="list"
      theme="field"
      @input="$emit('input', $event)"
    />
  </k-field>
</template>

<script>
import Field from "../Field.vue";
import Input from "../Input.vue";

export default {
  inheritAttrs: false,
  props: {
    ...Field.props,
    ...Input.props,
    marks: [Array, Boolean],
    value: String,
  },
  methods: {
    focus() {
      this.$refs.input.focus();
    }
  }
}
</script>

<style>
.k-list-field .k-list-input {
  padding: .375rem .5rem .375rem .75rem;
}
</style>
