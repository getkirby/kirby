<template>
  <k-field :input="_uid" v-bind="$props" class="k-select-field">
    <k-input
      ref="input"
      :id="_uid"
      v-bind="$props"
      theme="field"
      type="select"
      v-on="$listeners"
    />
  </k-field>
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
