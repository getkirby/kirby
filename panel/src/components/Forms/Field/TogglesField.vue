<template>
  <k-field v-bind="$props" class="k-toggles-field">
    <k-input
      :id="_uid"
      ref="input"
      v-bind="$props"
      v-on="$listeners"
      type="toggles"
      @reset="onInput('')"
    />
  </k-field>
</template>

<script>
import { props as Field } from "../Field.vue";
import { props as Input } from "../Input.vue";
import { props as TogglesInput } from "../Input/TogglesInput.vue";

export default {
  inheritAttrs: false,
  mixins: [Field, Input, TogglesInput],
  methods: {
    focus() {
      this.$refs.input.focus();
    },
    onInput(value) {
      this.$emit("input", value);
    }
  }
};
</script>

<style>
.k-toggles-field[data-disabled] .k-toggles-input {
  opacity: 0.5;
}
.k-toggles-field .k-input[data-invalid] {
  box-shadow: none;
}
.k-toggles-field .k-input,
.k-toggles-field .k-input-element {
  display: inline-flex;
}
</style>
