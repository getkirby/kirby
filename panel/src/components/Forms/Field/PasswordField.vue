<template>
  <k-field
    :input="_uid"
    :counter="counterOptions"
    v-bind="$props"
    class="k-password-field"
  >
    <template #options>
      <slot name="options" />
    </template>
    <k-input
      :id="_uid"
      ref="input"
      v-bind="$props"
      theme="field"
      type="password"
      v-on="$listeners"
    />
  </k-field>
</template>

<script>
import { props as Field } from "../Field.vue";
import { props as Input } from "../Input.vue";
import { props as PasswordInput } from "../Input/PasswordInput.vue";
import counter from "@/mixins/forms/counter.js";

/**
 * Have a look at `<k-field>`, `<k-input>` and `<k-password-input>` for additional information.
 */
export default {
  mixins: [Field, Input, PasswordInput, counter],
  inheritAttrs: false,
  props: {
    minlength: {
      type: Number,
      default: 8
    },
    icon: {
      type: String,
      default: "key"
    }
  },
  methods: {
    focus() {
      this.$refs.input.focus();
    }
  }
};
</script>
