<template>
  <k-field
    :input="_uid"
    :counter="counterOptions"
    v-bind="$props"
    class="k-password-field"
  >
    <k-input
      ref="input"
      :id="_uid"
      v-bind="$props"
      theme="field"
      type="password"
      v-on="$listeners"
    />
  </k-field>
</template>

<script>
import Field from "../Field.vue";
import Input from "../Input.vue";
import PasswordInput from "../Input/PasswordInput.vue";

export default {
  inheritAttrs: false,
  props: {
    ...Field.props,
    ...Input.props,
    ...PasswordInput.props,
    counter: {
      type: Boolean,
      default: true
    },
    minlength: {
      type: Number,
      default: 8
    },
    icon: {
      type: String,
      default: "key"
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
