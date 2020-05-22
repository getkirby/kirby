<template>
  <k-field
    :input="_uid"
    :counter="counterOptions"
    v-bind="$props"
  >
    <template v-slot:options>
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
import Field from "./Field.vue";
import Input from "./Input.vue";
import TextInput from "./TextInput.vue";

export default {
  inheritAttrs: false,
  props: {
    ...Field.props,
    ...Input.props,
    ...TextInput.props,
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
        count: this.value ? String(this.value).length : 0,
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

<style lang="scss">
.k-field-counter {
  display: none;
}
.k-text-field:focus-within .k-field-counter {
  display: block;
}
</style>
