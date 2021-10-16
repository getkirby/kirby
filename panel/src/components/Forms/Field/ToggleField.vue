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

/**
 * Have a look at `<k-field>`, `<k-input>` and `<k-toggle-input>`
 * for additional information.
 * @example <k-toggle-field v-model="toggle" label="Toggle" name="toggle" />
 */
export default {
  mixins: [Field, Input, ToggleInput],
  inheritAttrs: false,
  methods: {
    focus() {
      this.$refs.input.focus();
    }
  }
};
</script>
