<template>
  <k-field
    :input="_uid"
    :counter="counterOptions"
    v-bind="$props"
    class="k-text-field"
  >
    <slot slot="options" name="options" />
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
import TextInput from "../Input/TextInput.vue";

/**
 * Have a look at `<k-field>`, `<k-input>` and `<k-text-input>` 
 * for additional information.
 * @example <k-text-field v-model="text" name="text" label="Boring text" />
 */
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
    },
    select() {
      this.$refs.input.select();
    }
  }
}
</script>

<style>
.k-field-counter {
  display: none;
}
.k-text-field:focus-within .k-field-counter {
  display: block;
}
</style>
