<template>
  <input
    ref="input"
    v-bind="{
      autofocus,
      disabled,
      id,
      max,
      min,
      name,
      placeholder,
      required
    }"
    :value="number"
    :step="stepNumber"
    class="k-number-input"
    type="number"
    @keydown.ctrl.s="clean"
    @keydown.meta.s="clean"
    v-on="listeners"
  >
</template>

<script>
import {
  required,
  minValue,
  maxValue
} from "vuelidate/lib/validators";

/**
 * @example <k-input v-model="number" name="number" type="number" />
 */
export default {
  inheritAttrs: false,
  props: {
    autofocus: Boolean,
    disabled: Boolean,
    id: [Number, String],
    /**
     * The highest accepted number
     */
    max: Number,
    min: Number,
    name: [Number, String],
    placeholder: String,
    preselect: Boolean,
    required: Boolean,
    /**
     * The amount to increment with each input step. This can be a decimal.
     */
    step: Number,
    value: {
      type: [Number, String],
      default: null
    }
  },
  data() {
    return {
      number: this.format(this.value),
      stepNumber: this.format(this.step),
      timeout: null,
      listeners: {
        ...this.$listeners,
        input: (event) => this.onInput(event.target.value),
        blur: this.onBlur,
      }
    }
  },
  watch: {
    value(value) {
      this.number = value;
    },
    number: {
      immediate: true,
      handler() {
        this.onInvalid();
      }
    }
  },
  mounted() {
    if (this.$props.autofocus) {
      this.focus();
    }

    if (this.$props.preselect) {
      this.select();
    }
  },
  methods: {
    decimals() {
      const step = Number(this.step || 0);

      if (Math.floor(step) === step) {
        return 0;
      }

      if (step.toString().indexOf('e') !== -1) {
        return parseInt(step.toFixed(16).split(".")[1].split("").reverse().join("")).toString().length;
      }

      return step.toString().split(".")[1].length || 0;
    },
    format(value) {
      if (isNaN(value) || value === "") {
        return "";
      }

      const decimals = this.decimals();

      if (decimals) {
        value = parseFloat(value).toFixed(decimals);
      } else if (Number.isInteger(this.step)) {
        value = parseInt(value);
      } else {
        value = parseFloat(value);
      }

      return value;
    },
    clean() {
      this.number = this.format(this.number);
    },
    emit(value) {
      value = parseFloat(value);

      if (isNaN(value)) {
        value = "";
      }

      if (value !== this.value) {
        this.$emit("input", value);
      }
    },
    focus() {
      this.$refs.input.focus();
    },
    onInvalid() {
      this.$emit("invalid", this.$v.$invalid, this.$v);
    },
    onInput(value) {
      this.number = value;
      this.emit(value);
    },
    onBlur() {
      this.clean();
      this.emit(this.number);
    },
    select() {
      this.$refs.input.select();
    }
  },
  validations() {
    return {
      value: {
        required: this.required ? required : true,
        min: this.min ? minValue(this.min) : true,
        max: this.max ? maxValue(this.max) : true
      }
    };
  }
}

</script>

<style>
.k-number-input {
  width: 100%;
  border: 0;
  background: none;
  font: inherit;
  color: inherit;
}
.k-number-input::placeholder {
  color: var(--color-gray-500);
}
.k-number-input:focus {
  outline: 0;
}
.k-number-input:invalid {
  box-shadow: none;
  outline: 0;
}
</style>
