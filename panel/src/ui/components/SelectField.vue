<template>
  <k-field :input="_uid" v-bind="$props">
    <k-input
      :id="_uid"
      ref="input"
      v-bind="$props"
      theme="field"
      type="select"
      v-on="$listeners"
    />
  </k-field>
</template>

<script>
import Field from "./Field.vue";
import Input from "./Input.vue";
import SelectInput from "./SelectInput.vue";

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
