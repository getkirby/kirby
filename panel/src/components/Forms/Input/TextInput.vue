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
    :dir="direction"
    class="k-text-input"
    v-on="listeners"
  >
</template>

<script>
import {
  required,
  minLength,
  maxLength,
  email,
  url
} from "vuelidate/lib/validators";
import direction from "@/helpers/direction.js";

/**
 * @example <k-input v-model="text" name="text" type="text" />
 */
export default {
  inheritAttrs: false,
  class: "k-text-input",
  props: {
    autocomplete: {
      type: [Boolean, String],
      default: "off"
    },
    autofocus: Boolean,
    disabled: Boolean,
    id: [Number, String],
    maxlength: Number,
    minlength: Number,
    name: [Number, String],
    pattern: String,
    placeholder: String,
    preselect: Boolean,
    required: Boolean,
    spellcheck: {
      type: [Boolean, String],
      default: "off"
    },
    type: {
      type: String,
      default: "text"
    },
    value: String
  },
  data() {
    return {
      listeners: {
        ...this.$listeners,
        input: event => this.onInput(event.target.value)
      }
    };
  },
  computed: {
    direction() {
      return direction(this);
    }
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
    const match = (value) => {
      return (!this.required && !value) || !this.$refs.input.validity.patternMismatch;
    };

    return {
      value: {
        required: this.required ? required : true,
        minLength: this.minlength ? minLength(this.minlength) : true,
        maxLength: this.maxlength ? maxLength(this.maxlength) : true,
        email: this.type === "email" ? email : true,
        url: this.type === "url" ? url : true,
        pattern: this.pattern ? match : true,
      }
    };
  }
};
</script>

<style >
.k-text-input {
  width: 100%;
  border: 0;
  background: none;
  font: inherit;
  color: inherit;
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
