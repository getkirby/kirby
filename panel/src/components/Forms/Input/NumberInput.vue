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
    @keydown.cmd.s="clean"
    v-on="listeners"
  >
</template>

<script>
import {
  required,
  minValue,
  maxValue
} from "vuelidate/lib/validators";

export default {
  inheritAttrs: false,
  props: {
    autofocus: Boolean,
    disabled: Boolean,
    id: [Number, String],
    max: Number,
    min: Number,
    name: [Number, String],
    placeholder: String,
    preselect: Boolean,
    required: Boolean,
    step: Number,
    value: {
      type: [Number, String],
      default: null
    }
  },
  data() {
    return {
      number: this.format(this.value),
      stepNumber: this.convertExponentialToDecimal(this.step),
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
      this.number = this.convertExponentialToDecimal(value);
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

      const stepNumber = this.convertExponentialToDecimal(step);

      return stepNumber.toString().split(".")[1].length || 0;
    },
    convertExponentialToDecimal(value) {
      if (value !== null) {
        // sanity check - is it exponential number
        const str = value.toString();

        if (str.indexOf('e') !== -1) {
          const pieces = str.split('-');

          // Get last piece of number to ensure getting on negative numbers have two hype like "-1e-8"
          const exponent = pieces[pieces.length-1];
          const decimals = parseInt(exponent);

          // Unfortunately I can not return 1e-8 as 0.00000001, because even if I call parseFloat() on it,
          // it will still return the exponential representation. So we have to use .toFixed() method
          return value.toFixed(decimals);
        }
      }

      return value;
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
  color: $color-light-grey;
}
.k-number-input:focus {
  outline: 0;
}
.k-number-input:invalid {
  box-shadow: none;
  outline: 0;
}
</style>
