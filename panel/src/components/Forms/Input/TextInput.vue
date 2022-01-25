<template>
  <input
    ref="input"
    v-bind="{
      autocomplete,
      autofocus,
      disabled,
      id,
      minlength,
      name,
      pattern,
      placeholder,
      required,
      spellcheck,
      type,
      value
    }"
    v-direction
    class="k-text-input"
    v-on="listeners"
  />
</template>

<script>
import { autofocus, disabled, id, name, required } from "@/mixins/props.js";

import {
  required as validateRequired,
  minLength as validateMinLength,
  maxLength as validateMaxLength,
  email as validateEmail,
  url as validateUrl
} from "vuelidate/lib/validators";

export const props = {
  mixins: [autofocus, disabled, id, name, required],
  props: {
    autocomplete: {
      type: [Boolean, String],
      default: "off"
    },
    maxlength: Number,
    minlength: Number,
    pattern: String,
    placeholder: String,
    preselect: Boolean,
    spellcheck: {
      type: [Boolean, String],
      default: "off"
    },
    type: {
      type: String,
      default: "text"
    },
    value: String
  }
};

/**
 * @example <k-input v-model="text" name="text" type="text" />
 */
export default {
  mixins: [props],
  inheritAttrs: false,
  data() {
    return {
      listeners: {
        ...this.$listeners,
        input: (event) => this.onInput(event.target.value)
      }
    };
  },
  watch: {
    value() {
      this.onInvalid();
    }
  },
  mounted() {
    this.onInvalid();

    if (this.$props.autofocus) {
      this.focus();
    }

    if (this.$props.preselect) {
      this.select();
    }
  },
  methods: {
    focus() {
      this.$refs.input.focus();
    },
    onInput(value) {
      this.$emit("input", value);
    },
    onInvalid() {
      this.$emit("invalid", this.$v.$invalid, this.$v);
    },
    select() {
      this.$refs.input.select();
    }
  },
  validations() {
    const validateMatch = (value) => {
      return (
        (!this.required && !value) || !this.$refs.input.validity.patternMismatch
      );
    };

    return {
      value: {
        required: this.required ? validateRequired : true,
        minLength: this.minlength ? validateMinLength(this.minlength) : true,
        maxLength: this.maxlength ? validateMaxLength(this.maxlength) : true,
        email: this.type === "email" ? validateEmail : true,
        url: this.type === "url" ? validateUrl : true,
        pattern: this.pattern ? validateMatch : true
      }
    };
  }
};
</script>

<style>
.k-text-input {
  width: 100%;
  border: 0;
  background: none;
  font: inherit;
  color: inherit;
  font-variant-numeric: tabular-nums;
}
.k-text-input::placeholder {
  color: var(--color-gray-500);
}
.k-text-input:focus {
  outline: 0;
}
.k-text-input:invalid {
  box-shadow: none;
  outline: 0;
}
</style>
