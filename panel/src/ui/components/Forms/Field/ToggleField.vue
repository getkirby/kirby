<template>
  <kirby-field :input="_uid" v-bind="$props" class="kirby-toggle-field">
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
