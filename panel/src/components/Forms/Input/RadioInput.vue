<template>
  <ul :style="'--columns:' + columns" class="k-radio-input">
    <li v-for="(option, index) in options" :key="index">
      <input
        :id="id + '-' + index"
        :value="option.value"
        :name="id"
        :checked="value === option.value"
        type="radio"
        class="k-radio-input-native"
        @change="onInput(option.value)"
      >
      <label :for="id + '-' + index">
        <template v-if="option.info">
          <span class="k-radio-input-text">{{ option.text }}</span>
          <span class="k-radio-input-info">{{ option.info }}</span>
        </template>
        <template v-else>
          {{ option.text }}
        </template>
      </label>
      <k-icon v-if="option.icon" :type="option.icon" />
    </li>
  </ul>
</template>

<script>
import { required } from "vuelidate/lib/validators";

export default {
  inheritAttrs: false,
  props: {
    autofocus: Boolean,
    columns: Number,
    disabled: Boolean,
    id: {
      type: [Number, String],
      default() {
        return this._uid;
      }
    },
    options: Array,
    required: Boolean,
    value: [String, Number, Boolean]
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
  },
  methods: {
    focus() {
      this.$el.querySelector("input").focus();
    },
    onInput(value) {
      this.$emit("input", value);
    },
    onInvalid() {
      this.$emit("invalid", this.$v.$invalid, this.$v);
    },
    select() {
      this.focus();
    }
  },
  validations() {
    return {
      value: {
        required: this.required ? required : true
      }
    };
  }
}
</script>

<style>
.k-radio-input li {
  position: relative;
  line-height: 1.5rem;
  padding-left: 1.75rem;
}
.k-radio-input input {
  position: absolute;
  width: 0;
  height: 0;
  appearance: none;
  opacity: 0;
}
.k-radio-input label {
  cursor: pointer;
  align-items: center;
}
.k-radio-input label::before {
  position: absolute;
  top: .175em;
  left: 0;
  content: "";
  width: 1rem;
  height: 1rem;
  border-radius: 50%;
  border: 2px solid var(--color-gray-500);
  box-shadow: var(--color-white) 0 0 0 2px inset;
}
.k-radio-input input:checked + label::before {
  border-color: var(--color-gray-900);
  background: var(--color-gray-900);
}
[data-disabled] .k-radio-input input:checked + label::before {
  border-color: var(--color-gray-600);
  background: var(--color-gray-600);
}
.k-radio-input input:focus + label::before {
  border-color: var(--color-blue-600);
}
.k-radio-input input:focus:checked + label::before {
  background: var(--color-focus);
}

.k-radio-input-text {
  display: block;
}
</style>
