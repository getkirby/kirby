<template>
  <k-field
    :counter="counterOptions"
    v-bind="$props"
    class="k-checkboxes-field"
  >
    <k-input
      :id="_uid"
      ref="input"
      v-bind="$props"
      theme="field"
      type="checkboxes"
      v-on="$listeners"
    />
  </k-field>
</template>

<script>
import Field from "../Field.vue";
import Input from "../Input.vue";
import CheckboxesInput from "../Input/CheckboxesInput.vue";

/**
 * Have a look at `<k-field>`, `<k-input>` and `<k-checkboxes-input>` for additional information.
 */
export default {
  inheritAttrs: false,
  props: {
    ...Field.props,
    ...Input.props,
    ...CheckboxesInput.props,
    counter: {
      type: Boolean,
      default: true
    }
  },
  computed: {
    counterOptions() {
      if (this.value === null || this.disabled || this.counter === false) {
        return false;
      }
      return {
        count: this.value && Array.isArray(this.value) ? this.value.length : 0,
        min: this.min,
        max: this.max
      };
    }
  },
  methods: {
    focus() {
      this.$refs.input.focus();
    }
  }
}
</script>
