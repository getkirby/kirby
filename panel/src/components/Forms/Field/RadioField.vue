<template>
  <k-field v-bind="$props" class="k-radio-field">
    <k-input
      :id="_uid"
      ref="input"
      v-bind="$props"
      theme="field"
      type="radio"
      v-on="$listeners"
    />
  </k-field>
</template>

<script>
import Field from "../Field.vue";
import Input from "../Input.vue";
import RadioInput from "../Input/RadioInput.vue";

/**
 * Have a look at `<k-field>`, `<k-input>` and `<k-radio-input>` for additional information.
 */
export default {
  inheritAttrs: false,
  props: {
    ...Field.props,
    ...Input.props,
    ...RadioInput.props
  },
  methods: {
    focus() {
      this.$refs.input.focus();
    }
  }
}
</script>
