<template>
  <k-field :input="_uid" v-bind="$props" class="k-time-field">
    <k-input
      ref="input"
      :id="_uid"
      v-bind="$props"
      theme="field"
      type="time"
      v-on="$listeners"
    />
  </k-field>
</template>

<script>
import Field from "../Field.vue";
import Input from "../Input.vue";
import TimeInput from "../Input/TimeInput.vue";

export default {
  inheritAttrs: false,
  props: {
    ...Field.props,
    ...Input.props,
    ...TimeInput.props,
    icon: {
      type: String,
      default: "clock"
    }
  },
  methods: {
    focus() {
      this.$refs.input.focus();
    }
  }
}
</script>
