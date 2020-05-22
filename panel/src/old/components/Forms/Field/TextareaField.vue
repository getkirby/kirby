<template>
  <k-field
    :input="_uid"
    :counter="counterOptions"
    v-bind="$props"
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
import Field from "../Field.vue";
import Input from "../Input.vue";
import TextareaInput from "../Input/TextareaInput.vue";

export default {
  inheritAttrs: false,
  props: {
    ...Field.props,
    ...Input.props,
    ...TextareaInput.props,
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
        count: this.value ? this.value.length : 0,
        min: this.minlength,
        max: this.maxlength
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
