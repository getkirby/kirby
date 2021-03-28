<template>
  <k-field
    :input="_uid"
    :counter="counterOptions"
    v-bind="$props"
    class="k-textarea-field"
  >
    <k-input
      :id="_uid"
      ref="input"
      v-bind="$props"
      type="textarea"
      theme="field"
      v-on="$listeners"
    />
  </k-field>
</template>

<script>
import { props as Field } from "../Field.vue";
import { props as Input } from "../Input.vue";
import { props as TextareaInput } from "../Input/TextareaInput.vue";
import counter from "@/mixins/forms/counter.js";

export default {
  mixins: [
    Field,
    Input,
    TextareaInput,
    counter
  ],
  inheritAttrs: false,
  methods: {
    focus() {
      this.$refs.input.focus();
    }
  }
}
</script>
