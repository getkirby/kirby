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
import { props as Field } from "../Field.vue";
import { props as Input } from "../Input.vue";

export default {
  mixins: [
    Field,
    Input
  ],
  inheritAttrs: false,
  props: {
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
