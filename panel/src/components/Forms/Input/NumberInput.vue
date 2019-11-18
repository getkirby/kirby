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
      required,
      step
    }"
    :value="number"
    class="k-number-input"
    type="number"
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
      listeners: {
        ...this.$listeners,
        change: (event) => this.onChange(event.target.value),
        input: (event) => this.onInput(event.target.value),
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

      return step.toString().split(".")[1].length || 0;
    },
    format(value) {
      if (value === "") {
        return "";
      }

      const decimals = this.decimals();

      if (decimals) {
        return parseFloat(value).toFixed(decimals);
      }

      if (Number.isInteger(this.step)) {
        return parseInt(value);
      }

      return parseFloat(value);
    },
    focus() {
      this.$refs.input.focus();
    },
    onInvalid() {
      this.$emit("invalid", this.$v.$invalid, this.$v);
    },
    onInput(value) {
      this.number = value;
      this.$emit("input", this.number);
    },
    onChange(value) {
      this.number = this.format(value);
      this.$emit("input", this.number);
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
