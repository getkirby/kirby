<template>
  <k-field :input="_uid" v-bind="$props" class="k-range-field">
    <k-input
      :id="_uid"
      ref="input"
      v-bind="$props"
      theme="field"
      type="range"
      v-on="$listeners"
    />
  </k-field>
</template>

<script>
import Field from "../Field.vue";
import Input from "../Input.vue";
import RangeInput from "../Input/RangeInput.vue";

/**
 * Have a look at `<k-field>`, `<k-input>` and `<k-range-input>` for additional information.
 * @example <k-range-field v-model="range" name="range" label="Slider" />
 */
export default {
  inheritAttrs: false,
  props: {
    ...Field.props,
    ...Input.props,
    ...RangeInput.props
  },
  methods: {
    focus() {
      this.$refs.input.focus();
    }
  }
}
</script>
