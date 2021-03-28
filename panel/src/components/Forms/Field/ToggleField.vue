<template>
  <k-field :input="_uid" v-bind="$props" class="k-toggle-field">
    <k-input
      :id="_uid"
      ref="input"
      v-bind="$props"
      theme="field"
      type="toggle"
      v-on="$listeners"
    />
  </k-field>
</template>

<script>
import { props as Field } from "../Field.vue";
import { props as Input } from "../Input.vue";
import { props as ToggleInput } from "../Input/ToggleInput.vue";

export default {
  mixins: [
    Field,
    Input,
    ToggleInput
  ],
  inheritAttrs: false,
  methods: {
    focus() {
      this.$refs.input.focus();
    }
  }
}
</script>
