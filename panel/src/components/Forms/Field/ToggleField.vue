<template>
  <k-field :input="_uid" v-bind="$props" class="k-toggle-field">
    <k-input
      ref="input"
      :id="_uid"
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
