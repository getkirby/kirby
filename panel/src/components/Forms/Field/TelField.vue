<template>
  <k-field :input="_uid" v-bind="$props" class="k-tel-field">
    <k-input
      :id="_uid"
      ref="input"
      v-bind="$props"
      theme="field"
      type="tel"
      v-on="$listeners"
    />
  </k-field>
</template>

<script>
import Field from "../Field.vue";
import Input from "../Input.vue";
import TelInput from "../Input/TelInput.vue";

/**
 * Have a look at `<k-field>`, `<k-input>` and `<k-tel-input>` 
 * for additional information.
 * @example <k-tel-field v-model="tel" name="tel" label="Phone number" />
 */
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
