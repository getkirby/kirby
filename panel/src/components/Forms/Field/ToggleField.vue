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
import Field from "../Field.vue";
import Input from "../Input.vue";
import ToggleInput from "../Input/ToggleInput.vue";

/**
 * Have a look at `<k-field>`, `<k-input>` and `<k-toggle-input>` 
 * for additional information.
 * @example <k-toggle-field v-model="toggle" label="Toggle" name="toggle" />
 */
export default {
  inheritAttrs: false,
  props: {
    ...Field.props,
    ...Input.props,
    ...ToggleInput.props
  },
  methods: {
    focus() {
      this.$refs.input.focus();
    }
  }
}
</script>
