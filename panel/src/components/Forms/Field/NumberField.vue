<template>
  <k-field :input="_uid" v-bind="$props" class="k-number-field">
    <k-input
      :id="_uid"
      ref="input"
      v-bind="$props"
      theme="field"
      type="number"
      v-on="$listeners"
    />
  </k-field>
</template>

<script>
import Field from "../Field.vue";
import Input from "../Input.vue";
import NumberInput from "../Input/NumberInput.vue";

/**
 * Have a look at `<k-field>`, `<k-input>` and `<k-number-input>` for additional information.
 * @example <k-number-field v-model="number" name="number" label="Number" />
 */
export default {
  inheritAttrs: false,
  props: {
    ...Field.props,
    ...Input.props,
    ...NumberInput.props
  },
  methods: {
    focus() {
      this.$refs.input.focus();
    }
  }
}
</script>
