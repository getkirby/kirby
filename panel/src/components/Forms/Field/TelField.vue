<template>
  <k-field
    :input="_uid"
    v-bind="$props"
    class="k-tel-field"
  >
    <k-input
      :id="_uid"
      ref="input"
      v-bind="$props"
      theme="field"
      v-on="$listeners"
    />
  </k-field>
</template>

<script>
import Field from "../Field.vue";
import Input from "../Input.vue";
import TelInput from "@/ui/components/TelInput.vue";

export default {
  inheritAttrs: false,
  props: {
    ...Field.props,
    ...Input.props,
    ...TelInput.props,
    icon: {
      type: String,
      default: "phone"
    }
  },
  methods: {
    focus() {
      this.$refs.input.focus();
    }
  }
}
</script>
