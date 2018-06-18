<template>
  <kirby-field :input="_uid" v-bind="$props" class="kirby-select-field">
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
import SelectInput from "../Input/SelectInput.vue";

export default {
  inheritAttrs: false,
  props: {
    ...Field.props,
    ...Input.props,
    ...SelectInput.props,
    icon: {
      type: String,
      default: "angle-down"
    }
  },
  methods: {
    focus() {
      this.$refs.input.focus();
    }
  }
}
</script>
