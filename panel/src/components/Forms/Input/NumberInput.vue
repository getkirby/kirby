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
      step,
      value
    }"
    class="k-number-input"
    type="text"
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
      listeners: {
        ...this.$listeners,
        blur: (event) => this.onInput(event.target.value),
        input: () => {},
        keydown: (event) => this.onKeydown(event)
      }
    }
  },
  watch: {
    value() {
      this.onInvalid();
    }
  },
  computed: {
    decimals() {
      return this.step.toString().split(".")[1].length;
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
    onInvalid() {
      this.$emit("invalid", this.$v.$invalid, this.$v);
    },
    onInput(value) {
      // float calculations can be slightly off:
      // ensure to get correct result with same decimal number as step
      value = parseFloat(Number(parseFloat(value).toFixed(this.decimals)));
      value = Math.ceil(value / this.step) * this.step;
      value = value.toFixed(this.decimals);

      this.$emit("input", value);
    },
    onKeydown(e) {
      switch (e.code) {
        case "ArrowUp":
          e.preventDefault();
          this.onInput((Number(this.value) || 0) + this.step);
          break;
        case "ArrowDown":
          e.preventDefault();
          this.onInput((Number(this.value) || 0) - this.step);
          break;
      }
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
