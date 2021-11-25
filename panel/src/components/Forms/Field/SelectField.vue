<template>
  <k-field :input="_uid" v-bind="$props" class="k-select-field">
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
import { props as Field } from "../Field.vue";
import { props as Input } from "../Input.vue";
import { props as SelectInput } from "../Input/SelectInput.vue";

/**
 * Have a look at `<k-field>`, `<k-input>` and `<k-select-input>` for additional information.
 */
export default {
  mixins: [Field, Input, SelectInput],
  inheritAttrs: false,
  props: {
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
};
</script>
