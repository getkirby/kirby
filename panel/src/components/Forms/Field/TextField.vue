<template>
  <k-field
    :input="_uid"
    :counter="counterOptions"
    v-bind="$props"
    class="k-text-field"
  >
    <template #options>
      <slot name="options" />
    </template>
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
import { props as Field } from "../Field.vue";
import { props as Input } from "../Input.vue";
import { props as TextInput } from "../Input/TextInput.vue";
import counter from "@/mixins/forms/counter.js";

/**
 * Have a look at `<k-field>`, `<k-input>` and `<k-text-input>`
 * for additional information.
 * @example <k-text-field v-model="text" name="text" label="Boring text" />
 */
export default {
  mixins: [Field, Input, TextInput, counter],
  inheritAttrs: false,
  methods: {
    focus() {
      this.$refs.input.focus();
    },
    select() {
      this.$refs.input.select();
    }
  }
};
</script>

<style>
.k-field-counter {
  display: none;
}
.k-text-field:focus-within .k-field-counter {
  display: block;
}
</style>
