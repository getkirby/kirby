<template>
  <k-field
    :input="_uid"
    :counter="counterOptions"
    v-bind="$props"
    class="k-multiselect-field"
    @blur="blur"
    @keydown.native.enter.prevent="focus"
  >
    <k-input
      :id="_uid"
      ref="input"
      v-bind="$props"
      theme="field"
      type="multiselect"
      v-on="$listeners"
    />
  </k-field>
</template>

<script>
import { props as Field } from "../Field.vue";
import { props as Input } from "../Input.vue";
import { props as MultiselectInput } from "../Input/MultiselectInput.vue";
import counter from "@/mixins/forms/counter.js";

/**
 * Have a look at `<k-field>`, `<k-input>` and `<k-multiselect-input>` for additional information.
 */
export default {
  mixins: [Field, Input, MultiselectInput, counter],
  inheritAttrs: false,
  props: {
    icon: {
      type: String,
      default: "angle-down"
    }
  },
  mounted() {
    this.$refs.input.$el.setAttribute("tabindex", 0);
  },
  methods: {
    blur(e) {
      this.$refs.input.blur(e);
    },
    focus() {
      this.$refs.input.focus();
    }
  }
};
</script>
