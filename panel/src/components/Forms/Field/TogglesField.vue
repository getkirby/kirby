<template>
  <k-field v-bind="$props" class="k-toggles-field">
    <k-input
      :id="_uid"
      ref="input"
      v-bind="$props"
      v-on="$listeners"
      theme="field"
      type="toggles"
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
