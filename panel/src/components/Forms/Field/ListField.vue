<template>
  <k-field
    :input="_uid"
    :counter="false"
    v-bind="$props"
    class="k-list-field"
  >
    <k-input
      :id="_uid"
      ref="input"
      v-bind="$props"
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

/**
 * Have a look at `<k-field>` and `<k-input>` for additional information.
 */
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
