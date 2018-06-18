<template>
  <kirby-field :input="_uid" v-bind="$props" class="kirby-range-field">
    <kirby-input
      ref="input"
      :id="_uid"
      v-bind="$props"
      theme="field"
      v-on="$listeners"
    />
  </kirby-field>
</template>

<script>
import Field from "../Field.vue";
import Input from "../Input.vue";
import RangeInput from "../Input/RangeInput.vue";

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
